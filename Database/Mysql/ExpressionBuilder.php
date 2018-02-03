<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Database\Mysql;

use Mindy\QueryBuilder\AdapterInterface;
use Mindy\QueryBuilder\BaseExpressionBuilder;

class ExpressionBuilder extends BaseExpressionBuilder
{
    protected function lookupIregex(AdapterInterface $adapter, string $x, $y): string
    {
        if (is_bool($y)) {
            $y = (int) $y;
        }

        return $this->comparison(
            $adapter->quoteColumn($x),
            'REGEXP',
            $adapter->quoteValue((string) $y)
        );
    }

    protected function lookupRegex(AdapterInterface $adapter, string $x, $y): string
    {
        if (is_bool($y)) {
            $y = (int) $y;
        }

        $sql = $this->comparison(
            $adapter->quoteColumn($x),
            'REGEXP',
            $adapter->quoteValue((string) $y)
        );

        return 'BINARY '.$sql;
    }

    protected function lookupSecond(AdapterInterface $adapter, string $x, $y): string
    {
        return $this->eq(
            'EXTRACT(SECOND FROM '.$adapter->quoteColumn($x).')',
            $adapter->quoteValue((string) $y)
        );
    }

    protected function lookupYear(AdapterInterface $adapter, string $x, $y): string
    {
        return $this->eq(
            'EXTRACT(YEAR FROM '.$adapter->quoteColumn($x).')',
            $adapter->quoteValue((string) $y)
        );
    }

    protected function lookupMinute(AdapterInterface $adapter, string $x, $y): string
    {
        return $this->eq(
            'EXTRACT(MINUTE FROM '.$adapter->quoteColumn($x).')',
            $adapter->quoteValue((string) $y)
        );
    }

    protected function lookupHour(AdapterInterface $adapter, string $x, $y): string
    {
        return $this->eq(
            'EXTRACT(HOUR FROM '.$adapter->quoteColumn($x).')',
            $adapter->quoteValue((string) $y)
        );
    }

    protected function lookupDay(AdapterInterface $adapter, string $x, $y): string
    {
        return $this->eq(
            'EXTRACT(DAY FROM '.$adapter->quoteColumn($x).')',
            $adapter->quoteValue((string) $y)
        );
    }

    protected function lookupMonth(AdapterInterface $adapter, string $x, $y): string
    {
        return $this->eq(
            'EXTRACT(MONTH FROM '.$adapter->quoteColumn($x).')',
            $adapter->quoteValue((string) $y)
        );
    }

    protected function lookupWeekday(AdapterInterface $adapter, string $x, $y): string
    {
        return $this->eq(
            'DAYOFWEEK('.$adapter->quoteColumn($x).')',
            $adapter->quoteValue((string) WeekDayFormat::format($y))
        );
    }

    protected function lookupJson(AdapterInterface $adapter, string $x, $y): string
    {
        $result = [];
        foreach ($y as $field => $value) {
            list($name, $lookups) = $this->parse($field);
            $first = current($lookups);

            if ($this->has($first)) {
                $method = $this->formatMethod($first);

                $result[] = call_user_func_array([$this, $method], [
                    $adapter,
                    sprintf('JSON_EXTRACT(%s, %s)', $adapter->quoteColumn($x), $adapter->quoteValue('$.'.$name)),
                    $value,
                ]);
            }
        }

        return implode(' AND ', $result);
    }
}
