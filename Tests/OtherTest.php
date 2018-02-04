<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Tests;

use Mindy\QueryBuilder\Utils\TableNameResolver;

class OtherTest extends BaseTest
{
    public function testGroupOrder()
    {
        // Проверка порядка генерирования ORDER BY и GROUP BY
        $qb = $this->getQueryBuilder();
        $qb->select('t.*')->from(['t' => 'comment'])->group(['t.id'])->order(['t.id']);
        $this->assertSame(
            'SELECT t.* FROM comment AS t GROUP BY t.id ORDER BY t.id ASC',
            $qb->toSQL()
        );
    }

    public function testClone()
    {
        $qb = $this->getQueryBuilder();
        $qb->select('a, b, c')->from('test');

        $this->assertEquals('SELECT a, b, c FROM test', $qb->toSQL());
        $copy = clone $qb;
        $this->assertEquals('SELECT a, b, c FROM test', $copy->toSQL());
    }

    public function testRawTableName()
    {
        $this->assertEquals('test', TableNameResolver::getTableName('{{%test}}'));
        $this->assertEquals('test', TableNameResolver::getTableName('test'));
    }

    public function testInsert()
    {
        $qb = $this->getQueryBuilder();
        $this->assertSame(
            "INSERT INTO test (name) VALUES ('qwe')",
            $qb->insert('test', [['name' => 'qwe']])
        );
        $this->assertEquals(
            "INSERT INTO test (name) VALUES ('foo'), ('bar')",
            $qb->insert('test', [['name' => 'foo'], ['name' => 'bar']])
        );
    }

    public function testUpdate()
    {
        $qb = $this->getQueryBuilder();
        $this->assertEquals(
            "UPDATE test SET name='bar' WHERE (name = 'foo')",
            $qb->setTypeUpdate()->update('test', ['name' => 'bar'])->where(['name' => 'foo'])->toSQL()
        );
    }

    public function testDelete()
    {
        $qb = $this->getQueryBuilder();
        $this->assertEquals(
            "DELETE FROM test WHERE (name = 'qwe')",
            $qb->setTypeDelete()->where(['name' => 'qwe'])->from('test')->toSQL()
        );
    }
}
