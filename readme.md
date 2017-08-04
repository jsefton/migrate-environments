## Laravel Migrate Environments

This Artisan command allows you to migrate to external or additional environments by switching out the database details in an easy to use prompt interface.

This can be useful if you external staging and production environments and need to test or run migrations against the databases.

This tool could be adapted easily to become part of a build process to automatically run, but just be wary over where you store the credentials.

The credentials are stored in a JSON file for future use within /storage/app (This should not be committed into your repository, please check your site .gitignore if it comes up).

### Installation

You will need composer to install this package (get composer). Then run:

```bash
composer require jsefton/migrate-environments
```

#### Register Service Provider

Add the below into your `config/app.php` within `providers` array

```
Jsefton\MigrateEnvironments\MigrateEnvironmentsProvider::class
```

After installation you will need to publish the config file which will allow you to specify your own list of environments. To do this run:

```bash
php artisan vendor:publish --tag=migrate-env
```

This will create the file `config/migrate-env.php` where you can configure your list of environments.


### Usage

When in the CLI run the below to execute the command and begin the prompts

```bash
php artisan migrate:env
```

If you don't want to enter data through prompt questions, you can use arguments to pass in all the needed data. The following arguments are available:

```bash
--env[=ENV]            
--stored[=STORED]
--host[=HOST]
--database[=DATABASE]
--username[=USERNAME]
--password[=PASSWORD]
--port[=PORT]
--task[=TASK]
```

Full usage example with arguments:

```bash
php artisan migrate:env --env=Local --host=192.168.10.10 --database=testing --username=homestead --password=secret --task=migrate:status
```

If you have saved connection details then you can save time with:

```bash
php artisan migrate:env --env=Local --stored=true --task=migrate:status
```

You can also run extra artisan commands such as `db:seed` by selecting a task of `custom` which will then prompt for the task you want to run.



<img src="https://jamie-sefton.co.uk/external/migrate-env-3.gif" width="100%">

