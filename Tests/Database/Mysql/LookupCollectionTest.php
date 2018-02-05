<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Tests\Database\Mysql;

use Mindy\QueryBuilder\Database\Mysql\ExpressionBuilder;
use Mindy\QueryBuilder\Tests\BaseTest;

class LookupCollectionTest extends BaseTest
{
    protected $driver = 'mysql';

    public function providerLookups()
    {
        return [
            ['exact', 'name', new \DateTime(), sprintf("name = '%s'", date('Y-m-d H:i:s'))],

            ['gte', 'from_date', new \DateTime(), sprintf("from_date >= '%s'", date('Y-m-d H:i:s'))],
            ['gte', 'to_datetime', new \DateTime(), sprintf("to_datetime >= '%s'", date('Y-m-d H:i:s'))],

            // Test convert \DateTime to string if column type != date or datetime
            ['gte', 'name', new \DateTime(), sprintf("name >= '%s'", date('Y-m-d H:i:s'))],
            ['lte', 'name', new \DateTime(), sprintf("name <= '%s'", date('Y-m-d H:i:s'))],
            ['lt', 'name', new \DateTime(), sprintf("name < '%s'", date('Y-m-d H:i:s'))],
            ['gt', 'name', new \DateTime(), sprintf("name > '%s'", date('Y-m-d H:i:s'))],

            // Test integer
            ['gte', 'name', 1, 'name >= 1'],
            ['lte', 'name', 1, 'name <= 1'],
            ['lt', 'name', 1, 'name < 1'],
            ['gt', 'name', 1, 'name > 1'],

            ['range', 'name', [1, 2], 'name BETWEEN 1 AND 2'],

            ['isnt', 'name', null, 'name IS NOT NULL'],

            ['in', 'name', [1, 2], 'name IN (1, 2)'],
            ['in', 'name', 1, 'name IN (1)'],

            ['contains', 'name', true, "name LIKE '%1%'"],

            ['icontains', 'name', 'foo', "LOWER(name) LIKE '%foo%'"],
            ['icontains', 'name', 1, "LOWER(name) LIKE '%1%'"],
            ['icontains', 'name', true, "LOWER(name) LIKE '%1%'"],

            ['regex', 'name', 'foo', "BINARY name REGEXP 'foo'"],
            ['regex', 'name', 1, "BINARY name REGEXP '1'"],
            ['regex', 'name', true, "BINARY name REGEXP '1'"],

            ['iregex', 'name', 'foo', "name REGEXP 'foo'"],
            ['iregex', 'name', 1, "name REGEXP '1'"],
            ['iregex', 'name', true, "name REGEXP '1'"],

            ['second', 'name', 1, "EXTRACT(SECOND FROM name) = '1'"],
            ['minute', 'name', 1, "EXTRACT(MINUTE FROM name) = '1'"],
            ['hour', 'name', 1, "EXTRACT(HOUR FROM name) = '1'"],
            ['year', 'name', 1, "EXTRACT(YEAR FROM name) = '1'"],
            ['month', 'name', 1, "EXTRACT(MONTH FROM name) = '1'"],
            ['day', 'name', 1, "EXTRACT(DAY FROM name) = '1'"],
            ['week_day', 'name', 1, "DAYOFWEEK(name) = '2'"],
            ['week_day', 'name', 7, "DAYOFWEEK(name) = '1'"],

            ['json', 'attributes', ['name.foo.bar' => 'bar'], "JSON_EXTRACT(attributes, CONCAT('$.', JSON_QUOTE('name.foo.bar'))) = 'bar'"],
            ['json', 'attributes', ['name' => 'bar'], "JSON_EXTRACT(attributes, CONCAT('$.', JSON_QUOTE('name'))) = 'bar'"],
            ['json', 'attributes', ['name__contains' => 'bar'], "JSON_EXTRACT(attributes, CONCAT('$.', JSON_QUOTE('name'))) LIKE '%bar%'"],
            ['json', 'attributes', ['name__icontains' => 'BAR'], "LOWER(JSON_EXTRACT(attributes, CONCAT('$.', JSON_QUOTE('name')))) LIKE '%bar%'"],
            ['json', 'attributes', ['name__gte' => 1], "JSON_EXTRACT(attributes, CONCAT('$.', JSON_QUOTE('name'))) >= 1"],
            ['json', 'attributes', ['name__gt' => 1], "JSON_EXTRACT(attributes, CONCAT('$.', JSON_QUOTE('name'))) > 1"],
            ['json', 'attributes', ['name__lte' => 1], "JSON_EXTRACT(attributes, CONCAT('$.', JSON_QUOTE('name'))) <= 1"],
            ['json', 'attributes', ['name__lt' => 1], "JSON_EXTRACT(attributes, CONCAT('$.', JSON_QUOTE('name'))) < 1"],
            ['json', 'attributes', ['name__startswith' => 'bar'], "JSON_EXTRACT(attributes, CONCAT('$.', JSON_QUOTE('name'))) LIKE 'bar%'"],
            ['json', 'attributes', ['name__istartswith' => 'bar'], "LOWER(JSON_EXTRACT(attributes, CONCAT('$.', JSON_QUOTE('name')))) LIKE 'bar%'"],
            ['json', 'attributes', ['name__endswith' => 'bar'], "JSON_EXTRACT(attributes, CONCAT('$.', JSON_QUOTE('name'))) LIKE '%bar'"],
            ['json', 'attributes', ['name__iendswith' => 'bar'], "LOWER(JSON_EXTRACT(attributes, CONCAT('$.', JSON_QUOTE('name')))) LIKE '%bar'"],
            ['json', 'attributes', ['name__in' => [1, 2, 3]], "JSON_EXTRACT(attributes, CONCAT('$.', JSON_QUOTE('name'))) IN (1, 2, 3)"],
            ['json', 'attributes', ['name__range' => [1, 2]], "JSON_EXTRACT(attributes, CONCAT('$.', JSON_QUOTE('name'))) BETWEEN 1 AND 2"],
            ['json', 'attributes', ['name__isnull' => false], "JSON_EXTRACT(attributes, CONCAT('$.', JSON_QUOTE('name'))) IS NOT NULL"],
        ];
    }

    /**
     * @dataProvider providerLookups
     */
    public function testLookups($lookup, $field, $value, $result)
    {
        $c = new ExpressionBuilder($this->connection);
        $this->assertSame(
            $result,
            $c->process($this->getAdapter(), $lookup, $field, $value)
        );
    }

    public function testAvailableLookups()
    {
        static $lookups = [
            'regex', 'iregex', 'second', 'year', 'minute',
            'hour', 'day', 'month', 'week_day',
        ];

        $c = new ExpressionBuilder($this->connection);
        foreach ($lookups as $lookup) {
            $this->assertTrue($c->has($lookup));
        }

        $this->assertFalse($c->has('foobar'));
    }
}
