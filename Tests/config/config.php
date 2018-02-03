<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [
    'mysql' => [
        'wrapperClass' => 'Mindy\QueryBuilder\Connection',
        'dbname' => 'test',
        'user' => 'root',
        'password' => '',
        'host' => 'localhost',
        'driver' => 'pdo_mysql',
        'fixture' => __DIR__.'/../fixtures/mysql.sql',
    ],
    'pgsql' => [
        'wrapperClass' => 'Mindy\QueryBuilder\Connection',
        'dbname' => 'test',
        'user' => 'test',
        'password' => '',
        'host' => 'localhost',
        'driver' => 'pdo_pgsql',
        'fixture' => __DIR__.'/../fixtures/pgsql.sql',
    ],
    'sqlite' => [
        'wrapperClass' => 'Mindy\QueryBuilder\Connection',
        'memory' => true,
//        'path' => __DIR__ . '/sqlite.db',
        'driver' => 'pdo_sqlite',
        'driverClass' => 'Mindy\QueryBuilder\Database\Sqlite\Driver',
        'fixture' => __DIR__.'/../fixtures/sqlite.sql',
    ],
];
