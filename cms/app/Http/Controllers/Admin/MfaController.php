<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Access\RecordAudit;
use App\Actions\Identity\RecoveryCodes;
use App\Domain\Identity\Totp;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class MfaController extends Controller
{
    public function show(Request $request): View
    {
        return view('admin.identity.mfa', ['enabled' => $request->user()->hasConfirmedMfa()]);
    }

    public function enroll(Request $request, Totp $totp, RecordAudit $audit): View|RedirectResponse
    {
        if ($request->user()->hasConfirmedMfa()) {
            return back()->withErrors(['mfa' => 'Multi-factor authentication is already enabled.']);
        }
        $secret = $totp->secret();
        $request->user()->forceFill(['mfa_secret' => $secret, 'mfa_recovery_codes' => null, 'mfa_confirmed_at' => null])->save();
        $audit->execute($request->user(), 'identity.mfa_enrollment_started', $request->user());
        $issuer = rawurlencode((string) config('app.name'));
        $label = rawurlencode(config('app.name').':'.$request->user()->email);

        return view('admin.identity.mfa-enroll', ['secret' => $secret, 'uri' => "otpauth://totp/{$label}?secret={$secret}&issuer={$issuer}&algorithm=SHA1&digits=6&period=30"]);
    }

    public function confirm(Request $request, Totp $totp, RecoveryCodes $recovery, RecordAudit $audit): View|RedirectResponse
    {
        $validated = $request->validate(['code' => ['required', 'digits:6']]);
        $user = $request->user();
        if (! filled($user->mfa_secret) || ! $totp->verify($user->mfa_secret, $validated['code'])) {
            return back()->withErrors(['code' => 'The authentication code is invalid.']);
        }
        $user->forceFill(['mfa_confirmed_at' => now(), 'mfa_last_used_step' => intdiv(time(), 30)])->save();
        $codes = $recovery->generate($user);
        $request->session()->put('mfa_passed', true);
        $request->session()->regenerate();
        $audit->execute($user, 'identity.mfa_enabled', $user);

        return view('admin.identity.mfa-recovery', compact('codes'));
    }

    public function challenge(): View
    {
        return view('admin.identity.mfa-challenge');
    }

    public function verify(Request $request, Totp $totp, RecoveryCodes $recovery, RecordAudit $audit): RedirectResponse
    {
        $validated = $request->validate(['code' => ['required', 'string', 'max:32']]);
        $user = $request->user();
        $key = 'mfa:'.$user->id.':'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['code' => 'Too many attempts. Try again later.']);
        }
        $step = intdiv(time(), 30);
        $totpValid = $totp->verify((string) $user->mfa_secret, $validated['code']) && $user->mfa_last_used_step !== $step;
        $recoveryValid = ! $totpValid && $recovery->consume($user, $validated['code']);
        if (! $totpValid && ! $recoveryValid) {
            RateLimiter::hit($key, 60);
            $audit->execute($user, 'identity.mfa_challenge_failed', $user);

            return back()->withErrors(['code' => 'The authentication or recovery code is invalid.']);
        }
        if ($totpValid) {
            $user->forceFill(['mfa_last_used_step' => $step])->save();
        }
        RateLimiter::clear($key);
        $request->session()->put('mfa_passed', true);
        $request->session()->regenerate();
        $audit->execute($user, $recoveryValid ? 'identity.mfa_recovery_used' : 'identity.mfa_challenge_succeeded', $user);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function regenerate(Request $request, RecoveryCodes $recovery, RecordAudit $audit): View
    {
        abort_unless($request->user()->hasConfirmedMfa(), 404);
        $codes = $recovery->generate($request->user());
        $audit->execute($request->user(), 'identity.mfa_recovery_regenerated', $request->user());

        return view('admin.identity.mfa-recovery', compact('codes'));
    }

    public function disable(Request $request, RecordAudit $audit): RedirectResponse
    {
        $user = $request->user();
        $user->forceFill(['mfa_secret' => null, 'mfa_recovery_codes' => null, 'mfa_confirmed_at' => null, 'mfa_last_used_step' => null])->save();
        $request->session()->forget('mfa_passed');
        $request->session()->regenerate();
        $audit->execute($user, 'identity.mfa_disabled', $user);

        return redirect()->route('admin.mfa.show')->with('status', 'Multi-factor authentication disabled. Privileged access now requires re-enrollment.');
    }

    public function administrativeReset(Request $request, User $user, RecordAudit $audit): RedirectResponse
    {
        abort_if($request->user()->is($user), 422, 'Use your own security settings to disable MFA.');
        $user->forceFill(['mfa_secret' => null, 'mfa_recovery_codes' => null, 'mfa_confirmed_at' => null, 'mfa_last_used_step' => null])->save();
        $audit->execute($request->user(), 'identity.mfa_administratively_reset', $user);

        return back()->with('status', 'MFA reset. The user must enroll again before using privileged capabilities.');
    }
}
