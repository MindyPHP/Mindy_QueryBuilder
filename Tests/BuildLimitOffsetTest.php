<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Tests;

class BuildLimitOffsetTest extends BaseTest
{
    public $limit = '';

    public function testLimit()
    {
        $qb = $this->getQueryBuilder();
        $qb->limit(10);
        $this->assertSql('SELECT * LIMIT 10', $qb->toSQL());
    }

    public function testLimitOffset()
    {
        $qb = $this->getQueryBuilder();
        $qb->limit(10);
        $qb->offset(10);
        $this->assertSql('SELECT * LIMIT 10 OFFSET 10', $qb->toSQL());
    }

    public function testPaginate()
    {
        $qb = $this->getQueryBuilder();
        $qb->paginate(4, 10);
        $this->assertSql('SELECT * LIMIT 10 OFFSET 30', $qb->toSQL());
    }
}
