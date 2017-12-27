<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Tests;

class SqliteSchemaTest extends SchemaTest
{
    protected $driver = 'sqlite';

    public function testRandomOrder()
    {
        $adapter = $this->getQueryBuilder()->getAdapter();
        $this->assertEquals('RANDOM()', $adapter->getRandomOrder());
    }

    public function testLimitOffset()
    {
        $sql = $this->getQueryBuilder()->from('profile')->offset(1)->toSQL();
        $this->assertEquals($this->quoteSql('SELECT * FROM [[profile]] LIMIT 9223372036854775807 OFFSET 1'), $sql);
    }
}
