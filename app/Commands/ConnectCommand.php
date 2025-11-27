<?php

namespace App\Commands;

use App\Models\Host;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\select;

class ConnectCommand extends Command
{
    protected $signature = 'connect {alias? : The host alias} {--u|user= : Optional specific user}';
    protected $description = 'Connect to a host via SSH';

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

        $username = $this->option('user');
        $user = null;

        if ($username) {
            $user = $host->users()->where('username', $username)->first();
            if (! $user) {
                error("User '{$username}' not found for host '{$alias}'.");

                return;
            }
        } else {
            $users = $host->users;
            if ($users->isEmpty()) {
                error("No users found for host '{$alias}'.");

                return;
            }

            if ($users->count() === 1) {
                $user = $users->first();
            } else {
                $selectedUsername = select(
                    label: 'Select a user',
                    options: $users->pluck('username')->toArray()
                );
                $user = $users->where('username', $selectedUsername)->first();
            }
        }

        $password = $user->password; // Decrypted via cast
        $port = $host->port;
        $target = "{$user->username}@{$host->hostname}";

        // Check for sshpass
        $hasSshpass = ! empty(shell_exec('which sshpass'));

        if ($hasSshpass) {
            // Securely pass password via environment variable to avoid process listing exposure
            $cmd = "SSHPASS='{$password}' sshpass -e ssh -p {$port} {$target}";
        } else {
            info('sshpass not found. Running standard ssh command.');
            info("Password: {$password}");
            $cmd = "ssh -p {$port} {$target}";
        }

        info("Connecting to {$alias} ({$target})...");

        passthru($cmd);
    }
}
