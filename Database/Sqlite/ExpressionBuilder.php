<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Database\Sqlite;

use Mindy\QueryBuilder\AdapterInterface;
use Mindy\QueryBuilder\BaseExpressionBuilder;

class ExpressionBuilder extends BaseExpressionBuilder
{
    protected function lookupRegex(AdapterInterface $adapter, string $x, $y): string
    {
        return $adapter->quoteColumn($x).' REGEXP '.$adapter->quoteValue('/'.$y.'/');
    }

    protected function lookupIregex(AdapterInterface $adapter, string $x, $y): string
    {
        return $adapter->quoteColumn($x).' REGEXP '.$adapter->quoteValue('/'.$y.'/i');
    }

    protected function lookupSecond(AdapterInterface $adapter, string $x, $y): string
    {
        return "strftime('%S', ".$adapter->quoteColumn($x).') = '.$adapter->quoteValue((string) $y);
    }

    protected function lookupYear(AdapterInterface $adapter, string $x, $y): string
    {
        return "strftime('%Y', ".$adapter->quoteColumn($x).') = '.$adapter->quoteValue((string) $y);
    }

    protected function lookupMinute(AdapterInterface $adapter, string $x, $y): string
    {
        return "strftime('%M', ".$adapter->quoteColumn($x).') = '.$adapter->quoteValue((string) $y);
    }

    protected function lookupHour(AdapterInterface $adapter, string $x, $y): string
    {
        return "strftime('%H', ".$adapter->quoteColumn($x).') = '.$adapter->quoteValue((string) $y);
    }

    protected function lookupDay(AdapterInterface $adapter, string $x, $y): string
    {
        return "strftime('%d', ".$adapter->quoteColumn($x).') = '.$adapter->quoteValue((string) $y);
    }

    protected function lookupMonth(AdapterInterface $adapter, string $x, $y): string
    {
        $y = (int) $y;
        if (1 == strlen((string) $y)) {
            $y = '0'.(string) $y;
        }

        return "strftime('%m', ".$adapter->quoteColumn($x).') = '.$adapter->quoteValue((string) $y);
    }

    protected function lookupWeekDay(AdapterInterface $adapter, string $x, $y): string
    {
        $y = WeekDayFormat::format($y);

        return "strftime('%w', ".$adapter->quoteColumn($x).') = '.$adapter->quoteValue((string) $y);
    }

    protected function lookupRange(AdapterInterface $adapter, string $x, $y): string
    {
        list($min, $max) = $y;

        return $adapter->quoteColumn($x).' BETWEEN '.(int) $min.' AND '.(int) $max;
    }
}
