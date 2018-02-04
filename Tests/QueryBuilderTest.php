<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Tests;

use Mindy\QueryBuilder\Aggregation;
use Mindy\QueryBuilder\Expression;
use Mindy\QueryBuilder\LookupBuilder;
use Mindy\QueryBuilder\QueryBuilder;

class BuildSelectJoinCallback
{
    public function run(QueryBuilder $qb, LookupBuilder $lookupBuilder, array $lookupNodes)
    {
        $column = '';
        $alias = '';
        foreach ($lookupNodes as $i => $nodeName) {
            if ($i + 1 == count($lookupNodes)) {
                $column = $nodeName;
            } else {
                switch ($nodeName) {
                    case 'user':
                        $alias = 'user1';
                        $qb->join('LEFT JOIN', $nodeName, ['user1.id' => 'customer.user_id'], $alias);
                        break;
                }
            }
        }

        if (empty($alias) || empty($column)) {
            return false;
        }

        return [$alias, $column];
    }
}

class QueryBuilderTest extends ConnectionAwareTest
{
    protected function createBuilder()
    {
        return QueryBuilder::getInstance($this->connection);
    }

    /**
     * @throws \Exception
     */
    public function testSelect()
    {
        $builder = $this->createBuilder();

        $sql = $builder
            ->select('')
            ->toSQL();
        $this->assertSame('SELECT *', $sql);

        $sql = $builder
            ->select('id')
            ->toSQL();
        $this->assertSame('SELECT id', $sql);

        $sql = $builder
            ->select('p.id')
            ->toSQL();
        $this->assertSame('SELECT p.id', $sql);

        $sql = $builder
            ->select('id AS p_id')
            ->toSQL();
        $this->assertSame('SELECT id AS p_id', $sql);

        $sql = $builder
            ->select('p.id AS p_id')
            ->toSQL();
        $this->assertSame('SELECT p.id AS p_id', $sql);

        $sql = $builder
            ->select('id, name, price')
            ->toSQL();
        $this->assertSame('SELECT id, name, price', $sql);

        $sql = $builder
            ->select('id, name, price AS old_price')
            ->toSQL();
        $this->assertSame('SELECT id, name, price AS old_price', $sql);

        $sql = $builder
            ->select(['foo', 'bar'])
            ->toSQL();
        $this->assertSame('SELECT foo, bar', $sql);

        $sql = $builder
            ->select([
                'foo' => $this->createBuilder()->select(['foo', 'bar'])->from('test'),
            ])
            ->toSQL();
        $this->assertSame('SELECT (SELECT foo, bar FROM test) AS foo', $sql);

        $sql = $builder
            ->select([
                'foo' => new Expression('n+1'),
            ])
            ->toSQL();
        $this->assertSame('SELECT n+1 AS foo', $sql);

        $sql = $builder
            ->select([
                'id', 'root', 'lft', 'rgt',
                new Expression('rgt-lft-1 AS move'),
            ])
            ->toSQL();
        $this->assertSame('SELECT id, root, lft, rgt, rgt-lft-1 AS move', $sql);
    }

    public function testSubqueryAlias()
    {
        $subquerySql = $this
            ->createBuilder()
            ->select('id')
            ->from('test');

        $sql = $this
            ->createBuilder()
            ->select(['id_list' => $subquerySql])
            ->toSQL();

        $this->assertSame(
            'SELECT (SELECT id FROM test) AS id_list',
            $sql
        );
    }

    public function testSelectAutoJoin()
    {
        $qb = $this->createBuilder();
        $qb
            ->getLookupBuilder()
            ->setJoinCallback(new BuildSelectJoinCallback());

        $sql = $qb
            ->select(['user__username'])
            ->from('customer')
            ->toSQL();

        $this->assertSame(
            'SELECT user1.username FROM customer LEFT JOIN user AS user1 ON user1.id=customer.user_id',
            $sql
        );
    }

    public function testCount()
    {
        $sql = $this
            ->createBuilder()
            ->select(new Aggregation\Count('*', 'test'))
            ->toSQL();
        $this->assertSame('SELECT COUNT(*) AS test', $sql);

        $sql = $this
            ->createBuilder()
            ->select(new Aggregation\Count('*'))
            ->toSQL();
        $this->assertEquals('SELECT COUNT(*)', $sql);
    }

