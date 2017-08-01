## Laravel Migrate Enviornments

This Artisan command allows you to migrate to external or additional environments by switching out the database details in an easy to use prompt interface.

This can be useful if you external staging and production environments and need to test or run migrations against the databases. 

This tool could be adapted easily to become part of a build process to automatically run, but just be wary over where you store the credentials.

The credentials are stored in a json file for future use within /storage/app (This should not be committed into your repository, please check your site gitignore if it comes up).

### Installation

First copy the `MigrateEnv.php` file into `app/Console/Commands`

Next you need to register your command by editing `app/Console/Kernal.php` to add the new command into your `$commands` array.
```php
protected $commands = [
    Commands\MigrateEnv::class
];
```

### Usage

When in the CLI run the below to execute the command and begin the prompts

```bash
php artisan migrate:env
```