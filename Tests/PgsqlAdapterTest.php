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

class PgsqlAdapterTest extends BaseTest
{
    /**
     * @var string
     */
    protected $driver = 'pgsql';

    public function testAdapter()
    {
        $adapter = $this->getAdapter();
        $this->assertInstanceOf(IAdapter::class, $adapter);
        $this->assertSame(1, $adapter->prepareValue(1));
        $this->assertSame('1', $adapter->prepareValue('1'));
        $this->assertSame(true, $adapter->prepareValue(true));

        $this->assertSame(date('Y-m-d'), $adapter->getDate(time()));
        $this->assertSame(date('Y-m-d'), $adapter->getDate(new \DateTime()));
        $this->assertSame(date('Y-m-d'), $adapter->getDate());

        $this->assertSame('TRUE', $adapter->getBoolean(1));
        $this->assertSame('FALSE', $adapter->getBoolean(0));

        $this->assertSame('FALSE', $adapter->quoteValue(false));
        $this->assertSame('NULL', $adapter->quoteValue(null));

        $this->assertSame('ALTER TABLE "foo" ALTER COLUMN "bar" TYPE varchar(100)', $adapter->sqlAlterColumn('foo', 'bar', 'varchar(100)'));

        $this->assertSame(' LIMIT ALL OFFSET 10', $adapter->sqlLimitOffset(null, 10));
        $this->assertSame(' LIMIT 10 OFFSET 10', $adapter->sqlLimitOffset(10, 10));

        $this->assertSame('TRUNCATE TABLE "foo"', $adapter->sqlTruncateTable("foo", false));
        $this->assertSame('TRUNCATE TABLE "foo" CASCADE', $adapter->sqlTruncateTable("foo", true));

        $this->assertSame('DROP TABLE "foo"', $adapter->sqlDropTable("foo", false, false));
        $this->assertSame('DROP TABLE IF EXISTS "foo" CASCADE', $adapter->sqlDropTable("foo", true, true));
        $this->assertSame('DROP TABLE "foo" CASCADE', $adapter->sqlDropTable("foo", false, true));

        $this->assertSame('RANDOM()', $adapter->getRandomOrder());
        $this->assertSame('ALTER TABLE "foo" ADD "bar" VARCHAR(255)', $adapter->sqlAddColumn('foo', 'bar', 'VARCHAR(255)'));
        $this->assertSame('SELECT SETVAL("foo", 100, false)', $adapter->sqlResetSequence('foo', 100));

        $this->assertSame('DROP INDEX "bar"', $adapter->sqlDropIndex('foo', 'bar'));
        $this->assertSame('ALTER TABLE "foo" DROP CONSTRAINT "bar"', $adapter->sqlDropForeignKey('foo', 'bar'));
        $this->assertSame('ALTER TABLE "foo" DROP CONSTRAINT "bar"', $adapter->sqlDropPrimaryKey('foo', 'bar'));
        $this->assertSame('ALTER TABLE "foo" RENAME TO "bar"', $adapter->sqlRenameTable('foo', 'bar'));

        $this->assertSame('SET CONSTRAINTS ALL IMMEDIATE', $adapter->sqlCheckIntegrity(true));
        $this->assertSame('SET CONSTRAINTS ALL DEFERRED', $adapter->sqlCheckIntegrity(false));

        $this->assertSame('ALTER TABLE "bar"."foo" ENABLE TRIGGER ALL', $adapter->sqlCheckIntegrity(true, 'foo', 'bar'));
        $this->assertSame('ALTER TABLE "bar"."foo" ENABLE TRIGGER ALL', $adapter->sqlCheckIntegrity(1, 'foo', 'bar'));
        $this->assertSame('ALTER TABLE "bar"."foo" DISABLE TRIGGER ALL', $adapter->sqlCheckIntegrity(false, 'foo', 'bar'));
        $this->assertSame('ALTER TABLE "bar"."foo" DISABLE TRIGGER ALL', $adapter->sqlCheckIntegrity(0, 'foo', 'bar'));
        $this->assertSame('ALTER TABLE "bar"."foo" DISABLE TRIGGER ALL', $adapter->sqlCheckIntegrity('', 'foo', 'bar'));

    }

    public function testRenameColumn()
    {
        $this->assertSame(
            'ALTER TABLE "test_rename" RENAME COLUMN "foo" TO "bar"',
            $this->getAdapter()->sqlRenameColumn('test_rename', 'foo', 'bar')
        );
    }
}
