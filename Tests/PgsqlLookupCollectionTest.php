<?php
/**
 * Created by IntelliJ IDEA.
 * User: maxim
 * Date: 26/12/2017
 * Time: 16:02
 */

namespace Mindy\QueryBuilder\Tests;

use Mindy\QueryBuilder\Database\Pgsql\LookupCollection;

class PgsqlLookupCollectionTest extends BaseTest
{
    protected $driver = 'pgsql';

    public function providerLookups()
    {
        return [
            ['exact', 'name', 'foo', "\"name\"='foo'"],
            ['contains', 'name', 'foo', "\"name\"::text LIKE '%foo%'"],
            ['contains', 'name', true, "\"name\"::text LIKE '%1%'"],
            ['startswith', 'name', 'foo', "\"name\"::text LIKE 'foo%'"],
            ['startswith', 'name', true, "\"name\"::text LIKE '1%'"],
            ['istartswith', 'name', 'foo', "LOWER(\"name\"::text) LIKE 'foo%'"],
            ['istartswith', 'name', true, "LOWER(\"name\"::text) LIKE '1%'"],
            ['endswith', 'name', 'foo', "\"name\"::text LIKE '%foo'"],
            ['endswith', 'name', true, "\"name\"::text LIKE '%1'"],
            ['iendswith', 'name', 'foo', "LOWER(\"name\"::text) LIKE '%foo'"],
            ['iendswith', 'name', true, "LOWER(\"name\"::text) LIKE '%1'"],
            ['icontains', 'name', 'foo', "LOWER(\"name\"::text) LIKE '%foo%'"],
            ['icontains', 'name', 1, "LOWER(\"name\"::text) LIKE '%1%'"],
            ['icontains', 'name', true, "LOWER(\"name\"::text) LIKE '%1%'"],
            ['regex', 'name', 'foo', "\"name\"~'foo'"],
            ['regex', 'name', 1, "\"name\"~1"],
            ['regex', 'name', true, "\"name\"~TRUE"],
            ['iregex', 'name', 'foo', "\"name\"~*'foo'"],
            ['iregex', 'name', 1, "\"name\"~*1"],
            ['iregex', 'name', true, "\"name\"~*TRUE"],
            ['second', 'name', 1, "EXTRACT(SECOND FROM \"name\"::timestamp)='1'"],
            ['minute', 'name', 1, "EXTRACT(MINUTE FROM \"name\"::timestamp)='1'"],
            ['hour', 'name', 1, "EXTRACT(HOUR FROM \"name\"::timestamp)='1'"],
            ['year', 'name', 1, "EXTRACT(YEAR FROM \"name\"::timestamp)='1'"],
            ['month', 'name', 1, "EXTRACT(MONTH FROM \"name\"::timestamp)='1'"],
            ['day', 'name', 1, "EXTRACT(DAY FROM \"name\"::timestamp)='1'"],
            ['week_day', 'name', 1, "EXTRACT(DOW FROM \"name\"::timestamp)='1'"],
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
            'regex', 'iregex', 'second', 'year', 'minute', 'hour', 'day', 'month', 'week_day',
        ];

        $c = new LookupCollection();
        foreach ($lookups as $lookup) {
            $this->assertTrue($c->has($lookup));
        }

        $this->assertFalse($c->has('foobar'));
    }
}
