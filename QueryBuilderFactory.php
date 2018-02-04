<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder;

use Doctrine\DBAL\Connection;
use Mindy\QueryBuilder\Exception\NotSupportedException;

class QueryBuilderFactory
{
    /**
     * @param Connection             $connection
     *
     * @throws NotSupportedException
     *
     * @return QueryBuilder
     */
    public static function getQueryBuilder(Connection $connection)
    {
        switch ($connection->getDriver()->getName()) {
            case 'pdo_mysql':
                $adapter = new Database\Mysql\Adapter($connection);
                break;

            case 'pdo_sqlite':
                $adapter = new Database\sqlite\Adapter($connection);
                break;

            case 'pdo_pgsql':
                $adapter = new Database\Pgsql\Adapter($connection);
                break;

            default:
                throw new NotSupportedException('Unknown driver');
        }

        $lookupBuilder = new LookupBuilder();
        $lookupBuilder->addLookupCollection($adapter->getLookupCollection());

        return new QueryBuilder($connection, $adapter, $lookupBuilder);
    }
}
