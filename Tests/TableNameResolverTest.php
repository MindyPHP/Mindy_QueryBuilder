<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Mindy\QueryBuilder\Tests;

use Mindy\QueryBuilder\Utils\TableNameResolver;
use PHPUnit\Framework\TestCase;

class TableNameResolverTest extends TestCase
{
    public function testTableName()
    {
        $this->assertEquals('test', TableNameResolver::getTableName('{{%test}}'));
        $this->assertEquals('test', TableNameResolver::getTableName('test'));
    }
}
