<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [
    'mysql' => [
        'dbname' => 'test',
        'user' => 'root',
        'password' => '',
        'host' => 'localhost',
        'driver' => 'pdo_mysql',
        'fixture' => __DIR__.'/../fixtures/mysql.sql',
    ],
    'pgsql' => [
        'dbname' => 'test',
        'user' => 'test',
        'password' => '',
        'host' => 'localhost',
        'driver' => 'pdo_pgsql',
        'fixture' => __DIR__.'/../fixtures/pgsql.sql',
    ],
    'sqlite' => [
        'memory' => true,
//        'path' => __DIR__ . '/sqlite.db',
        'driver' => 'pdo_sqlite',
        'driverClass' => 'Mindy\QueryBuilder\Driver\SqliteDriver',
        'fixture' => __DIR__.'/../fixtures/sqlite.sql',
    ],
];
