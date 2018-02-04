<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Tests\Database\Postgres;

use Doctrine\DBAL\Schema\Table;
use Mindy\QueryBuilder\AdapterInterface;
use Mindy\QueryBuilder\Tests\BaseTest;

class AdapterTest extends BaseTest
{
    /**
     * @var string
     */
    protected $driver = 'pgsql';

    public function testAdapter()
    {
        $adapter = $this->getAdapter();
        $this->assertInstanceOf(AdapterInterface::class, $adapter);

        $this->assertSame('true', $adapter->getBoolean(1));
        $this->assertSame('false', $adapter->getBoolean(0));

        $this->assertSame('RANDOM()', $adapter->getRandomOrder());

        $this->assertSame('SET CONSTRAINTS ALL IMMEDIATE', $adapter->sqlCheckIntegrity(true));
        $this->assertSame('SET CONSTRAINTS ALL DEFERRED', $adapter->sqlCheckIntegrity(false));

        $this->assertSame('ALTER TABLE bar.foo ENABLE TRIGGER ALL', $adapter->sqlCheckIntegrity(true, 'foo', 'bar'));
        $this->assertSame('ALTER TABLE bar.foo ENABLE TRIGGER ALL', $adapter->sqlCheckIntegrity(1, 'foo', 'bar'));
        $this->assertSame('ALTER TABLE bar.foo DISABLE TRIGGER ALL', $adapter->sqlCheckIntegrity(false, 'foo', 'bar'));
        $this->assertSame('ALTER TABLE bar.foo DISABLE TRIGGER ALL', $adapter->sqlCheckIntegrity(0, 'foo', 'bar'));
        $this->assertSame('ALTER TABLE bar.foo DISABLE TRIGGER ALL', $adapter->sqlCheckIntegrity('', 'foo', 'bar'));
    }
}
