<?php

namespace Jsefton\MigrateEnv\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class MigrateEnv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:env {--env=} {--stored=} {--host=} {--database=} {--username=} {--password=} {--port=3306} {--task=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate to specific environments';

    /**
     * The set environment to target
     * @var
     */
    protected $env;

    /**
     * The stored or set database details
     * @var
     */
    protected $dbHost;
    protected $dbName;
    protected $dbUser;
    protected $dbPassword;
    protected $dbPort = 3306;

    /**
     * Used to track what task to run
     * @var
     */
    protected $task;

    /**
     * Option to use stored details
     * @var bool
     */
    protected $useStored = false;


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected function processOptions()
    {
        if($this->option('env')) {
            $this->env = $this->option('env');
        }

        if($this->option('stored')) {
            $this->useStored = true;
        }

        if($this->option('host')) {
            $this->dbHost = $this->option('host');
        }

        if($this->option('database')) {
            $this->dbName = $this->option('database');
        }

        if($this->option('username')) {
            $this->dbUser = $this->option('username');
        }

        if($this->option('password')) {
            $this->dbPassword = $this->option('password');
        }

        if($this->option('port')) {
            $this->dbPort = $this->option('port');
        }

        if($this->option('task')) {
            $this->task = $this->option('task');
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->processOptions();

        // Ask for environment target (simply add more to support additional environment targets)
        if(!$this->env) {
            $this->env = $this->choice('Please select an environment target?', config('migrate-env.environments'));
            $this->info('Environment: ' . $this->env);
        }

        // Stored json file of credentials (Note the storage folder should be ignored by git, therefore not committed)
        $filePath = storage_path() . "/app/db-migration-" . str_slug($this->env) . ".json";

        // Check if we have previously saved details for the set environment
        if(file_exists($filePath)) {
            if($this->useStored) {
                $details = json_decode(file_get_contents($filePath), true);
            } else {
                if(!$this->dbHost) {
                    if ($this->confirm('Settings for this environment have been found, do you want to use stored settings?')) {
                        $details = json_decode(file_get_contents($filePath), true);
                    }
                }
            }
        }

        // If not stored, or said no to using stored details, then re-ask for all the needed information
        if(!isset($details)) {
            if(!$this->dbHost) {
                $this->dbHost = $this->ask('Please enter the Database Host');
            }

            if(!$this->dbName) {
                $this->dbName = $this->ask('Please enter the Database Name');
            }

            if(!$this->dbUser) {
                $this->dbUser = $this->ask('Please enter the Database User');
            }

            if(!$this->dbPassword) {
                $this->dbPassword = $this->secret('Please enter the Database Password');
            }

            if(!$this->dbPort) {
                $this->dbPort = $this->ask('Please enter the Database Port (default: 3306)', 3306);
            }

            $details = [
                'env' => $this->env,
                'dbHost' => $this->dbHost,
                'dbName' => $this->dbName,
                'dbUser' => $this->dbUser,
                'dbPassword' => $this->dbPassword,
                'dbPort' => $this->dbPort
            ];

            // Store the environment credentials in a json file inside storage/app
            file_put_contents($filePath, json_encode($details));
        }

        // Create temp connection details
        $tempConnection = [
            'driver' => 'mysql',
            'host' => $details['dbHost'],
            'port' => $details['dbPort'],
            'database' => $details['dbName'],
            'username' => $details['dbUser'],
            'password' => $details['dbPassword'],
            'unix_socket' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ];

        // Set the database connection details for this request.
        config(['database.connections.mysql_env_migration_temp' => $tempConnection]);

        // Attempt to reconnect if previous connection
        DB::reconnect('mysql_env_migration_temp');

        // Test the database connection before doing anything else
        try {
            DB::connection('mysql_env_migration_temp')->getPdo();
        } catch (\Exception $e) {
            $this->error("Could not connect to the database.  Please check your configuration.");
            exit;
        }


        if($this->task) {
            $task = $this->task;
        } else {
            // Ask for the choice of command to be ran against the remote connection
            $task = $this->choice('Please select a migration task to run', ['migrate', 'rollback', 'status', 'refresh', 'reset', 'custom']);

            // If not running migrate then build up correct syntax
            if ($task !== "migrate") {
                if($task === "custom") {
                    $task = $this->ask('Please enter the command you want to run (e.g. db:seed)');
                } else {
                    $task = "migrate:" . $task;
                }
            }
        }

        // Show output of task about to be ran
        $this->info('Running task: ' . $task);

        // Execute the migrations
        Artisan::call($task, ['--database' => 'mysql_env_migration_temp']);
        $this->info(Artisan::output());

    }
}
