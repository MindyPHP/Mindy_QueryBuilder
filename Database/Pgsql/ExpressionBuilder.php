<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Database\Pgsql;

use Mindy\QueryBuilder\AdapterInterface;
use Mindy\QueryBuilder\BaseExpressionBuilder;

class ExpressionBuilder extends BaseExpressionBuilder
{
    protected function lookupSecond(AdapterInterface $adapter, string $x, $y): string
    {
        return $this->eq(
            'EXTRACT(SECOND FROM '.$adapter->quoteColumn($x).'::timestamp)',
            $adapter->quoteValue((string) $y)
        );
    }

    protected function lookupYear(AdapterInterface $adapter, string $x, $y): string
    {
        return $this->eq(
            'EXTRACT(YEAR FROM '.$adapter->quoteColumn($x).'::timestamp)',
            $adapter->quoteValue((string) $y)
        );
    }

    protected function lookupMinute(AdapterInterface $adapter, string $x, $y): string
    {
        return $this->eq(
            'EXTRACT(MINUTE FROM '.$adapter->quoteColumn($x).'::timestamp)',
            $adapter->quoteValue((string) $y)
        );
    }

    protected function lookupHour(AdapterInterface $adapter, string $x, $y): string
    {
        return $this->eq(
            'EXTRACT(HOUR FROM '.$adapter->quoteColumn($x).'::timestamp)',
            $adapter->quoteValue((string) $y)
        );
    }

    protected function lookupDay(AdapterInterface $adapter, string $x, $y): string
    {
        return $this->eq(
            'EXTRACT(DAY FROM '.$adapter->quoteColumn($x).'::timestamp)',
            $adapter->quoteValue((string) $y)
        );
    }

    protected function lookupMonth(AdapterInterface $adapter, string $x, $y): string
    {
        return $this->eq(
            'EXTRACT(MONTH FROM '.$adapter->quoteColumn($x).'::timestamp)',
            $adapter->quoteValue((string) $y)
        );
    }

    protected function lookupWeekDay(AdapterInterface $adapter, string $x, $y): string
    {
        return $this->eq(
            'EXTRACT(DOW FROM '.$adapter->quoteColumn($x).'::timestamp)',
            $adapter->quoteValue((string) WeekDayFormat::format($y))
        );
    }

    protected function lookupRegex(AdapterInterface $adapter, string $x, $y): string
    {
        return $this->comparison($adapter->quoteColumn($x), '~', $adapter->quoteValue($y));
    }

    protected function lookupIregex(AdapterInterface $adapter, string $x, $y): string
    {
        return $this->comparison($adapter->quoteColumn($x), '~*', $adapter->quoteValue($y));
    }

    protected function lookupContains(AdapterInterface $adapter, string $x, $y): string
    {
        if (is_bool($y)) {
            $y = (int) $y;
        }

        return $this->like(
            $adapter->quoteColumn($x).'::text',
            $adapter->quoteValue('%'.(string) $y.'%')
        );
    }

    protected function lookupIcontains(AdapterInterface $adapter, string $x, $y): string
    {
        if (is_bool($y)) {
            $y = (int) $y;
        }

        return $this->like(
            'LOWER('.$adapter->quoteColumn($x).'::text)',
            $adapter->quoteValue('%'.mb_strtolower((string) $y, 'UTF-8').'%')
        );
    }

    protected function lookupStartswith(AdapterInterface $adapter, string $x, $y): string
    {
        if (is_bool($y)) {
            $y = (int) $y;
        }

        return $this->like(
            $adapter->quoteColumn($x).'::text',
            $adapter->quoteValue((string) $y.'%')
        );
    }

    protected function lookupIstartswith(AdapterInterface $adapter, string $x, $y): string
    {
        if (is_bool($y)) {
            $y = (int) $y;
        }

        return $this->like(
            'LOWER('.$adapter->quoteColumn($x).'::text)',
            $adapter->quoteValue(mb_strtolower((string) $y, 'UTF-8').'%')
        );
    }

    protected function lookupEndswith(AdapterInterface $adapter, string $x, $y): string
    {
        if (is_bool($y)) {
            $y = (int) $y;
        }

        return $this->like(
            $adapter->quoteColumn($x).'::text',
            $adapter->quoteValue('%'.(string) $y)
        );
    }

    protected function lookupIendswith(AdapterInterface $adapter, string $x, $y): string
    {
        if (is_bool($y)) {
            $y = (int) $y;
        }

        return $this->like(
            'LOWER('.$adapter->quoteColumn($x).'::text)',
            $adapter->quoteValue('%'.mb_strtolower((string) $y, 'UTF-8'))
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

                $castColumn = sprintf("%s ->> '%s'", $adapter->quoteColumn($x), $name);
                switch ($first) {
                    case 'exact':
                        if (is_numeric($value)) {
                            $castColumn = sprintf('(%s)::int', $castColumn);
                        } else {
                            $castColumn = sprintf('(%s)::text', $castColumn);
                        }
                        break;
                    case 'gte':
                    case 'gt':
                    case 'lte':
                    case 'lt':
                        $castColumn = sprintf('(%s)::int', $castColumn);
                        break;
                }

                $result[] = call_user_func_array([$this, $method], [
                    $adapter,
                    $castColumn,
                    $value,
                ]);
            }
        }

        return implode(' AND ', $result);
    }
}
