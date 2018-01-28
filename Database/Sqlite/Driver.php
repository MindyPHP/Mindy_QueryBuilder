<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Database\Sqlite;

use Doctrine\DBAL\Driver\PDOSqlite\Driver as BaseSqliteDriver;

class Driver extends BaseSqliteDriver
{
    /**
     * {@inheritdoc}
     */
    public function connect(array $params, $username = null, $password = null, array $driverOptions = [])
    {
        $connect = parent::connect($params, $username, $password, $driverOptions);
        $connect->sqliteCreateFunction('REGEXP', 'preg_match', 2);

        return $connect;
    }
}
