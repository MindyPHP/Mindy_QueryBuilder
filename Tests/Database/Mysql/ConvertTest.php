<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Tests\Database\Mysql;

use Doctrine\DBAL\Platforms\MySqlPlatform;
use Mindy\QueryBuilder\Tests\BaseTest;

class ConvertTest extends BaseTest
{
    protected $driver = 'mysql';

    public function testBoolean()
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->assertInstanceOf(MySqlPlatform::class, $platform);
        $this->assertSame(
            1,
            $platform->convertBooleansToDatabaseValue(true)
        );
    }
}
