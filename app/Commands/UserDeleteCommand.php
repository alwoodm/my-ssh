<?php

namespace App\Commands;

use App\Models\Host;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\select;

class UserDeleteCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'user:delete {alias? : The host alias} {user? : The user to delete}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Delete a user from a host';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $alias = $this->argument('alias');
        $targetUser = $this->argument('user');

        if (! $alias) {
            $aliases = Host::pluck('alias')->toArray();
            if (empty($aliases)) {
                error('No hosts found.');

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

        if (! $targetUser) {
            $users = $host->users()->pluck('username')->toArray();
            if (empty($users)) {
                error("No users found for host '{$alias}'.");

                return;
            }
            $targetUser = select(
                label: 'Select a user to delete',
                options: $users,
                scroll: 10
            );
        }

        $user = $host->users()->where('username', $targetUser)->first();
        if (! $user) {
            error("User '{$targetUser}' not found for host '{$alias}'.");

            return;
        }

        $confirmed = confirm(
            label: "Are you sure you want to delete user '{$targetUser}' from host '{$alias}'?",
            default: false,
            yes: 'Yes, delete it',
            no: 'No, cancel'
        );

        if ($confirmed) {
            $user->delete();
            info("User '{$targetUser}' deleted successfully.");
        } else {
            info('Deletion cancelled.');
        }
    }
}
