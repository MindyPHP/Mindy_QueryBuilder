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
use Mindy\QueryBuilder\QueryBuilder;
use Mindy\QueryBuilder\QueryBuilderFactory;

class QueryBuilderFactoryTest extends BaseTest
{
    /**
     * @throws \Mindy\QueryBuilder\Exception\NotSupportedException
     */
    public function testFactory()
    {
        $this->assertInstanceOf(
            QueryBuilder::class,
            QueryBuilderFactory::getQueryBuilder($this->connection)
        );
    }

    /**
     * @expectedException \Mindy\QueryBuilder\Exception\NotSupportedException
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

        QueryBuilderFactory::getQueryBuilder($connection);
    }
}
