<?php

namespace App\Models\Sanctum;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    use HasUlids;

    protected $connection = 'pgsql_main';
    protected $table      = "admin.personal_access_tokens";


    public static function setExpiresToken($id_session, $method): int
    {
        return self::query()
            ->where("id", $id_session)
            ->update([
                'expires_at' => now(),
                'state'      => $method,
            ]);
    }

    public static function getTokensLogin($id_user): object
    {
        return self::query()
            ->where("tokenable_id", $id_user)
            ->where("expires_at", null)
            ->get();
    }

    public static function removeAccessToken(string $id): int
    {
        return self::query()
            ->where('tokenable_id', $id)
            ->delete();
    }

    public static function getSessionDestroy($id_session): ?object
    {
        return self::query()
            ->where('id', '=', $id_session)
            ->where('expires_at', '=', null)
            ->first();
    }
}
