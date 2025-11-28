<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Updater Strategy
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the updater strategies below you wish
    | to use as your default strategy for your application.
    |
    */

    'strategy' => 'github',

    /*
    |--------------------------------------------------------------------------
    | Updater Strategies
    |--------------------------------------------------------------------------
    |
    | Here you may specify the configuration for each of the updater strategies
    | supported by Laravel Zero.
    |
    */

    'strategies' => [
        'github' => [
            'repository_owner' => 'alwoodm',
            'repository_name' => 'my-ssh',
            'token' => env('GITHUB_TOKEN'),
        ],
    ],

];
