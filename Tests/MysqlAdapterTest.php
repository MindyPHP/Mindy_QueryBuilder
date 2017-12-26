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

class MysqlAdapterTest extends BaseTest
{
    /**
     * @var string
     */
    protected $driver = 'mysql';

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

        $this->assertSame(' LIMIT 10, 18446744073709551615', $adapter->sqlLimitOffset(null, 10));
        $this->assertSame(' LIMIT 10 OFFSET 10', $adapter->sqlLimitOffset(10, 10));

        $this->assertSame('RAND()', $adapter->getRandomOrder());
        $this->assertSame('ALTER TABLE `foo` ADD `bar` VARCHAR(255)', $adapter->sqlAddColumn('foo', 'bar', 'VARCHAR(255)'));
        $this->assertSame('ALTER TABLE `foo` AUTO_INCREMENT=100', $adapter->sqlResetSequence('foo', 100));

        $this->assertSame('DROP INDEX `bar` ON `foo`', $adapter->sqlDropIndex('foo', 'bar'));
        $this->assertSame('ALTER TABLE `foo` DROP FOREIGN KEY `bar`', $adapter->sqlDropForeignKey('foo', 'bar'));
        $this->assertSame('ALTER TABLE `foo` DROP PRIMARY KEY', $adapter->sqlDropPrimaryKey('foo', 'bar'));
        $this->assertSame('RENAME TABLE `foo` TO `bar`', $adapter->sqlRenameTable('foo', 'bar'));

        $this->assertSame('SET FOREIGN_KEY_CHECKS = 1', $adapter->sqlCheckIntegrity(true));
        $this->assertSame('SET FOREIGN_KEY_CHECKS = 1', $adapter->sqlCheckIntegrity(1));
        $this->assertSame('SET FOREIGN_KEY_CHECKS = 0', $adapter->sqlCheckIntegrity(false));
        $this->assertSame('SET FOREIGN_KEY_CHECKS = 0', $adapter->sqlCheckIntegrity(0));
        $this->assertSame('SET FOREIGN_KEY_CHECKS = 0', $adapter->sqlCheckIntegrity(''));
    }

    public function testRenameColumn()
    {
        $table = new Table('test_rename');
        $table->addColumn('foo', 'string', ['length' => 255]);
        $this->connection->getSchemaManager()->createTable($table);
        $this->assertSame(
            'ALTER TABLE `test_rename` CHANGE `foo` `bar` varchar(255) COLLATE utf8_unicode_ci NOT NULL',
            $this->getAdapter()->sqlRenameColumn('test_rename', 'foo', 'bar')
        );
        $this->connection->getSchemaManager()->dropTable('test_rename');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Unable to find 'foo_unknown' in table 'test_rename'.
     */
    public function testRenameColumnException()
    {
        $table = new Table('test_rename');
        $table->addColumn('foo', 'string', ['length' => 255]);
        $this->connection->getSchemaManager()->createTable($table);
        $this->getAdapter()->sqlRenameColumn('test_rename', 'foo_unknown', 'bar');
        $this->connection->getSchemaManager()->dropTable('test_rename');
    }
}
