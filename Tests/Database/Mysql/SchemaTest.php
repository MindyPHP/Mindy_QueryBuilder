<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Tests\Database\Mysql;

use Mindy\QueryBuilder\Tests\SchemaTest as BaseSchemaTest;

class SchemaTest extends BaseSchemaTest
{
    protected $driver = 'mysql';

    public function testRandomOrder()
    {
        $adapter = $this->getQueryBuilder()->getAdapter();
        $this->assertEquals('RAND()', $adapter->getRandomOrder());
    }
}
