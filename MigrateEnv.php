<?php

namespace App\Console\Commands;

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
    protected $signature = 'migrate:env';

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
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Ask for environment target (simply add more to support additional environment targets)
        $this->env = $this->choice('Please select an environment target?', ['Local', 'QA', 'Production']);
        $this->info('Environment: ' . $this->env);

        // Stored json file of credentials (Note the storage folder should be ignored by git, therefore not committed)
        $filePath = storage_path() . "/app/db-migration-" . $this->env . ".json";

        // Check if we have previously saved details for the set environment
        if(file_exists($filePath)) {
            if ($this->confirm('Settings for this environment have been found, do you want to use stored settings?')) {
                $details = json_decode(file_get_contents($filePath), true);
            }
        }

        // If not stored, or said no to using stored details, then re-ask for all the needed information
        if(!isset($details)) {
            $this->dbHost = $this->ask('Please enter the Database Host');
            $this->dbName = $this->ask('Please enter the Database Name');
            $this->dbUser = $this->ask('Please enter the Database User');
            $this->dbPassword = $this->secret('Please enter the Database Password');
            $this->dbPort = $this->ask('Please enter the Database Port (default: 3306)', 3306);

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

        // Set the database connection details for this request.
        config(['database.connections.mysql.host' => $details['dbHost']]);
        config(['database.connections.mysql.port' => $details['dbPort']]);
        config(['database.connections.mysql.database' => $details['dbName']]);
        config(['database.connections.mysql.username' => $details['dbUser']]);
        config(['database.connections.mysql.password' => $details['dbPassword']]);

        // Test the database connection before doing anything else
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $this->error("Could not connect to the database.  Please check your configuration.");
            exit;
        }

        // Execute the migrations
        Artisan::call('migrate');
        $this->info(Artisan::output());

    }
}
