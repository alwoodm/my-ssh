<?php

namespace App\Commands;

use App\Models\Host;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class UserAddCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'user:add {alias? : The host alias} {username? : The username}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Add a user to an existing host';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $alias = $this->argument('alias');

        if (! $alias) {
            $aliases = Host::pluck('alias')->toArray();
            if (empty($aliases)) {
                error('No hosts found. Run host:add first.');

                return;
            }
            $alias = select(
                label: 'Select a host',
                options: $aliases,
                scroll: 10
            );
        }

        $host = Host::where('alias', $alias)->first();

        if (! $host) {
            error("Host '{$alias}' not found.");

            return;
        }

        $username = $this->argument('username');

        if ($username) {
            if ($host->users()->where('username', $username)->exists()) {
                error('This user already exists for this host.');

                return;
            }
        } else {
            $username = text(
                label: 'Username',
                required: true,
                validate: fn (string $value) => match (true) {
                    $host->users()->where('username', $value)->exists() => 'This user already exists for this host.',
                    default => null,
                }
            );
        }

        $password = password(
            label: 'Password',
            required: true
        );

        $host->users()->create([
            'username' => $username,
            'password' => $password,
        ]);

        info("User '{$username}' added to host '{$alias}' successfully.");
    }
}
