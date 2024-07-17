<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;


class MakeSchema extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:make-schema {--connection=} {--table=} {--path=} {--schema=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description: Make schema of base table';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $connection = $this->option('connection') ?? env('DB_CONNECTION');

        $table = $this->option('table');

        $config = config('database.connections');

        $schema = $this->option('schema');

        if ($config) {
            if ($connection == 'mariadb') {
                $connection = 'mysql';
            }
            if (!array_key_exists($connection, $config)) {
                print "Connection is not supported! Try:\n";
                foreach ($config as $key => $value) {
                    if ($key == 'mysql') {
                        print "** $key ** (or mariadb)\n";
                    } else {
                        print "** $key **\n";
                    }
                }
            } else {
                $table_prefix = explode('.', $table);
                if (isset($table_prefix[1])) {
                    $table = $table_prefix[1];

                }
                $result = DB::connection($connection)
                    ->table('information_schema.tables as tab')
                    ->select('tab.table_schema as database_name', 'tab.table_name', 'col.column_name as column_', 'col.data_type as type_')
                    ->join('information_schema.columns as col', function ($join) {
                        $join->on('col.table_schema', '=', 'tab.table_schema')
                            ->on('col.table_name', '=', 'tab.table_name')
                            ->where('col.column_name', 'LIKE', '%');
                    })
                    ->where('tab.table_type', '=', 'BASE TABLE')
                    ->where('tab.table_name', '=', $table)
                    ->when($schema == null, function ($query) {
                        return $query->orderBy('tab.table_schema');
                    })
                    ->when($schema != null, function ($query) use ($schema) {
                        return $query->where('tab.table_schema', '=', $schema);
                    })
                    ->orderBy('tab.table_name')
                    ->get()
                    ->all();

                $data_type = [
                    'boolean'                     => ['type' => 'BOOL', 'init' => 'initBool'],
                    'bool'                        => ['type' => 'BOOL', 'init' => 'initBool'],

                    'smallint'                    => ['type' => 'INT', 'init' => 'initInt'],
                    'mediumint'                   => ['type' => 'INT', 'init' => 'initInt'],
                    'tinyint'                     => ['type' => 'INT', 'init' => 'initInt'],
                    'bigint'                      => ['type' => 'INT', 'init' => 'initInt'],
                    'int'                         => ['type' => 'INT', 'init' => 'initInt'],
                    'integer'                     => ['type' => 'INT', 'init' => 'initInt'],
                    'bigserial'                   => ['type' => 'INT', 'init' => 'initInt'],
                    'serial'                      => ['type' => 'INT', 'init' => 'initInt'],

                    'double precision'            => ['type' => 'FLOAT', 'init' => 'initFloat'],
                    'decimal'                     => ['type' => 'FLOAT', 'init' => 'initFloat'],
                    'float'                       => ['type' => 'FLOAT', 'init' => 'initFloat'],
                    'double'                      => ['type' => 'FLOAT', 'init' => 'initFloat'],

                    'character varying'           => ['type' => 'STRING', 'init' => 'initString'],
                    'character'                   => ['type' => 'STRING', 'init' => 'initString'],
                    'varchar'                     => ['type' => 'STRING', 'init' => 'initString'],
                    'text'                        => ['type' => 'STRING', 'init' => 'initString'],

                    'jsonb'                       => ['type' => 'STRING', 'init' => 'initObject'],
                    'json'                        => ['type' => 'STRING', 'init' => 'initObject'],

                    'timestamp without time zone' => ['type' => 'STRING', 'init' => 'initCarbon'],
                    'timestamp'                   => ['type' => 'STRING', 'init' => 'initCarbon'],
                    'date'                        => ['type' => 'STRING', 'init' => 'initCarbon'],
                    'datetime'                    => ['type' => 'STRING', 'init' => 'initCarbon'],
                ];

                if ($result) {
                    if ($this->option('path')) {
                        $path = app_path('/Consts/' . $this->option('path') . '/');
                    } else {
                        $path = app_path('/Consts/');
                    }

                    $filename = 'DB' . Str::ucfirst(Str::camel(Str::singular($result[0]->table_name))) . 'Fields.php';

                    if (!File::isDirectory($path)) {
                        File::makeDirectory($path, 0775, true);
                    }

                    $content = "<?php\n\n";

                    $content
                        .= "namespace App\Consts\Schema;\n" .
                        "use App\Consts\DbTypes;\n";

                    $content .= "\nabstract class " . 'DB' . Str::ucfirst(Str::camel(Str::singular($result[0]->table_name)))
                        . "Fields\n{\n";

                    $content .= "\tconst " . Str::upper($result[0]->table_name) . " = [\n";

                    foreach ($result as $value) {
                        $content .= "\t\t'$value->column_' => [\n\t\t\t'type' => DbTypes::{$data_type[Str::lower($value->type_)]['type']},\n\t\t\t'cache' => ";
                        if (Str::lower($value->column_) == 'update_at') {
                            $content .= "false,\n\t\t],\n";
                        } else {
                            $content .= "true,\n\t\t],\n";
                        }
                    }

                    $content .= "\t];\n}";

                    File::put($path . $filename, $content);
                } else {
                    print "Table '$table' is not found\n";
                }
            }
        }
    }
}
