<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Tests;

class BuildOrderTest extends BaseTest
{
    public function testOrder()
    {
        $qb = $this->getQueryBuilder();
        $qb->order(['id', '-name']);
        $this->assertSame(' ORDER BY id ASC, name DESC', $qb->buildOrder());
    }

    public function testString()
    {
        $qb = $this->getQueryBuilder();
        $qb->order('id ASC, name DESC');
        $this->assertSame(' ORDER BY id ASC, name DESC', $qb->buildOrder());

        $qb = $this->getQueryBuilder();
        $qb->order('id, name');
        $this->assertSame(' ORDER BY id, name', $qb->buildOrder());
    }

    public function testOrderEmpty()
    {
        $qb = $this->getQueryBuilder();
        $this->assertSame('', $qb->buildOrder());
    }
}