    public function testAvg()
    {
        $sql = $this
            ->createBuilder()
            ->select(new Aggregation\Avg('*'))
            ->toSQL();
        $this->assertEquals('SELECT AVG(*)', $sql);
    }

    public function testSum()
    {
        $sql = $this
            ->createBuilder()
            ->select(new Aggregation\Sum('*'))
            ->toSQL();
        $this->assertEquals('SELECT SUM(*)', $sql);
    }

    public function testMin()
    {
        $sql = $this
            ->createBuilder()
            ->select(new Aggregation\Min('*'))
            ->toSQL();
        $this->assertEquals('SELECT MIN(*)', $sql);
    }

    public function testMax()
    {
        $sql = $this
            ->createBuilder()
            ->select(new Aggregation\Max('*'))
            ->toSQL();
        $this->assertEquals('SELECT MAX(*)', $sql);
    }

    public function testSelectDistinct()
    {
        $sql = $this
            ->createBuilder()
            ->select(null, true)
            ->toSQL();
        $this->assertEquals('SELECT DISTINCT *', $sql);

        $sql = $this
            ->createBuilder()
            ->select('description', true)
            ->from('profile')
            ->toSQL();
        $this->assertSame('SELECT DISTINCT description FROM profile', $sql);
    }

    public function testAlias()
    {
        $qb = $this->createBuilder();
        $sql = $qb
            ->setAlias('test1')
            ->select(['id'])
            ->from('test')
            ->toSQL();
        $this->assertSame('SELECT test1.id FROM test AS test1', $sql);

        $sql = $qb
            ->select(['id'])
            ->from('test')
            ->setAlias('test1')
            ->toSQL();
        $this->assertSame('SELECT test1.id FROM test AS test1', $sql);

        $sql = $qb
            ->select('id')
            ->from('test')
            ->setAlias('test1')
            ->toSQL();
        $this->assertSame('SELECT test1.id FROM test AS test1', $sql);
    }

    /**
     * @throws \Exception
     */
    public function testInsert()
    {
        $sql = $this
            ->createBuilder()
            ->insert('test')
            ->values([
                ['name' => 'foo', 'price' => 100.05],
                ['name' => 'bar', 'price' => 95.05],
            ])
            ->toSQL();
        $this->assertSame("INSERT INTO test (name, price) VALUES ('foo', '100.05'), ('bar', '95.05')", $sql);

        $sql = $this
            ->createBuilder()
            ->insert('test')
            ->values([
                ['foo' => new Expression('1+1')],
            ])
            ->toSQL();
        $this->assertSame('INSERT INTO test (foo) VALUES (1+1)', $sql);

        $sql = $this
            ->createBuilder()
            ->insert('test')
            ->values([
                'foo' => 'bar',
            ])
            ->toSQL();
        $this->assertSame("INSERT INTO test (foo) VALUES ('bar')", $sql);

        $sql = $this
            ->createBuilder()
            ->insert('test')
            ->values([
                'foo' => new Expression('1+1'),
            ])
            ->toSQL();
        $this->assertSame('INSERT INTO test (foo) VALUES (1+1)', $sql);

        $sql = $this
            ->createBuilder()
            ->insert('test')
            ->values([
                ['name' => 'qwe']
            ])
            ->toSQL();
        $this->assertSame("INSERT INTO test (name) VALUES ('qwe')", $sql);

        $sql = $this
            ->createBuilder()
            ->insert('test')
            ->values([
                ['name' => 'foo'],
                ['name' => 'bar']
            ])
            ->toSQL();
        $this->assertSame("INSERT INTO test (name) VALUES ('foo'), ('bar')", $sql);
    }

