<?php

namespace App\Models\V1\User;

use App\Consts\DateFormat;
use App\Events\User\AdminEditUserEvent;
use App\Libs\NorIntoDB;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CUser extends User
{
    use HasFactory;

    protected $connection = "pgsql_main";
    protected $table      = "main.users";

    public static function getUser(string $id, $select = ['*'], array $opts = []): ?object
    {
        if (!Str::isUlid($id)) {
            return null;
        }

        return DB::connection('pgsql_main')
            ->table('main.users')
            ->where('id', $id)
            ->distinct()
            ->first($select);
    }

    public static function getUsers(array $data = []): LengthAwarePaginator|Collection
    {
        $query = self::query();
        if (!empty($data['filter_q'])) {
            $query->where(function (Builder $query) use ($data) {
                $query
                    ->orWhere('name', 'ilike', "%{$data['filter_q']}%")
                    ->orWhere('username', 'ilike', "%{$data['filter_q']}%")
                    ->orWhere('email', 'ilike', "%{$data['filter_q']}%");
            });
        }

        if (!empty($data['filter_name'])) {
            $query->where('name', 'ilike', "%{$data['filter_name']}%");
        }

        if (!empty($data['filter_username'])) {
            $query->where('username', 'ilike', "%{$data['filter_username']}%");
        }

        if (!empty($data['filter_email'])) {
            $query->where('email', 'ilike', "%{$data['filter_email']}%");
        }

        if (!empty($data['filter_phone'])) {
            $query->where('phone', 'ilike', "%{$data['filter_phone']}%");
        }

        if (isset($data['filter_status'])) {
            $query->where('status', $data['filter_status']);
        }

        if (isset($data['filter_is_owner'])) {
            $query->where('is_owner', $data['filter_is_owner']);
        }

        $sort_data = [
            'name'       => 'name',
            'username'   => 'username',
            'email'      => 'email',
            'phone'      => 'phone',
            'status'     => 'status',
            'is_owner'   => 'is_owner',
            'created_at' => 'created_at',
        ];
        if (isset($data['sort'], $sort_data[$data['sort']])) {
            $sort = $sort_data[$data['sort']];
        } else {
            $sort = $sort_data['created_at'];
        }

        if (isset($data['order']) && ($data['order'] === 'desc')) {
            $order = 'desc';
        } else {
            $order = 'asc';
        }
        $query->orderBy($sort, $order);
        if (!isset($data['limit'])) {
            return $query->get($data['select']);
        } else {
            return $query->paginate($data['limit'], $data['select'], "{$data['page']}", $data['page']);
        }
    }

    public static function getUserByAny($value): ?object
    {
        return DB::connection('pgsql_main')
            ->table('main.users')
            ->where(function (Builder $query) use ($value) {
                $query
                    ->orWhere('username', $value)
                    ->orWhere('email', $value);
            })
            ->distinct()
            ->first();
    }

    public static function getUserByKeyValue($key, $value, $select = ['*']): ?object
    {
        return DB::connection('pgsql_main')
            ->table('main.users')
            ->where($key, $value)
            ->distinct()
            ->first($select);
    }

    public static function setStatus($user_id, $status): int
    {
        return DB::connection('pgsql_main')
            ->table('main.users')
            ->where('id', $user_id)
            ->update([
                'status' => $status
            ]);
    }
}
