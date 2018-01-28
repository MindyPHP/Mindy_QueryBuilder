<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Tests\Database\Sqlite;

use Mindy\QueryBuilder\AdapterInterface;
use Mindy\QueryBuilder\Tests\BaseTest;

class AdapterTest extends BaseTest
{
    /**
     * @var string
     */
    protected $driver = 'sqlite';

    public function testAdapter()
    {
        $adapter = $this->getAdapter();
        $this->assertInstanceOf(AdapterInterface::class, $adapter);
        $this->assertSame(1, $adapter->prepareValue(1));
        $this->assertSame('1', $adapter->prepareValue('1'));
        $this->assertSame(1, $adapter->prepareValue(true));

        $this->assertSame(date('Y-m-d'), $adapter->getDate(time()));
        $this->assertSame(date('Y-m-d'), $adapter->getDate(new \DateTime()));
        $this->assertSame(date('Y-m-d'), $adapter->getDate());

        $this->assertSame('LIMIT -1 OFFSET 10', $adapter->sqlLimitOffset(null, 10));
        $this->assertSame('LIMIT 10 OFFSET 10', $adapter->sqlLimitOffset(10, 10));

        $this->assertSame('RANDOM()', $adapter->getRandomOrder());
        $this->assertSame('ALTER TABLE `foo` ADD COLUMN `bar` VARCHAR(255)', $adapter->sqlAddColumn('foo', 'bar', 'VARCHAR(255)'));
        $this->assertSame('UPDATE sqlite_sequence SET seq=100 WHERE name=\'foo\'', $adapter->sqlResetSequence('foo', 100));

        $this->assertSame('DROP INDEX `bar`', $adapter->sqlDropIndex('foo', 'bar'));
        $this->assertSame('ALTER TABLE `foo` RENAME TO `bar`', $adapter->sqlRenameTable('foo', 'bar'));

        $this->assertSame('DELETE FROM `foo`', $adapter->sqlTruncateTable('foo'));

        $this->assertSame('PRAGMA foreign_keys=1', $adapter->sqlCheckIntegrity(true));
        $this->assertSame('PRAGMA foreign_keys=1', $adapter->sqlCheckIntegrity(1));
        $this->assertSame('PRAGMA foreign_keys=0', $adapter->sqlCheckIntegrity(false));
        $this->assertSame('PRAGMA foreign_keys=0', $adapter->sqlCheckIntegrity(0));
        $this->assertSame('PRAGMA foreign_keys=0', $adapter->sqlCheckIntegrity(''));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage not supported by SQLite
     */
    public function testDropForeignKeyException()
    {
        $this->getAdapter()->sqlDropForeignKey('foo', 'bar');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage not supported by SQLite
     */
    public function testDropColumnException()
    {
        $this->getAdapter()->sqlDropColumn('foo', 'bar');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage not supported by SQLite
     */
    public function testAddForeignKeyException()
    {
        $this->getAdapter()->sqlAddForeignKey('foo', 'bar', [], 'tbl', []);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage not supported by SQLite
     */
    public function testAlterColumnException()
    {
        $this->getAdapter()->sqlAlterColumn('foo', 'bar', 'tbl');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage not supported by SQLite
     */
    public function testAddPrimaryKeyException()
    {
        $this->getAdapter()->sqlAddPrimaryKey('foo', 'bar', 'tbl');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage not supported by SQLite
     */
    public function testDropPrimaryKeyException()
    {
        $this->getAdapter()->sqlDropPrimaryKey('foo', 'bar');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage not supported by SQLite
     */
    public function testRenameColumnException()
    {
        $this->getAdapter()->sqlRenameColumn('test_rename', 'foo_unknown', 'bar');
    }
}
