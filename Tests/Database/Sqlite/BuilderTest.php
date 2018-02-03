<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Tests\Database\Sqlite;

use Mindy\QueryBuilder\Database\Sqlite\Builder;
use Mindy\QueryBuilder\Tests\ConnectionAwareTest;

class BuilderTest extends ConnectionAwareTest
{
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
        return [
            ['name', 1, 'name = \'1\''],
            ['name__exact', 1, 'name = \'1\''],

            ['name__gte', 1, 'name >= \'1\''],
            ['name__gt', '1', 'name > \'1\''],

            ['name__lte', '1', 'name <= \'1\''],
            ['name__lt', '1', 'name < \'1\''],

            ['name__icontains', 'FOO', 'LOWER(name) LIKE \'%foo%\''],
            ['name__contains', '1', 'name LIKE \'%1%\''],

            ['name__istartswith', 'FOO', 'LOWER(name) LIKE \'foo%\''],
            ['name__startswith', '1', 'name LIKE \'1%\''],

            ['name__iendswith', 'FOO', 'LOWER(name) LIKE \'%foo\''],
            ['name__endswith', '1', 'name LIKE \'%1\''],

            ['name__isnull', true, 'name IS NULL'],
            ['name__isnull', false, 'name IS NOT NULL'],

            ['name__weekday', '1', 'strftime(\'%w\', name) = 1'],
            ['name__weekday', '7', 'strftime(\'%w\', name) = 0'],

            ['name__regex', '1', 'name REGEXP /1/'],
            ['name__iregex', '7', 'name REGEXP /7/i'],

            ['name__in', [1, 2, 3], 'name IN (1, 2, 3)'],
            ['name__notin', [1, 2, 3], 'name NOT IN (1, 2, 3)'],

            ['name__day', '1', 'strftime(\'%d\', name) = 1'],
            ['name__second', '1', 'strftime(\'%S\', name) = 1'],
            ['name__month', '1', 'strftime(\'%m\', name) = 01'],
            ['name__minute', '1', 'strftime(\'%M\', name) = 1'],
            ['name__hour', '1', 'strftime(\'%H\', name) = 1'],
            ['name__year', '1', 'strftime(\'%Y\', name) = 1'],
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
