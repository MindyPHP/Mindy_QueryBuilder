<?php
/**
 * Created by IntelliJ IDEA.
 * User: maxim
 * Date: 26/12/2017
 * Time: 17:00
 */

namespace Mindy\QueryBuilder\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Mindy\QueryBuilder\LookupBuilder\LookupBuilder;
use Mindy\QueryBuilder\QueryBuilder;
use Mindy\QueryBuilder\QueryBuilderFactory;

class QueryBuilderFactoryTest extends BaseTest
{
    public function testFactory()
    {
        $factory = new QueryBuilderFactory($this->connection, $this->getAdapter(), new LookupBuilder());
        $this->assertInstanceOf(QueryBuilder::class, $factory->getQueryBuilder());
        $this->assertInstanceOf(Connection::class, $factory->getQueryBuilder()->getConnection());
        $this->assertInstanceOf(AbstractPlatform::class, $factory->getQueryBuilder()->getDatabasePlatform());
    }
}
