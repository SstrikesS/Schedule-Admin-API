<?php

namespace App\Models\V1\Admin;

use App\Consts\DateFormat;
use App\Jobs\IpLocationJob;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ActivityModel extends Model
{
    use HasFactory;

    protected $connection = "pgsql_main";
    protected $table      = "admin.user_activity";

    public static function getOldPassword(AUser $user): array
    {
        return self::query()
            ->where('user_id', $user->getAttribute('id'))
            ->where('key', 'admin.user.password.reset')
            ->orWhere('key', 'admin.user.register')
            ->get()
            ->all();
    }

    public static function getUserActivities(Request $request, array $filter): LengthAwarePaginator|Collection
    {
        $query = self::query()
            ->where(function ($query) use ($filter) {
                if ($filter['search_by']) {
                    $query->where($filter['search_by'], 'LIKE', $filter['key']);
                }
                $query->where('user_id', $filter['user_id']);
            })
            ->orderBy($filter['sort_by'], $filter['sort']);

        if (!isset($filter['limit'])) {

            return $query->get($filter['select']);
        } else {

            return $query->paginate($filter['limit'], $filter['select'], "{$filter['page']}", $filter['page']);
        }
    }

    public static function getUserLogin(Request $request, array $filter): LengthAwarePaginator|Collection
    {
        $table_partition = DB::connection('pgsql_main')
            ->table('admin.user_activity')
            ->select($filter['select'])
            ->selectRaw('ROW_NUMBER() OVER (PARTITION BY "id_session" ORDER BY created_at DESC) AS rn')
            ->where('user_id', '=', $filter['user_id'])
            ->orderBy($filter['sort_by'], $filter['sort']);

        $query           = DB::connection('pgsql_main')
            ->table(DB::raw("({$table_partition->toSql()}) as login_history"))
            ->mergeBindings($table_partition)
            ->where('rn', '=', 1);

        if (!isset($filter['limit'])) {
            return $query->get($filter['select']);
        } else {
            return $query->paginate($filter['limit'], $filter['select'], "{$filter['page']}", $filter['page']);
        }
    }

    public static function addUserActivity(array $data): string
    {
        if (!isset($data['id'])) {
            $data['id'] = generateUlid();
        }

        if (!isset($data['user_id'])) {
            $data['user_id'] = Auth::user()->getAuthIdentifier();
        }

        if (!isset($data['ip'])) {
            $data['ip'] = request()->getClientIp();
//            $data['location'] = '[ ' . geoip()->getLocation($data['ip'])->lat . ',' . geoip()->getLocation($data['ip'])->lon . ' ]';
        }

        if (!isset($data['user_agent'])) {
            $data['user_agent'] = request()->userAgent();
        }

        if (!isset($data['created_at'])) {
            $data['created_at'] = now()->format(DateFormat::TIMESTAMP_DB);
        }
        $send_data = [
            'id' => $data['id'],
            'ip' => $data['ip'],
        ];
        IpLocationJob::dispatch($send_data,'admin')->onConnection('database');

        return self::query()
            ->insertGetId($data);
    }
}
