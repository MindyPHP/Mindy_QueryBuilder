<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Tests;

use Mindy\QueryBuilder\Database\Sqlite\ExpressionBuilder;
use Mindy\QueryBuilder\LookupBuilder;

class LookupBuilderTest extends BaseTest
{
    protected $driver = 'mysql';

    /**
     * @return array
     */
    public function lookupProvider()
    {
        return [
            [['id' => 1], 'id = 1'],
            [['id__exact' => 1], 'id = 1'],
            [['id__gte' => 1], 'id >= 1'],
            [['id__lte' => 1], 'id <= 1'],
            [['id__gt' => 1], 'id > 1'],
            [['id__lt' => 1], 'id < 1'],
            [['id__isnt' => 1], 'id <> 1'],
            [['id__range' => [1, 2]], 'id BETWEEN 1 AND 2'],
            [['id__isnull' => true], 'id IS NULL'],
            [['id__isnull' => false], 'id IS NOT NULL'],
            [['id__contains' => 'FOO'], "id LIKE '%FOO%'"],
            [['id__icontains' => 'FOO'], "LOWER(id) LIKE '%foo%'"],
            [['id__startswith' => 'FOO'], "id LIKE 'FOO%'"],
            [['id__istartswith' => 'FOO'], 'LOWER(id) LIKE \'foo%\''],
            [['id__endswith' => 'FOO'], "id LIKE '%FOO'"],
            [['id__iendswith' => 'FOO'], 'LOWER(id) LIKE \'%foo\''],
            [['id__in' => [1, 2, 'test']], 'id IN (1, 2, \'test\')'],
            [['id__in' => 'SELECT id FROM test'], 'id IN (SELECT id FROM test)'],
        ];
    }

    /**
     * @dataProvider lookupProvider
     */
    public function testLookups($where, $whereSql)
    {
        $builder = new LookupBuilder();
        $builder->addLookupCollection(new ExpressionBuilder($this->connection));
        $qb = $this->getQueryBuilder();
        $adapter = $qb->getAdapter();

        list($lookup, $column, $value) = current($builder->parse($qb, $where));
        $this->assertSame($whereSql, $builder->runLookup($adapter, $lookup, $column, $value));
    }
}
