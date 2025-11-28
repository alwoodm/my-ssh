<?php

namespace App\Providers;

use Illuminate\Console\Application;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->ensureDatabaseExists();
        $this->hideInternalCommands();
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Ensure the database directory and file exist, and run migrations.
     */
    private function ensureDatabaseExists(): void
    {
        $path = (getenv('HOME') ?? getenv('USERPROFILE')) . '/.myssh';
        $database = $path . '/database.sqlite';

        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }

        if (! file_exists($database)) {
            touch($database);
        }

        // Silently run migrations
        Artisan::call('migrate', ['--force' => true]);
    }

    /**
     * Hide internal commands from the list.
     */
    private function hideInternalCommands(): void
    {
        $commandsToHide = [
            'migrate',
            'migrate:fresh',
            'migrate:install',
            'migrate:refresh',
            'migrate:reset',
            'migrate:rollback',
            'migrate:status',
            'db:seed',
            'db:wipe',
            'make:command',
            'make:factory',
            'make:migration',
            'make:model',
            'make:seeder',
            'make:test',
            'stub:publish',
            'test',
        ];

        $artisan = Artisan::getFacadeRoot();
        $allCommands = $artisan->all();

        foreach ($allCommands as $name => $command) {
            if (in_array($name, $commandsToHide)) {
                $command->setHidden(true);
            }
        }
    }
}
