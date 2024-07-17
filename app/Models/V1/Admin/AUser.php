<?php

namespace App\Models\V1\Admin;

use App\Consts\DateFormat;
use App\Events\Admin\PostAUserAddEvent;
use App\Libs\NorIntoDB;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AUser extends User
{
    protected $connection = "pgsql_main";
    protected $table      = "admin.users";

    public static function getUserByCredentials(array $credentials, array $filter): Model|\Illuminate\Database\Eloquent\Builder|null
    {
        return self::query()
            ->where(function ($query) use ($credentials) {
                foreach ($credentials as $key => $value) {
                    $query->where($key, $value);
                }
            })->distinct()->first($filter);
    }

    public static function getUserByName(string $name, array $filter): \Illuminate\Database\Eloquent\Builder|Model|null
    {
        return self::query()
            ->where(function ($query) use ($name) {
                $query
                    ->orWhere('username', $name)
                    ->orWhere('email', $name);
            })
            ->distinct()
            ->first($filter);
    }

    public static function getUser(string $id, $select = ['*'], array $opts = []): ?object
    {
        if (!Str::isUlid($id)) {
            return null;
        }

        return self::query()
            ->where('id', $id)
            ->distinct()
            ->first($select);
    }

    public static function getUsers(array $data = []): LengthAwarePaginator|Collection
    {
        $query = self::query()
            ->where(function ($query) use ($data) {
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
            });

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

            return $query->get($data['select'] );
        } else {

            return $query->paginate($data['limit'], $data['select'] , "{$data['page']}", $data['page']);
        }
    }

    public static function addUserGetID(array $data): string
    {
        $id = self::query()->insertGetId($data);
        PostAUserAddEvent::dispatch($id, $data);

        return $id;
    }

    public static function editUser(string $id, array|NorIntoDB $data): ?int
    {
        if (!Str::isUlid($id)) {

            return null;
        }

        $update = $data instanceof NorIntoDB ? $data->getData() : $data;
        if (!isset($update['updated_at'])) {
            $update['updated_at'] = now()->format(DateFormat::TIMESTAMP_DB);
        }

        return self::query()
            ->where('id', $id)
            ->update($update);
    }

    public static function checkExists(array $credentials): bool
    {
        return self::query()
            ->where(function ($query) use ($credentials) {
                foreach ($credentials as $key => $value) {
                    $query->orWhere($key, $value);
                }
            })
            ->exists();
    }

    public static function editCoverPhoto($id, $image): void
    {
        self::query()
            ->where('id', $id)
            ->update([
                'background' => $image
            ]);
    }

    public static function getPermission(string $id): ?object
    {
        return DB::connection('pgsql_main')
            ->table('admin.user_permission')
            ->where('user_id', $id)
            ->distinct()
            ->first(['permission']);
    }

    public static function setStatus($user_id, $status): int
    {
        return self::query()
            ->where('id', $user_id)
            ->update([
                'status' => $status
            ]);
    }
}
