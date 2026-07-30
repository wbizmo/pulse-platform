<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Access\RecordAudit;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PasswordUpdateRequest;
use App\Http\Requests\Admin\ProfileRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class IdentityController extends Controller
{
    public function verificationNotice(Request $request): View|RedirectResponse
    {
        return $request->user()->hasVerifiedEmail() ? redirect()->route('admin.dashboard') : view('admin.identity.verify-email');
    }

    public function verify(EmailVerificationRequest $request, RecordAudit $audit): RedirectResponse
    {
        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
            $audit->execute($request->user(), 'identity.email_verified', $request->user());
        }

        return redirect()->route('admin.dashboard')->with('status', 'Email address verified.');
    }

    public function resendVerification(Request $request): RedirectResponse
    {
        if (! $request->user()->hasVerifiedEmail()) {
            $request->user()->sendEmailVerificationNotification();
        }

        return back()->with('status', 'A verification link has been sent if verification is still required.');
    }

    public function forgotPassword(): View
    {
        return view('admin.identity.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email:rfc']]);
        if (User::query()->where('email', $request->string('email'))->where('status', 'active')->exists()) {
            Password::sendResetLink($request->only('email'));
        }

        return back()->with('status', 'If an eligible account exists, a password reset link has been sent.');
    }

    public function resetPassword(Request $request, string $token): View
    {
        return view('admin.identity.reset-password', ['token' => $token, 'email' => $request->string('email')]);
    }

    public function updateResetPassword(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'token' => ['required'], 'email' => ['required', 'email:rfc'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);
        $status = Password::reset($credentials, function ($user, $password): void {
            $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
            event(new PasswordReset($user));
            app(RecordAudit::class)->execute($user, 'identity.password_reset', $user);
        });

        return $status === Password::PasswordReset
            ? redirect()->route('admin.login')->with('status', __($status))
            : back()->withErrors(['email' => __($status)])->onlyInput('email');
    }

    public function confirmPassword(): View
    {
        return view('admin.identity.confirm-password');
    }

    public function storePasswordConfirmation(Request $request): RedirectResponse
    {
        $request->validate(['password' => ['required', 'string']]);
        if (! Hash::check((string) $request->string('password'), $request->user()->password)) {
            return back()->withErrors(['password' => 'The password is incorrect.']);
        }
        $request->session()->passwordConfirmed();

        return redirect()->intended(route('admin.profile.edit'));
    }

    public function editProfile(Request $request): View
    {
        $sessions = config('session.driver') === 'database'
            ? DB::table(config('session.table'))->where('user_id', $request->user()->id)->orderByDesc('last_activity')->get()
            : collect();

        return view('admin.identity.profile', compact('sessions'));
    }

    public function updateProfile(ProfileRequest $request, RecordAudit $audit): RedirectResponse
    {
        $user = $request->user();
        $emailChanged = $user->email !== $request->validated('email');
        $user->fill($request->validated());
        if ($emailChanged) {
            $user->email_verified_at = null;
        }
        $user->save();
        $audit->execute($user, 'identity.profile_updated', $user, ['email_changed' => $emailChanged]);

        return redirect()->route($emailChanged ? 'admin.verification.notice' : 'admin.profile.edit')->with('status', 'Profile updated.');
    }

    public function updatePassword(PasswordUpdateRequest $request, RecordAudit $audit): RedirectResponse
    {
        $user = $request->user();
        $user->update(['password' => $request->validated('password'), 'remember_token' => Str::random(60)]);
        $audit->execute($user, 'identity.password_changed', $user);

        return back()->with('status', 'Password updated.');
    }

    public function revokeSession(Request $request, string $session): RedirectResponse
    {
        abort_unless(config('session.driver') === 'database', 404);
        $owned = DB::table(config('session.table'))->where('id', $session)->where('user_id', $request->user()->id);
        abort_unless($owned->exists(), 404);
        $owned->delete();
        if (hash_equals($request->session()->getId(), $session)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login');
        }

        app(RecordAudit::class)->execute($request->user(), 'identity.session_revoked', $request->user());

        return back()->with('status', 'Session revoked.');
    }
}
