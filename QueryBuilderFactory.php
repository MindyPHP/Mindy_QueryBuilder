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

class QueryBuilderFactory
{
    /**
     * @param Connection             $connection
     * @param BaseAdapter            $adapter
     * @param LookupBuilderInterface $lookupBuilder
     *
     * @return QueryBuilder
     */
    public static function getQueryBuilder(Connection $connection, BaseAdapter $adapter, LookupBuilderInterface $lookupBuilder)
    {
        return new QueryBuilder($connection, $adapter, $lookupBuilder);
    }
}
