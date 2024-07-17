<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;


class MakeStruct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:make-struct {--connection=} {--table=} {--path=} {--schema=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description: Make struct of base table';

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
                    'boolean' => ['type' => 'bool', 'init' => 'initBool'],
                    'bool'    => ['type' => 'bool', 'init' => 'initBool'],

                    'smallint'  => ['type' => 'int', 'init' => 'initInt'],
                    'mediumint' => ['type' => 'int', 'init' => 'initInt'],
                    'tinyint'   => ['type' => 'int', 'init' => 'initInt'],
                    'bigint'    => ['type' => 'int', 'init' => 'initInt'],
                    'int'       => ['type' => 'int', 'init' => 'initInt'],
                    'integer'   => ['type' => 'int', 'init' => 'initInt'],
                    'bigserial' => ['type' => 'int', 'init' => 'initInt'],
                    'serial'    => ['type' => 'int', 'init' => 'initInt'],

                    'double precision' => ['type' => 'float', 'init' => 'initFloat'],
                    'decimal'          => ['type' => 'float', 'init' => 'initFloat'],
                    'float'            => ['type' => 'float', 'init' => 'initFloat'],
                    'double'           => ['type' => 'float', 'init' => 'initFloat'],

                    'character varying' => ['type' => 'string', 'init' => 'initString'],
                    'character'         => ['type' => 'string', 'init' => 'initString'],
                    'varchar'           => ['type' => 'string', 'init' => 'initString'],
                    'text'              => ['type' => 'string', 'init' => 'initString'],

                    'jsonb' => ['type' => 'object', 'init' => 'initObject'],
                    'json'  => ['type' => 'object', 'init' => 'initObject'],

                    'timestamp without time zone' => ['type' => 'Carbon', 'init' => 'initCarbon'],
                    'timestamp'                   => ['type' => 'Carbon', 'init' => 'initCarbon'],
                    'date'                        => ['type' => 'Carbon', 'init' => 'initCarbon'],
                    'datetime'                    => ['type' => 'Carbon', 'init' => 'initCarbon'],
                ];

                if ($result) {
                    if ($this->option('path')) {
                        $path = app_path('/Structs/' . $this->option('path') . '/');
                    } else {
                        $path = app_path('/Structs/');
                    }

                    $filename = Str::ucfirst(Str::camel(Str::singular($result[0]->table_name))) . 'Struct.php';

                    if (!File::isDirectory($path)) {
                        File::makeDirectory($path, 0775, true);
                    }

                    $content = "<?php\n\n";

                    $content
                        .= "namespace App\Structs\\" . str_replace('/', '\\', $this->option('path')) . ";\n\n" .
                        "use App\Libs\Serializer\Normalize;\n" .
                        "use App\Structs\Struct;\n" .
                        "use Illuminate\Support\Carbon;\n";

                    $content .= "\nclass " . Str::ucfirst(Str::camel(Str::singular($result[0]->table_name)))
                        . "Struct extends Struct\n{\n";


                    foreach ($result as $value) {
                        $content .= "\tpublic ?" . $data_type[Str::lower($value->type_)]['type'] . " $$value->column_;\n";
                    }

                    $content .= "\tpublic function __construct(object|array \$data)\n\t{\n\t\tif (is_object(\$data)) {\n\t\t\t\$data = (array)\$data;\n\t\t}\n\n";

                    foreach ($result as $value) {
                        $content .= "\t\t\$this->$value->column_ = Normalize::" . $data_type[Str::lower($value->type_)]['init'] . "(\$data, '$value->column_');\n";
                    }

                    $content .= "\n\t}\n}";

                    File::put($path . $filename, $content);
                } else {
                    print "Table '$table' is not found\n";
                }
            }
        }
    }
}
