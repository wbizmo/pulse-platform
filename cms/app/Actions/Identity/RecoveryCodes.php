<?php

namespace App\Actions\Identity;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class RecoveryCodes
{
    public function generate(User $user): array
    {
        $codes = collect(range(1, 8))->map(fn () => Str::lower(Str::random(5).'-'.Str::random(5)))->all();
        $user->forceFill(['mfa_recovery_codes' => array_map(fn ($code) => Hash::make($code), $codes)])->save();

        return $codes;
    }

    public function consume(User $user, string $candidate): bool
    {
        foreach ($user->mfa_recovery_codes ?? [] as $index => $hash) {
            if (Hash::check(Str::lower(trim($candidate)), $hash)) {
                $codes = $user->mfa_recovery_codes;
                unset($codes[$index]);
                $user->forceFill(['mfa_recovery_codes' => array_values($codes)])->save();

                return true;
            }
        }

        return false;
    }
}