    /**
     * @throws \Exception
     */
    public function testUpdate()
    {
        $builder = $this->createBuilder();
        $sql = $builder
            ->update('test')
            ->values([
                'name' => 'foo',
                'price' => 100.05,
            ])
            ->toSQL();
        $this->assertSame("UPDATE test SET name='foo', price='100.05'", $sql);

        $sql = $builder
            ->update('test')
            ->values([
                'name' => 'foo',
                'price' => new Expression('price + 100'),
            ])
            ->toSQL();
        $this->assertSame("UPDATE test SET name='foo', price=price + 100", $sql);

        $sql = $this
            ->createBuilder()
            ->where(['id' => 1])
            ->update('test')
            ->values(['name' => 'foo'])
            ->toSQL();
        $this->assertSame("UPDATE test SET name='foo' WHERE (id = 1)", $sql);

        $sql = $this
            ->createBuilder()
            ->where(['id__gte' => 1])
            ->update('test')
            ->values(['name' => 'foo'])
            ->toSQL();
        $this->assertSame("UPDATE test SET name='foo' WHERE (id >= 1)", $sql);

        $sql = $this
            ->createBuilder()
            ->where(['id' => 1])
            ->update('test')
            ->values(['id' => new Expression('id+1')])
            ->toSQL();
        $this->assertSame('UPDATE test SET id=id+1 WHERE (id = 1)', $sql);

        $sql = $this
            ->createBuilder()
            ->where(['id' => 1])
            ->update('test')
            ->values(['name' => null])
            ->toSQL();
        $this->assertSame('UPDATE test SET name=NULL WHERE (id = 1)', $sql);

        $sql = $this
            ->createBuilder()
            ->update('test')
            ->values(['name' => 'bar'])
            ->where(['name' => 'foo'])
            ->toSQL();
        $this->assertEquals("UPDATE test SET name='bar' WHERE (name = 'foo')", $sql);
    }

    /**
     * @throws \Exception
     */
    public function testDelete()
    {
        $builder = $this->createBuilder();
        $sql = $builder
            ->delete('test')
            ->where([
                'name' => 'foo',
                'price' => 100.05,
            ])
            ->toSQL();
        $this->assertSame("DELETE FROM test WHERE ((name = 'foo') AND (price = 100.05))", $sql);

        $sql = $this
            ->createBuilder()
            ->delete('test')
            ->where(['name' => 'qwe'])
            ->toSQL();
        $this->assertEquals("DELETE FROM test WHERE (name = 'qwe')", $sql);
    }

    public function testGroup()
    {
        $sql = $this
            ->createBuilder()
            ->group(['id', 'name'])
            ->toSQL();
        $this->assertSame('SELECT * GROUP BY id, name', $sql);

        $sql = $this
            ->createBuilder()
            ->group(['id'])
            ->group('name')
            ->toSQL();
        $this->assertSame('SELECT * GROUP BY id, name', $sql);

        $sql = $this
            ->createBuilder()
            ->group('id, name')
            ->toSQL();
        $this->assertSame('SELECT * GROUP BY id, name', $sql);
    }

    public function testOrder()
    {
        $sql = $this
            ->createBuilder()
            ->from('test')
            ->order(['id', '-name'])
            ->toSQL();
        $this->assertSame('SELECT * FROM test ORDER BY id ASC, name DESC', $sql);

        $sql = $this
            ->createBuilder()
            ->from('test')
            ->order('id ASC, name DESC')
            ->toSQL();
        $this->assertSame('SELECT * FROM test ORDER BY id ASC, name DESC', $sql);

        $sql = $this
            ->createBuilder()
            ->from('test')
            ->order('id, name')
            ->toSQL();
        $this->assertSame('SELECT * FROM test ORDER BY id, name', $sql);

        // Проверка порядка генерирования ORDER BY и GROUP BY
        $sql = $this
            ->createBuilder()
            ->select('t.*')
            ->from(['t' => 'comment'])
            ->group(['t.id'])
            ->order(['t.id'])
            ->toSQL();
        $this->assertSame('SELECT t.* FROM comment AS t GROUP BY t.id ORDER BY t.id ASC', $sql);
    }

    public function testClone()
    {
        $qb = $this->createBuilder();
        $qb->select('a, b, c')->from('test');

        $this->assertEquals('SELECT a, b, c FROM test', $qb->toSQL());
        $copy = clone $qb;
        $this->assertEquals('SELECT a, b, c FROM test', $copy->toSQL());
    }
}
