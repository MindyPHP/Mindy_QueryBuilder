<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Mindy\QueryBuilder\QueryBuilder;
use PHPUnit\Framework\TestCase;

abstract class BaseTest extends ConnectionAwareTest
{
    /**
     * @throws \Exception
     *
     * @return \Mindy\QueryBuilder\BaseAdapter
     */
    protected function getAdapter()
    {
        return $this->getQueryBuilder()->getAdapter();
    }

    /**
     * @throws \Exception
     *
     * @return QueryBuilder
     */
    protected function getQueryBuilder()
    {
        return QueryBuilder::getInstance($this->connection);
    }

    /**
     * @param $sql
     *
     * @return string
     */
    protected function quoteSql($sql)
    {
        return $this->getAdapter()->quoteSql($sql);
    }

    protected function assertSql($sql, $actual)
    {
        $this->assertEquals($this->quoteSql($sql), trim($actual));
    }
}
