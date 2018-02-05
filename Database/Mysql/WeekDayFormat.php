<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Database\Mysql;

class WeekDayFormat
{
    /**
     * DAYOFWEEK(timestamp) 1-7 Sunday=1
     *
     * @param string|int $y
     *
     * @return int
     */
    public static function format($y): int
    {
        $y = (int)$y;
        if ($y < 1 || $y > 7) {
            throw new \LogicException(sprintf(
                'Incorrect day of week: %s. Available range 1-7 where 1 - monday.',
                $y
            ));
        }

        return 7 === $y ? 1 : $y + 1;
    }
}
