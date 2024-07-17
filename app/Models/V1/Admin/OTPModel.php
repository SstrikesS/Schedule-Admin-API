<?php

namespace App\Models\V1\Admin;

use Illuminate\Database\Eloquent\Model;

class OTPModel extends Model
{
    /** The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admin.one_time_password';

    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'pgsql_main';

    public static function generateOTP(AUser $user, $key): int
    {
        $otp = rand(100000, 999999);

        if (self::query()->updateOrInsert([
                'user_id' => $user->getAttribute('id'),
                'key'     => $key,
            ], [
                'created_at' => now(),
                'otp'        => $otp,
            ]))
        {

            return $otp;
        } else {

            return 0;
        }
    }

    public static function checkOTP($user_id, $otp, $key): bool
    {
        if (self::query()
            ->where('user_id', $user_id)
            ->where('key', $key)
            ->where('otp', $otp)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->first()
        ) {

            return true;
        } else {

            return false;
        }
    }

    public static function updateOTP($user_id, $key): void
    {
        self::query()
            ->where('user_id', $user_id)
            ->where('key', $key)
            ->update(['otp' => null]);
    }
}
