<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Tests;

use Mindy\QueryBuilder\AbstractBuilder;
use Mindy\QueryBuilder\Connection;

class ConnectionLookupBuilderTest extends ConnectionAwareTest
{
    public function testLookupBuilderTrait()
    {
        $this->assertInstanceOf(Connection::class, $this->connection);
        $this->assertInstanceOf(AbstractBuilder::class, $this->connection->getLookupBuilder());
    }
}
