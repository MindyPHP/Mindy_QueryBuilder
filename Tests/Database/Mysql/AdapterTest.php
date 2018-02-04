<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Tests\Database\Mysql;

use Doctrine\DBAL\Schema\Table;
use Mindy\QueryBuilder\AdapterInterface;
use Mindy\QueryBuilder\Database\Mysql\Adapter;
use Mindy\QueryBuilder\Tests\BaseTest;

class AdapterTest extends BaseTest
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
        /** @var Adapter $adapter */
        $adapter = $this->getAdapter();
        $this->assertInstanceOf(AdapterInterface::class, $adapter);

        $this->assertSame('LIMIT 18446744073709551615 OFFSET 10', $adapter->sqlLimitOffset(null, 10));
        $this->assertSame('LIMIT 10 OFFSET 10', $adapter->sqlLimitOffset(10, 10));

        $this->assertSame('RAND()', $adapter->getRandomOrder());
        $this->assertSame('ALTER TABLE foo AUTO_INCREMENT=100', $adapter->sqlResetSequence('foo', 100));

        $this->assertSame('SET FOREIGN_KEY_CHECKS = 1', $adapter->sqlCheckIntegrity(true));
        $this->assertSame('SET FOREIGN_KEY_CHECKS = 1', $adapter->sqlCheckIntegrity(1));
        $this->assertSame('SET FOREIGN_KEY_CHECKS = 0', $adapter->sqlCheckIntegrity(false));
        $this->assertSame('SET FOREIGN_KEY_CHECKS = 0', $adapter->sqlCheckIntegrity(0));
    }
}
