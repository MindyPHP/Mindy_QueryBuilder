<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Tests;

class BuildGroupTest extends BaseTest
{
    public function testSimple()
    {
        $qb = $this->getQueryBuilder();
        $qb->group(['id', 'name']);
        $this->assertSame(' GROUP BY id, name', $qb->buildGroup());
    }

    public function testaddGroupBy()
    {
        $qb = $this->getQueryBuilder();
        $qb->group(['id']);
        $qb->addGroupBy('name');
        $this->assertSame(' GROUP BY id, name', $qb->buildGroup());
    }

    public function testString()
    {
        $qb = $this->getQueryBuilder();
        $qb->group('id, name');
        $this->assertSame(' GROUP BY id, name', $qb->buildGroup());
    }

    public function testOrderEmpty()
    {
        $qb = $this->getQueryBuilder();
        $this->assertSame('', $qb->buildOrder());
    }
}
