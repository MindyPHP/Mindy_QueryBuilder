<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Tests\Database\Postgres;

use Mindy\QueryBuilder\Database\Pgsql\Builder;
use Mindy\QueryBuilder\Tests\ConnectionAwareTest;

class BuilderTest extends ConnectionAwareTest
{
    protected $driver = 'pgsql';

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function setUp()
    {
        parent::setUp();

        $this->builder = new Builder($this->connection);
    }

    public function lookupProvider(): array
    {
        // http://sqlfiddle.com/#!17/5fbf85/14
        /*
        CREATE TABLE tbl (id int, name text, attrs json);

        INSERT INTO tbl
        VALUES
            (1, 'Joe', '{"qty": 1, "price": 200, "name": "Joe"}'),
            (2, 'Mark', '{"qty": 3, "price": 240, "name": "Mark"}')
        ;

        SELECT * FROM tbl WHERE
        CAST(attrs ->> 'qty' AS integer) >= 1 AND CAST(attrs ->> 'price' AS integer) > 100
         */

        return [
            ['name', 1, 'name = \'1\''],
            ['name__exact', 1, 'name = \'1\''],

            ['name__gte', 1, 'CAST(name AS integer) >= \'1\''],
            ['name__gt', '1', 'CAST(name AS integer) > \'1\''],

            ['name__lte', '1', 'CAST(name AS integer) <= \'1\''],
            ['name__lt', '1', 'CAST(name AS integer) < \'1\''],

            ['name__icontains', 'FOO', 'LOWER(name::text) LIKE \'%foo%\''],
            ['name__contains', '1', 'name::text LIKE \'%1%\''],

            ['name__istartswith', 'FOO', 'LOWER(name::text) LIKE \'foo%\''],
            ['name__startswith', '1', 'name::text LIKE \'1%\''],

            ['name__iendswith', 'FOO', 'LOWER(name::text) LIKE \'%foo\''],
            ['name__endswith', '1', 'name::text LIKE \'%1\''],

            ['name__isnull', true, 'name IS NULL'],
            ['name__isnull', false, 'name IS NOT NULL'],

            ['name__weekday', '1', 'EXTRACT(DOW FROM name::timestamp) = 1'],
            ['name__weekday', '7', 'EXTRACT(DOW FROM name::timestamp) = 0'],

            ['name__regex', '1', 'name ~ 1'],
            ['name__iregex', '7', 'name ~* 7'],

            ['name__in', [1, 2, 3], 'name IN (1, 2, 3)'],
            ['name__notin', [1, 2, 3], 'name NOT IN (1, 2, 3)'],

            ['name__day', '1', 'EXTRACT(DAY FROM name::timestamp) = 1'],
            ['name__second', '1', 'EXTRACT(SECOND FROM name::timestamp) = 1'],
            ['name__month', '1', 'EXTRACT(MONTH FROM name::timestamp) = 1'],
            ['name__minute', '1', 'EXTRACT(MINUTE FROM name::timestamp) = 1'],
            ['name__hour', '1', 'EXTRACT(HOUR FROM name::timestamp) = 1'],
            ['name__year', '1', 'EXTRACT(YEAR FROM name::timestamp) = 1'],

            ['attrs__json', [], ''],
            ['attrs__json', ['qty__gte' => 1, 'price__gt' => 100], 'CAST(attrs ->> \'qty\' AS integer) >= \'1\' AND CAST(attrs ->> \'price\' AS integer) > \'100\''],
            ['attrs__json', ['name' => 'Joe'], 'attrs ->> \'name\' = \'Joe\''],
        ];
    }

    /**
     * @dataProvider lookupProvider
     *
     * @param string $lookup
     * @param $parameters
     * @param string $expected
     */
    public function testBuilder(string $lookup, $parameters, string $expected)
    {
        $this->assertSame($expected, $this->builder->build($lookup, $parameters));
    }
}
