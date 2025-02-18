<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Models\User;

class OtpHelper
{
    /**
     * Generate a secure 6-digit OTP.
     */
    public static function generateOtp(): string
    {
        return str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Assign an OTP to a user with expiration.
     */
    public static function assignOtp(User $user, int $validityMinutes = 10): string
    {
        $otp = self::generateOtp();
        $user->update([
            'otp' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes($validityMinutes),
        ]);
        return $otp;
    }

    /**
     * Verify the OTP for a user.
     */
    public static function verifyOtp(User $user, string $otp): bool
    {
        return $user->otp === $otp && Carbon::now()->lessThanOrEqualTo($user->otp_expires_at);
    }

    /**
     * Invalidate OTP after successful use.
     */
    public static function invalidateOtp(User $user): void
    {
        $user->update([
            'otp' => null,
            'otp_expires_at' => null,
        ]);
    }
}
