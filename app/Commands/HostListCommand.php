<?php

namespace App\Commands;

use App\Models\Host;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\info;
use function Laravel\Prompts\table;

class HostListCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'host:list';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'List all SSH hosts';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $hosts = Host::with('users')->get();

        if ($hosts->isEmpty()) {
            info("No hosts found. Run 'host:add' to create one.");

            return;
        }

        $rows = $hosts->map(function (Host $host) {
            $users = $host->users->pluck('username')->join(', ');

            return [
                $host->alias,
                $host->hostname,
                $host->port,
                $users,
            ];
        })->toArray();

        table(
            headers: ['Alias', 'Hostname', 'Port', 'Users'],
            rows: $rows
        );
    }
}
