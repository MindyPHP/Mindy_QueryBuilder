<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Tests\Database\Postgres;

use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Mindy\QueryBuilder\Tests\BaseTest;

class ConvertTest extends BaseTest
{
    /**
     * @var string
     */
    protected $driver = 'pgsql';

    public function testBoolean()
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->assertInstanceOf(PostgreSqlPlatform::class, $platform);
        $this->assertSame(
            1,
            $platform->convertBooleansToDatabaseValue(true)
        );
    }
}
