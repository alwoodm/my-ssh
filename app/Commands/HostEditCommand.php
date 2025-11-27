<?php

namespace App\Commands;

use App\Models\Host;
use App\Models\ServerUser;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class HostEditCommand extends Command
{
    protected $signature = 'host:edit {alias? : The host alias}';

    protected $description = 'Edit a host and manage its users';

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
                label: 'Select a host to edit',
                options: $aliases,
                scroll: 10
            );
        }

        $host = Host::where('alias', $alias)->first();

        if (! $host) {
            error("Host '{$alias}' not found.");

            return;
        }

        while (true) {
            // Refresh host data
            $host->refresh();

            $action = select(
                label: "Managing Host: {$host->alias} ({$host->hostname})",
                options: [
                    'edit_alias' => 'Edit Alias',
                    'edit_hostname' => 'Edit Hostname',
                    'manage_users' => 'Manage Users',
                    'delete_host' => 'Delete Host',
                    'exit' => 'Exit',
                ],
                default: 'exit'
            );

            match ($action) {
                'edit_alias' => $this->editAlias($host),
                'edit_hostname' => $this->editHostname($host),
                'manage_users' => $this->manageUsers($host),
                'delete_host' => $this->deleteHost($host),
                'exit' => null,
            };

            if ($action === 'exit' || $action === 'delete_host') {
                break;
            }
        }
    }

    protected function editAlias(Host $host): void
    {
        $newAlias = text(
            label: 'New Alias',
            default: $host->alias,
            required: true,
            validate: fn (string $value) => match (true) {
                $value !== $host->alias && Host::where('alias', $value)->exists() => 'This alias is already taken.',
                default => null,
            }
        );

        $host->update(['alias' => $newAlias]);
        info('Alias updated successfully.');
    }

    protected function editHostname(Host $host): void
    {
        $newHostname = text(
            label: 'New Hostname',
            default: $host->hostname,
            required: true
        );

        $host->update(['hostname' => $newHostname]);
        info('Hostname updated successfully.');
    }

    protected function deleteHost(Host $host): string
    {
        $confirmed = confirm(
            label: "Are you sure you want to delete host '{$host->alias}'?",
            default: false,
            yes: 'Yes, delete it',
            no: 'No, cancel'
        );

        if ($confirmed) {
            $host->delete();
            info("Host '{$host->alias}' deleted successfully.");

            return 'delete_host';
        }

        return 'cancel';
    }

    protected function manageUsers(Host $host): void
    {
        while (true) {
            $host->refresh();
            $users = $host->users;

            $options = $users->pluck('username', 'id')->toArray();
            $options['add_new'] = '+ Add New User';
            $options['back'] = '< Back';

            $selected = select(
                label: "Users for {$host->alias}",
                options: $options,
                scroll: 10
            );

            if ($selected === 'back') {
                break;
            }

            if ($selected === 'add_new') {
                $this->call('user:add', ['alias' => $host->alias]);

                continue;
            }

            // Selected an existing user ID
            $user = $users->find($selected);
            if ($user) {
                $this->manageSingleUser($user);
            }
        }
    }

    protected function manageSingleUser(ServerUser $user): void
    {
        while (true) {
            $user->refresh();
            // Check if user still exists (might have been deleted)
            if (! $user->exists) {
                break;
            }

            $action = select(
                label: "Managing User: {$user->username}",
                options: [
                    'edit_username' => 'Edit Username',
                    'edit_password' => 'Edit Password',
                    'delete_user' => 'Delete User',
                    'back' => 'Back',
                ]
            );

            if ($action === 'back') {
                break;
            }

            match ($action) {
                'edit_username' => $this->editUserUsername($user),
                'edit_password' => $this->editUserPassword($user),
                'delete_user' => $this->deleteUser($user),
            };

            if ($action === 'delete_user') {
                break;
            }
        }
    }

    protected function editUserUsername(ServerUser $user): void
    {
        $newUsername = text(
            label: 'New Username',
            default: $user->username,
            required: true,
            validate: fn (string $value) => match (true) {
                $value !== $user->username && $user->host->users()->where('username', $value)->exists() => 'This username already exists for this host.',
                default => null,
            }
        );

        $user->update(['username' => $newUsername]);
        info('Username updated successfully.');
    }

    protected function editUserPassword(ServerUser $user): void
    {
        $newPassword = password(
            label: 'New Password',
            required: true
        );

        $user->update(['password' => $newPassword]);
        info('Password updated successfully.');
    }

    protected function deleteUser(ServerUser $user): void
    {
        $confirmed = confirm(
            label: "Are you sure you want to delete user '{$user->username}'?",
            default: false,
            yes: 'Yes, delete it',
            no: 'No, cancel'
        );

        if ($confirmed) {
            $user->delete();
            info("User '{$user->username}' deleted successfully.");
        }
    }
}
