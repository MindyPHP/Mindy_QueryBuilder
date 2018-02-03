<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Tests;

use Mindy\QueryBuilder\Database;
use PHPUnit\Framework\TestCase;

class WeekDayFormatTest extends TestCase
{
    public function mysqlProvider()
    {
        return [
            [2, 1], // Понедельник
            [3, 2], // Вторник
            [4, 3], // Среда
            [5, 4], // Четверг
            [6, 5], // Пятница
            [7, 6], // Суббота
            [1, 7], // Воскресенье
        ];
    }

    /**
     * @dataProvider mysqlProvider
     *
     * @param int $expected
     * @param int $dayOfWeek
     */
    public function testMysql(int $expected, int $dayOfWeek)
    {
        $this->assertSame($expected, Database\Mysql\WeekDayFormat::format($dayOfWeek));
    }

    public function sqliteProvider()
    {
        return [
            [1, 1], // Понедельник
            [2, 2], // Вторник
            [3, 3], // Среда
            [4, 4], // Четверг
            [5, 5], // Пятница
            [6, 6], // Суббота
            [0, 7], // Воскресенье
        ];
    }

    /**
     * @dataProvider sqliteProvider
     *
     * @param int $expected
     * @param int $dayOfWeek
     */
    public function testSqlite(int $expected, int $dayOfWeek)
    {
        $this->assertSame($expected, Database\Sqlite\WeekDayFormat::format($dayOfWeek));
    }

    public function pgsqlProvider()
    {
        return [
            [2, 1], // Понедельник
            [3, 2], // Вторник
            [4, 3], // Среда
            [5, 4], // Четверг
            [6, 5], // Пятница
            [7, 6], // Суббота
            [0, 7], // Воскресенье
        ];
    }

    /**
     * @dataProvider pgsqlProvider
     *
     * @param int $expected
     * @param int $dayOfWeek
     */
    public function testPostgres(int $expected, int $dayOfWeek)
    {
        $this->assertSame($expected, Database\Pgsql\WeekDayFormat::format($dayOfWeek));
    }
}
