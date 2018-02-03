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
        $this->assertSame(1, $adapter->prepareValue(1));
        $this->assertSame('1', $adapter->prepareValue('1'));
        $this->assertSame(true, $adapter->prepareValue(true));

        $this->assertSame(date('Y-m-d'), $adapter->getDate(time()));
        $this->assertSame(date('Y-m-d'), $adapter->getDate(new \DateTime()));
        $this->assertSame(date('Y-m-d'), $adapter->getDate());

        $this->assertSame('true', $adapter->getBoolean(1));
        $this->assertSame('false', $adapter->getBoolean(0));

        $this->assertSame('FALSE', $adapter->quoteValue(false));
        $this->assertSame('NULL', $adapter->quoteValue(null));

        $this->assertSame('OFFSET 10', $adapter->sqlLimitOffset(null, 10));
        $this->assertSame('LIMIT 10 OFFSET 10', $adapter->sqlLimitOffset(10, 10));

        $this->assertSame('RANDOM()', $adapter->getRandomOrder());
        $this->assertSame('SELECT SETVAL(foo, 100, false)', $adapter->sqlResetSequence('foo', 100));

        $this->assertSame('SET CONSTRAINTS ALL IMMEDIATE', $adapter->sqlCheckIntegrity(true));
        $this->assertSame('SET CONSTRAINTS ALL DEFERRED', $adapter->sqlCheckIntegrity(false));

        $this->assertSame('ALTER TABLE bar.foo ENABLE TRIGGER ALL', $adapter->sqlCheckIntegrity(true, 'foo', 'bar'));
        $this->assertSame('ALTER TABLE bar.foo ENABLE TRIGGER ALL', $adapter->sqlCheckIntegrity(1, 'foo', 'bar'));
        $this->assertSame('ALTER TABLE bar.foo DISABLE TRIGGER ALL', $adapter->sqlCheckIntegrity(false, 'foo', 'bar'));
        $this->assertSame('ALTER TABLE bar.foo DISABLE TRIGGER ALL', $adapter->sqlCheckIntegrity(0, 'foo', 'bar'));
        $this->assertSame('ALTER TABLE bar.foo DISABLE TRIGGER ALL', $adapter->sqlCheckIntegrity('', 'foo', 'bar'));
    }
}
