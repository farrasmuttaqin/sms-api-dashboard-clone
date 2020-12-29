<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => 'api_dashboard',

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'api_dashboard' => [
            'driver' => 'mysql',
            'host' => env('API_DASHBOARD_DB_HOST', '127.0.0.1'),
            'port' => env('API_DASHBOARD_DB_PORT', '3306'),
            'database' => env('API_DASHBOARD_DB_DATABASE', 'forge'),
            'username' => env('API_DASHBOARD_DB_USERNAME', 'forge'),
            'password' => env('API_DASHBOARD_DB_PASSWORD', ''),
            'unix_socket' => env('API_DASHBOARD_DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
            'options' => [
                PDO::ATTR_CASE => PDO::CASE_LOWER,
            ],
        ],

        'mysql_sms_api' => [
            'driver' => 'mysql',
            'host' => env('SMS_API_DB_HOST', '127.0.0.1'),
            'port' => env('SMS_API_DB_PORT', '3306'),
            'database' => env('SMS_API_DB_DATABASE', 'forge'),
            'username' => env('SMS_API_DB_USERNAME', 'forge'),
            'password' => env('SMS_API_DB_PASSWORD', ''),
            'unix_socket' => env('SMS_API_DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
            'options' => [
                PDO::ATTR_CASE => PDO::CASE_LOWER,
            ],
        ],

        'mysql_bill_u_msg' => [
            'driver' => 'mysql',
            'host' => env('BILL_U_MESSAGE_DB_HOST', '127.0.0.1'),
            'port' => env('BILL_U_MESSAGE_DB_PORT', '3306'),
            'database' => env('BILL_U_MESSAGE_DB_DATABASE', 'forge'),
            'username' => env('BILL_U_MESSAGE_DB_USERNAME', 'forge'),
            'password' => env('BILL_U_MESSAGE_DB_PASSWORD', ''),
            'unix_socket' => env('BILL_U_MESSAGE_DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
            'options' => [
                PDO::ATTR_CASE => PDO::CASE_LOWER,
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer set of commands than a typical key-value systems
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => 'predis',

        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 0,
        ],

    ],

];