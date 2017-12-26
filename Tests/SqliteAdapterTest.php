<?php
/**
 * Created by IntelliJ IDEA.
 * User: maxim
 * Date: 26/12/2017
 * Time: 16:16
 */

namespace Mindy\QueryBuilder\Tests;

use Doctrine\DBAL\Schema\Table;
use Mindy\QueryBuilder\Interfaces\IAdapter;

class SqliteAdapterTest extends BaseTest
{
    /**
     * @var string
     */
    protected $driver = 'sqlite';

    public function tearDown()
    {
        try {
            $this->connection->getSchemaManager()->dropTable('test_rename');
        } catch (\Exception $e) {

        }
    }

    public function testAdapter()
    {
        $adapter = $this->getAdapter();
        $this->assertInstanceOf(IAdapter::class, $adapter);
        $this->assertSame(1, $adapter->prepareValue(1));
        $this->assertSame('1', $adapter->prepareValue('1'));
        $this->assertSame(1, $adapter->prepareValue(true));

        $this->assertSame(date('Y-m-d'), $adapter->getDate(time()));
        $this->assertSame(date('Y-m-d'), $adapter->getDate(new \DateTime()));
        $this->assertSame(date('Y-m-d'), $adapter->getDate());

        $this->assertSame(' LIMIT 9223372036854775807 OFFSET 10', $adapter->sqlLimitOffset(null, 10));
        $this->assertSame(' LIMIT 10 OFFSET 10', $adapter->sqlLimitOffset(10, 10));

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
