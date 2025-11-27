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
            ['Alias', 'Hostname', 'Users', 'Last Login'],
            [
                ['prod', '1.1.1.1', 'user1', 'Never'],
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

test('host:delete removes host and users', function () {
    $host = Host::create(['alias' => 'todelete', 'hostname' => '1.1.1.1']);
    $host->users()->create(['username' => 'user1', 'password' => 'pass']);

    $this->artisan('host:delete todelete')
        ->expectsConfirmation("Are you sure you want to delete host 'todelete' and all its users?", 'yes')
        ->assertExitCode(0);

    $this->assertDatabaseMissing('hosts', ['alias' => 'todelete']);
    $this->assertDatabaseMissing('server_users', ['username' => 'user1']);
});

test('host:edit updates alias', function () {
    $host = Host::create(['alias' => 'oldalias', 'hostname' => '1.1.1.1']);

    $this->artisan('host:edit oldalias')
        ->expectsQuestion('Managing Host: oldalias (1.1.1.1)', 'edit_alias')
        ->expectsQuestion('New Alias', 'newalias')
        ->expectsQuestion('Managing Host: newalias (1.1.1.1)', 'exit')
        ->assertExitCode(0);

    $this->assertDatabaseHas('hosts', ['alias' => 'newalias']);
});

test('connect accepts user argument', function () {
    $host = Host::create(['alias' => 'prod', 'hostname' => '1.1.1.1']);
    $host->users()->create(['username' => 'admin', 'password' => 'secret']);

    $this->artisan('connect prod admin')
        ->expectsOutput('Connecting to prod (admin@1.1.1.1)...')
        ->assertExitCode(0);
});

test('host:edit accepts user argument', function () {
    $host = Host::create(['alias' => 'prod', 'hostname' => '1.1.1.1']);
    $host->users()->create(['username' => 'admin', 'password' => 'secret']);

    $this->artisan('host:edit prod admin')
        ->expectsQuestion('Managing User: admin', 'edit_password')
        ->expectsQuestion('New Password', 'newsecret')
        ->expectsQuestion('Managing User: admin', 'back')
        ->assertExitCode(0);

    $this->assertDatabaseHas('server_users', [
        'username' => 'admin',
        'password' => 'newsecret', // Encrypted but we check existence
    ]);
});

test('host:delete accepts user argument', function () {
    $host = Host::create(['alias' => 'prod', 'hostname' => '1.1.1.1']);
    $host->users()->create(['username' => 'admin', 'password' => 'secret']);
    $host->users()->create(['username' => 'user2', 'password' => 'secret']);

    $this->artisan('host:delete prod admin')
        ->expectsConfirmation("Are you sure you want to delete user 'admin' from host 'prod'?", 'yes')
        ->assertExitCode(0);

    $this->assertDatabaseMissing('server_users', ['username' => 'admin']);
    $this->assertDatabaseHas('server_users', ['username' => 'user2']);
    $this->assertDatabaseHas('hosts', ['alias' => 'prod']);
});
