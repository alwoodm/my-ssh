<?php

namespace App\Commands;

use App\Models\Host;
use Illuminate\Support\Facades\DB;
use LaravelZero\Framework\Commands\Command;

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
    protected $signature = 'host:add';

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
        $alias = text(
            label: 'Host Alias',
            placeholder: 'e.g. production',
            required: true,
            validate: fn (string $value) => match (true) {
                Host::where('alias', $value)->exists() => 'This alias is already taken.',
                default => null,
            }
        );

        $hostname = text(
            label: 'Hostname / IP',
            placeholder: 'e.g. 192.168.1.1 or example.com',
            required: true
        );

        $port = text(
            label: 'Port',
            default: '22',
            validate: fn (string $value) => is_numeric($value) ? null : 'Port must be a number.'
        );

        $username = text(
            label: 'Username',
            required: true
        );

        $password = password(
            label: 'Password',
            required: true
        );

        DB::transaction(function () use ($alias, $hostname, $port, $username, $password) {
            $host = Host::create([
                'alias' => $alias,
                'hostname' => $hostname,
                'port' => (int) $port,
            ]);

            $host->users()->create([
                'username' => $username,
                'password' => $password,
            ]);
        });

        info("Host '{$alias}' added successfully.");
    }
}
