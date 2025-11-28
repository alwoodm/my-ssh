<?php

use App\Models\Host;
use Laravel\Prompts\Prompt;

test('host:add creates a new host and user', function () {
    Prompt::fake([
        'Host Alias' => 'prod',
        'Hostname / IP' => '192.168.1.100',
        'Username' => 'admin',
        'Password' => 'secret123',
    ]);

    $this->artisan('host:add')
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

    Prompt::fake([
        'Username' => 'deployer',
        'Password' => 'newpass',
    ]);

    $this->artisan('user:add prod')
        ->assertExitCode(0);

    $this->assertDatabaseHas('server_users', [
        'host_id' => $host->id,
        'username' => 'deployer',
    ]);
});

test('host:delete removes host and users', function () {
    $host = Host::create(['alias' => 'todelete', 'hostname' => '1.1.1.1']);
    $host->users()->create(['username' => 'user1', 'password' => 'pass']);

    Prompt::fake([
        "Are you sure you want to delete host 'todelete'?" => true,
    ]);

    $this->artisan('host:delete todelete')
        ->assertExitCode(0);

    $this->assertDatabaseMissing('hosts', ['alias' => 'todelete']);
    $this->assertDatabaseMissing('server_users', ['username' => 'user1']);
});

test('host:edit updates alias', function () {
    $host = Host::create(['alias' => 'oldalias', 'hostname' => '1.1.1.1']);

    Prompt::fake([
        'Managing Host: oldalias (1.1.1.1)' => 'edit_alias',
        'New Alias' => 'newalias',
        'Managing Host: newalias (1.1.1.1)' => 'exit',
    ]);

    $this->artisan('host:edit oldalias')
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

    Prompt::fake([
        'Managing User: admin' => 'edit_password',
        'New Password' => 'newsecret',
        'Managing User: admin' => 'back',
    ]);

    $this->artisan('host:edit prod admin')
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

    Prompt::fake([
        "Are you sure you want to delete user 'admin'?" => true,
    ]);

    $this->artisan('host:delete prod admin')
        ->assertExitCode(0);

    $this->assertDatabaseMissing('server_users', ['username' => 'admin']);
    $this->assertDatabaseHas('server_users', ['username' => 'user2']);
    $this->assertDatabaseHas('hosts', ['alias' => 'prod']);
});
