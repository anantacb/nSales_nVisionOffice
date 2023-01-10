<?php

use App\Helpers\SqlFormatter;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/test', function () {

    $databaseName = "nvisiondb_b2bmaster";
    $tableName = 'tasks';

    $columns = [
        [
            'column' => 'Id',
            'type' => 'int',
            'size' => 11,
            'auto_increment' => true,
            'nullable' => false,
            'default' => null,
            'primary_key' => true
        ],
        [
            'column' => 'InsertTime',
            'type' => 'datetime',
            'size' => 255,
            'auto_increment' => false,
            'nullable' => false,
            'default' => null,
            'primary_key' => false
        ],
        [
            'column' => 'start_date',
            'type' => 'datetime',
            'size' => null,
            'auto_increment' => false,
            'nullable' => false,
            'default' => null,
            'primary_key' => false
        ],
        [
            'column' => 'insert_time',
            'type' => 'datetime',
            'size' => null,
            'auto_increment' => false,
            'nullable' => false,
            'default' => 'current_timestamp',
            'primary_key' => false
        ],
        [
            'column' => 'update_time',
            'type' => 'datetime',
            'size' => null,
            'auto_increment' => false,
            'nullable' => true,
            'default' => null,
            'primary_key' => false
        ]
    ];

    $sql = "CREATE TABLE IF NOT EXISTS";

    if ($databaseName) {
        $sql .= "`$databaseName`.";
    }

    $sql .= "`$tableName` ( ";

    $columnStrings = [];
    foreach ($columns as $key => $column) {
        $dataTypeString = "";
        switch ($column['type']) {
            case 'int':
                $dataTypeString = "int(" . $column['size'] . ")";
                break;
            case 'varchar':
                $dataTypeString = "varchar(" . $column['size'] . ")";
                break;
            case 'text':
                $dataTypeString = "text";
                break;
            case 'datetime':
                $dataTypeString = "datetime";
                break;
            case 'double':
                $dataTypeString = "double";
                break;
            case 'longtext':
                $dataTypeString = "longtext";
                break;
            case 'tinytext':
                $dataTypeString = "tinytext";
                break;
            case 'tinyint':
                $dataTypeString = "tinyint";
                break;
            case 'blob':
                $dataTypeString = "blob";
                break;
            case 'longblob':
                $dataTypeString = "longblob";
                break;
            case (bool)preg_match('/enum\(.*\)/', $column['type']):
                $dataTypeString = $column['type'];
                break;
            default:
                break;
        }

        $columnString = "`" . $column['column'] . "`" . " " . $dataTypeString . " ";

        if ($column['auto_increment']) {
            $columnString .= "AUTO_INCREMENT ";
        }

        if ($column['primary_key']) {
            $columnString .= "PRIMARY KEY ";
        } else {
            if ($column['nullable']) {
                $columnString .= "null ";
            } else {
                $columnString .= "not null ";
            }
        }

        if ($column['default']) {
            $columnString .= "default " . $column['default'] . " ";
        }

        $columnStrings[] = trim($columnString);
    }

    $sql = $sql . implode(",", $columnStrings) . ") ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

    return SqlFormatter::format($sql);
    dd(SqlFormatter::format($sql));
    $template = "
    CREATE TABLE IF NOT EXISTS tasks (
    task_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    start_date DATE,
    due_date DATE,
    status TINYINT NOT NULL,
    priority TINYINT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)  ENGINE=INNODB;
    ";


    /*$migrator = app('migrator');
    // Now that we have the connections we can resolve it and pretend to run the
    // queries against the database returning the array of raw SQL statements
    // that would get fired against the database system for this migration.
    $db = $migrator->resolveConnection(null);

    $func = function () {
        Schema::connection('mysql')->create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    };

    $sql = $db->pretend(function () use ($func) {
        $func();
    });

    dd($sql);*/
});

/*Route::get('/', function () {
    return view('app');
});*/

Route::get('{any}', function () {
    return view('app');
})->where('any', '.*');

//Auth::routes();

//Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
