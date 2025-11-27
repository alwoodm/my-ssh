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
        $hosts = Host::with(['users'])
            ->orderByDesc('last_login_at')
            ->orderBy('alias')
            ->get();

        if ($hosts->isEmpty()) {
            info("No hosts found. Run 'host:add' to create one.");

            return;
        }

        $rows = $hosts->map(function (Host $host) {
            $users = $host->users->pluck('username')->join(', ');

            $lastLoginStr = $host->last_login_at ? $host->last_login_at->diffForHumans() : 'Never';

            return [
                $host->alias,
                $host->hostname,
                $users,
                $lastLoginStr,
            ];
        })->toArray();

        table(
            headers: ['Alias', 'Hostname', 'Users', 'Last Login'],
            rows: $rows
        );
    }
}
