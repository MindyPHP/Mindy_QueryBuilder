<?php

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Tests;

use Mindy\QueryBuilder\Aggregation\Aggregation;
use PHPUnit\Framework\TestCase;

class AggregationTest extends TestCase
{
    public function testAlias()
    {
        $a = new Aggregation('foo');
        $a->setTableAlias('bar');
        $this->assertSame('[[bar]].', $a->toSQL());
    }
}
