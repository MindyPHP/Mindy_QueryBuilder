<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Tests\Database\Mysql;

use Mindy\QueryBuilder\Database\Mysql\Adapter;
use Mindy\QueryBuilder\Tests\BaseTest;
use PDO;
use PHPUnit\Framework\TestCase;

class QuoteTest extends BaseTest
{
    protected function setUp()
    {
        parent::setUp();

        if (!extension_loaded('pdo') || !extension_loaded('pdo_mysql')) {
            $this->markTestSkipped('pdo and pdo_mysql extension are required.');
        }
    }

    protected function getAdapter()
    {
        return new Adapter($this->connection);
    }

    public function testQuoteValue()
    {
        $adapter = $this->getAdapter();
        $this->assertEquals(123, $adapter->quoteValue(123));
        $this->assertEquals("'string'", $adapter->quoteValue('string'));
        $this->assertEquals("'It\\''s interesting'", $adapter->quoteValue("It\'s interesting"));
    }

    public function testQuoteTableName()
    {
        $adapter = $this->getAdapter();
        $this->assertEquals('`table`', $adapter->quoteTableName('table'));
        $this->assertEquals('`table`', $adapter->quoteTableName('`table`'));
        $this->assertEquals('`schema`.`table`', $adapter->quoteTableName('schema.table'));
        $this->assertEquals('`schema`.`table`', $adapter->quoteTableName('schema.`table`'));
        $this->assertEquals('{{table}}', $adapter->quoteTableName('{{table}}'));
        $this->assertEquals('(table)', $adapter->quoteTableName('(table)'));
    }

    public function testQuoteColumnName()
    {
        $adapter = $this->getAdapter();
        $this->assertEquals('`column`', $adapter->quoteColumn('column'));
        $this->assertEquals('`column`', $adapter->quoteColumn('`column`'));
        $this->assertEquals('`table`.`column`', $adapter->quoteColumn('table.column'));
        $this->assertEquals('`table`.`column`', $adapter->quoteColumn('table.`column`'));
        $this->assertEquals('[[column]]', $adapter->quoteColumn('[[column]]'));
        $this->assertEquals('{{column}}', $adapter->quoteColumn('{{column}}'));
        $this->assertEquals('(column)', $adapter->quoteColumn('(column)'));
    }
}
