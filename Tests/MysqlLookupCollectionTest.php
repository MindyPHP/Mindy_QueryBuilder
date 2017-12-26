<?php
/**
 * Created by IntelliJ IDEA.
 * User: maxim
 * Date: 26/12/2017
 * Time: 16:02
 */

namespace Mindy\QueryBuilder\Tests;

use Mindy\QueryBuilder\Database\Mysql\LookupCollection;

class MysqlLookupCollectionTest extends BaseTest
{
    protected $driver = 'mysql';

    public function providerLookups()
    {
        return [
            ['icontains', 'name', 'foo', "LOWER(`name`) LIKE '%foo%'"],
            ['icontains', 'name', 1, "LOWER(`name`) LIKE '%1%'"],
            ['icontains', 'name', true, "LOWER(`name`) LIKE '%1%'"],
            ['regex', 'name', 'foo', "BINARY `name` REGEXP 'foo'"],
            ['regex', 'name', 1, "BINARY `name` REGEXP '1'"],
            ['regex', 'name', true, "BINARY `name` REGEXP '1'"],
            ['iregex', 'name', 'foo', "`name` REGEXP 'foo'"],
            ['iregex', 'name', 1, "`name` REGEXP '1'"],
            ['iregex', 'name', true, "`name` REGEXP '1'"],
            ['second', 'name', 1, "EXTRACT(SECOND FROM `name`)='1'"],
            ['minute', 'name', 1, "EXTRACT(MINUTE FROM `name`)='1'"],
            ['hour', 'name', 1, "EXTRACT(HOUR FROM `name`)='1'"],
            ['year', 'name', 1, "EXTRACT(YEAR FROM `name`)='1'"],
            ['month', 'name', 1, "EXTRACT(MONTH FROM `name`)='1'"],
            ['day', 'name', 1, "EXTRACT(DAY FROM `name`)='1'"],
            ['week_day', 'name', 1, "DAYOFWEEK(`name`)='1'"],
        ];
    }

    /**
     * @dataProvider providerLookups
     */
    public function testLookups($lookup, $field, $value, $result)
    {
        $c = new LookupCollection();
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

        $c = new LookupCollection();
        foreach ($lookups as $lookup) {
            $this->assertTrue($c->has($lookup));
        }

        $this->assertFalse($c->has('foobar'));
    }
}
