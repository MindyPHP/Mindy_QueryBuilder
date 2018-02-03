<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Tests;

abstract class SchemaTest extends BaseTest
{
    abstract public function testLimitOffset();

    abstract public function testRandomOrder();

    public function testDistinct()
    {
        $qb = $this->getQueryBuilder();
        $this->assertSame('SELECT * FROM profile', $qb->from('profile')->toSQL());
        $this->assertSame('SELECT DISTINCT description FROM profile', $qb->select('description', true)->from('profile')->toSQL());
    }

    public function testGetDateTime()
    {
        $a = $this->getQueryBuilder()->getAdapter();
        $timestamp = strtotime('2016-07-22 13:54:09');
        $this->assertEquals('2016-07-22', $a->getDate($timestamp));
        $this->assertEquals('2016-07-22 13:54:09', $a->getDateTime($timestamp));

        $this->assertEquals('2016-07-22', $a->getDate((string) $timestamp));
        $this->assertEquals('2016-07-22 13:54:09', $a->getDateTime((string) $timestamp));

        $this->assertEquals('2016-07-22', $a->getDate('2016-07-22 13:54:09'));
        $this->assertEquals('2016-07-22 13:54:09', $a->getDateTime('2016-07-22 13:54:09'));
    }
}
