<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
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
