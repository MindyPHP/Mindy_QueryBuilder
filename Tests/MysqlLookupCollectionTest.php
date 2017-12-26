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
            ['exact', 'name', new \DateTime(), sprintf("`name`='%s'", date('Y-m-d H:i:s'))],
            ['gte', 'name', new \DateTime(), sprintf("`name`>='%s'", date('Y-m-d H:i:s'))],
            ['lte', 'name', new \DateTime(), sprintf("`name`<='%s'", date('Y-m-d H:i:s'))],
            ['lt', 'name', new \DateTime(), sprintf("`name`<'%s'", date('Y-m-d H:i:s'))],
            ['gt', 'name', new \DateTime(), sprintf("`name`>'%s'", date('Y-m-d H:i:s'))],
            ['range', 'name', [1, 2], "`name` BETWEEN 1 AND 2"],
            ['isnt', 'name', null, "`name` IS NOT NULL"],
            ['in', 'name', [1, 2], "`name` IN (1, 2)"],
            ['in', 'name', 1, "`name` IN (1)"],
            ['unknown', 'name', 1, null],
            ['contains', 'name', true, "`name` LIKE '%1%'"],
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
