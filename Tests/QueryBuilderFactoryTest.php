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
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Mindy\QueryBuilder\LookupBuilder;
use Mindy\QueryBuilder\QueryBuilder;
use Mindy\QueryBuilder\QueryBuilderFactory;

class QueryBuilderFactoryTest extends BaseTest
{
    public function testFactory()
    {
        $qb = QueryBuilderFactory::getQueryBuilder($this->connection, $this->getAdapter(), new LookupBuilder());
        $this->assertInstanceOf(QueryBuilder::class, $qb);
        $this->assertInstanceOf(Connection::class, $qb->getConnection());
        $this->assertInstanceOf(AbstractPlatform::class, $qb->getDatabasePlatform());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Unknown driver
     */
    public function testInstance()
    {
        $driver = $this->getMockBuilder(Driver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $driver->method('getName')->willReturn('foo');

        $connection = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connection->method('getDriver')->willReturn($driver);

        QueryBuilder::getInstance($connection);
    }
}
