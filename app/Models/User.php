<?php

namespace App\Models;

use App\Consts\AccessTokenState;
use App\Structs\V1\Admin\AUserStruct;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\NewAccessToken;

class User extends Authenticatable
{
    use HasUlids, HasApiTokens, HasFactory, Notifiable;

    protected $connection = "pgsql_main";
    protected $table      = "admin.users";
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable
        = [
            'name',
            'username',
            'email',
            'password',
        ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden
        = [
            'password',
            'remember_token',
        ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts
        = [
            'email_verified_at' => 'datetime',
        ];

    public function struct(): AUserStruct
    {
        return new AUserStruct($this->getAttributes());
    }

    public function newCreateToken(string $name, array $abilities = ['*'], DateTimeInterface $expiresAt = null): NewAccessToken
    {
        $token = $this->createToken($name, $abilities);

        $token->accessToken->state = AccessTokenState::ACTIVE;
        $token->accessToken->save();

        return $token;
    }

//    public function tokens()
//    {
//        return $this->morphMany(Sanctum::$personalAccessTokenModel, 'tokenable', 'string');
//    }
}
