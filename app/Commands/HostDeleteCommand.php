<?php

namespace App\Commands;

use App\Models\Host;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\select;

class HostDeleteCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'host:delete {alias? : The host alias}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Delete a host and its users';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $alias = $this->argument('alias');

        if (! $alias) {
            $aliases = Host::pluck('alias')->toArray();
            if (empty($aliases)) {
                error('No hosts found.');

                return;
            }
            $alias = select(
                label: 'Select a host to delete',
                options: $aliases,
                scroll: 10
            );
        }

        $host = Host::where('alias', $alias)->first();

        if (! $host) {
            error("Host '{$alias}' not found.");

            return;
        }

        $confirmed = confirm(
            label: "Are you sure you want to delete host '{$alias}' and all its users?",
            default: false,
            yes: 'Yes, delete it',
            no: 'No, cancel'
        );

        if ($confirmed) {
            $host->delete();
            info("Host '{$alias}' deleted successfully.");
        } else {
            info('Deletion cancelled.');
        }
    }
}
