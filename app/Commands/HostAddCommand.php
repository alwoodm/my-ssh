<?php

namespace App\Commands;

use App\Models\Host;
use Illuminate\Support\Facades\DB;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class HostAddCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'host:add {alias? : The host alias} {hostname? : The hostname or IP} {user? : The username}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Add a new SSH host and user';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $alias = $this->argument('alias') ?? text(
            label: 'Host Alias',
            placeholder: 'e.g. production',
            required: true,
            validate: fn (string $value) => match (true) {
                Host::where('alias', $value)->exists() => 'This alias is already taken.',
                default => null,
            }
        );

        // If alias was provided as argument, we still need to validate it
        if ($this->argument('alias') && Host::where('alias', $alias)->exists()) {
            error('This alias is already taken.');

            return;
        }

        $hostname = $this->argument('hostname') ?? text(
            label: 'Hostname / IP',
            placeholder: 'e.g. 192.168.1.1 or example.com',
            required: true
        );

        $username = $this->argument('user') ?? text(
            label: 'Username',
            required: true
        );

        $password = password(
            label: 'Password',
            required: true
        );

        DB::transaction(function () use ($alias, $hostname, $username, $password) {
            $host = Host::create([
                'alias' => $alias,
                'hostname' => $hostname,
            ]);

            $host->users()->create([
                'username' => $username,
                'password' => $password,
            ]);
        });

        info("Host '{$alias}' added successfully.");
    }
}
