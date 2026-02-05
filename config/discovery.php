<?php

return [
    /*
     * |--------------------------------------------------------------------------
     * | Cache Configuration
     * |--------------------------------------------------------------------------
     * |
     * | Configure caching behavior for discovery operations. Caching significantly
     * | improves performance by avoiding repeated filesystem scans.
     * |
     * | - enabled: Enable/disable caching (recommended: true in production)
     * | - path: Directory where cache files are stored
     * | - ttl: Time-to-live for cache entries (null = forever)
     * |
     */
    'cache' => [
        'enabled' => env('DISCOVERY_CACHE_ENABLED', env('APP_ENV') !== 'local'),
        'path' => base_path('bootstrap/cache/discovery'),
        'ttl' => null,  // Forever in production
    ],

    /*
     * |--------------------------------------------------------------------------
     * | Monorepo Patterns
     * |--------------------------------------------------------------------------
     * |
     * | Define the structure of your monorepo for automatic namespace resolution.
     * | These patterns help the Discovery package understand how to convert file
     * | paths to fully qualified class names.
     * |
     * | Placeholders:
     * | - {package}: Package name from packages/* directory
     * | - {module}: Module name from modules/* directory
     * |
     */
    'monorepo' => [
        'packages' => [
            'path' => 'packages/*',
            'namespace' => 'Fulers\{package}',
        ],
        'modules' => [
            'path' => 'modules/*',
            'namespace' => 'Modules\{module}',
        ],
    ],

    /*
     * |--------------------------------------------------------------------------
     * | Discovery Paths
     * |--------------------------------------------------------------------------
     * |
     * | Pre-configured paths for common discovery use cases. These can be
     * | referenced in your code using config('discovery.paths.settings'), etc.
     * |
     * | You can add your own custom path groups here for easy reuse across
     * | your application.
     * |
     */
    'paths' => [
        'settings' => [
            'packages/*/src/Settings',
            'modules/*/src/Settings',
            'app/Settings',
        ],
        'health' => [
            'packages/*/src/Health',
            'modules/*/src/Health',
        ],
        'commands' => [
            'packages/*/src/Console/Commands',
            'modules/*/src/Console/Commands',
        ],
    ],
];
