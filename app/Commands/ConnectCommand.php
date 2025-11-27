<?php

namespace App\Commands;

use App\Models\Host;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\select;

class ConnectCommand extends Command
{
    protected $signature = 'connect {alias? : The host alias} {user? : The specific user} {--u|user= : (Deprecated) Optional specific user}';
    protected $description = 'Connect to a host via SSH';

    public function handle(): void
    {
        $alias = $this->argument('alias');

        if (! $alias) {
            // Sort hosts by the most recent login
            $aliases = Host::orderByDesc('last_login_at')
                ->orderBy('alias')
                ->pluck('alias')
                ->toArray();

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

        // Update host last login time
        $host->update(['last_login_at' => now()]);

        $username = $this->argument('user') ?: $this->option('user');
        $user = null;

        if ($username) {
            $user = $host->users()->where('username', $username)->first();
            if (! $user) {
                error("User '{$username}' not found for host '{$alias}'.");

                return;
            }
        } else {
            // Sort users by last login
            $users = $host->users()->orderByDesc('last_login_at')->orderBy('username')->get();

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

        // Update user last login time
        $user->update(['last_login_at' => now()]);

        $password = $user->password; // Decrypted via cast
        $target = "{$user->username}@{$host->hostname}";

        info("Connecting to {$alias} ({$target})...");

        // Strategy 1: sshpass
        if (! empty(shell_exec('which sshpass'))) {
            $cmd = "SSHPASS='{$password}' sshpass -e ssh {$target}";
        }
        // Strategy 2: Clipboard (macOS pbcopy)
        elseif (! empty(shell_exec('which pbcopy'))) {
            // Copy password to clipboard
            if (! app()->runningUnitTests()) {
                $proc = proc_open('pbcopy', [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes);
                if (is_resource($proc)) {
                    fwrite($pipes[0], $password);
                    fclose($pipes[0]);
                    proc_close($proc);
                    info('Password copied to clipboard! Paste it when prompted.');
                } else {
                    error('Failed to access clipboard.');
                }
            }

            $cmd = "ssh {$target}";
        }
        // Strategy 3: Manual fallback
        else {
            info("Neither 'sshpass' nor 'expect' found. You will need to type the password manually.");
            info("Password: {$password}");
            $cmd = "ssh {$target}";
        }

        if (app()->runningUnitTests()) {
            // For testing, we just want to see which command was generated
            // We simplify the expect command output for easier assertion
            if (str_contains($cmd, 'expect')) {
                info('Command: expect script execution');
            } else {
                info("Command: {$cmd}");
            }

            return;
        }

        passthru($cmd);
    }
}
