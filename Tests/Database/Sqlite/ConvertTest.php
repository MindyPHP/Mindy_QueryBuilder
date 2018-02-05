<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Tests\Database\Sqlite;

use Doctrine\DBAL\Platforms\SqlitePlatform;
use Mindy\QueryBuilder\Tests\BaseTest;

class ConvertTest extends BaseTest
{
    protected $driver = 'sqlite';

    public function testBoolean()
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->assertInstanceOf(SqlitePlatform::class, $platform);
        $this->assertSame(
            1,
            $platform->convertBooleansToDatabaseValue(true)
        );
    }
}
