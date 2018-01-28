<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Tests;

use Doctrine\DBAL\Connection;
use Mindy\QueryBuilder\NewQueryBuilder;
use PHPUnit\Framework\TestCase;

class NewQueryBuilderTest extends TestCase
{
    public function testFrom()
    {
        $connection = $this
            ->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $qb = new NewQueryBuilder($connection);
        $qb->from('foo', 'bar')->from('example');
        $this->assertSame('SELECT  FROM foo bar, example', $qb->getSQL());

        $qb->select('*')->from('foo', 'bar')->from('example');
        $this->assertSame('SELECT * FROM foo bar, example', $qb->getSQL());
    }
}
