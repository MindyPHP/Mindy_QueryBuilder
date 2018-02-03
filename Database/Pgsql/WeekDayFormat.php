<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Database\Pgsql;

class WeekDayFormat
{
    /**
     * EXTRACT('dow' FROM timestamp)   0-6    Sunday=0
     * TO_CHAR(timestamp, 'D')         1-7    Sunday=1
     *
     * @param int $y
     *
     * @return int
     */
    public static function format(int $y): int
    {
        if ($y < 1 || $y > 7) {
            throw new \LogicException(sprintf(
                'Incorrect day of week: %s. Available range 1-7 where 1 - monday.',
                $y
            ));
        }

        return $y === 7 ? 0 : $y;
    }
}
