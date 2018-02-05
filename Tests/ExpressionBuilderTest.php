<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Tests;

use Mindy\QueryBuilder\ExpressionBuilder;

class ExpressionBuilderTest extends ConnectionAwareTest
{
    public function testFormatDate()
    {
        $expr = new ExpressionBuilder($this->connection);
        $this->assertInstanceOf(\DateTime::class, $expr->formatDateTime(null));
        $this->assertInstanceOf(\DateTime::class, $expr->formatDateTime(time()));
        $this->assertInstanceOf(\DateTime::class, $expr->formatDateTime(date('d-m-Y')));
        $this->assertInstanceOf(\DateTime::class, $expr->formatDateTime(new \DateTime()));
    }
}
