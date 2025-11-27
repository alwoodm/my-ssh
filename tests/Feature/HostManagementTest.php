<?php

use App\Models\Host;

test('host:add creates a new host and user', function () {
    $this->artisan('host:add')
        ->expectsQuestion('Host Alias', 'prod')
        ->expectsQuestion('Hostname / IP', '192.168.1.100')
        ->expectsQuestion('Username', 'admin')
        ->expectsQuestion('Password', 'secret123')
        ->assertExitCode(0);

    $this->assertDatabaseHas('hosts', [
        'alias' => 'prod',
        'hostname' => '192.168.1.100',
    ]);

    $host = Host::where('alias', 'prod')->first();

    $this->assertDatabaseHas('server_users', [
        'host_id' => $host->id,
        'username' => 'admin',
    ]);
});

test('host:list displays hosts', function () {
    $host = Host::create(['alias' => 'prod', 'hostname' => '1.1.1.1']);
    $host->users()->create(['username' => 'user1', 'password' => 'pass']);

    $this->artisan('host:list')
        ->expectsTable(
            ['Alias', 'Hostname', 'Users'],
            [
                ['prod', '1.1.1.1', 'user1'],
            ]
        )
        ->assertExitCode(0);
});

test('user:add attaches user to host', function () {
    $host = Host::create(['alias' => 'prod', 'hostname' => '1.1.1.1']);
    $host->users()->create(['username' => 'admin', 'password' => 'secret']);

    $this->artisan('user:add prod')
        ->expectsQuestion('Username', 'deployer')
        ->expectsQuestion('Password', 'newpass')
        ->assertExitCode(0);

    $this->assertDatabaseHas('server_users', [
        'host_id' => $host->id,
        'username' => 'deployer',
    ]);
});
