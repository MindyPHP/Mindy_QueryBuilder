<?php
/**
 * Created by IntelliJ IDEA.
 * User: maxim
 * Date: 26/12/2017
 * Time: 17:00
 */

namespace Mindy\QueryBuilder\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Mindy\QueryBuilder\LookupBuilder\LookupBuilder;
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
