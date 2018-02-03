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
        return 'EXTRACT(SECOND FROM '.$adapter->quoteColumn($x).'::timestamp) = '.$adapter->quoteValue((string) $y);
    }

    protected function lookupYear(AdapterInterface $adapter, string $x, $y): string
    {
        return 'EXTRACT(YEAR FROM '.$adapter->quoteColumn($x).'::timestamp) = '.$adapter->quoteValue((string) $y);
    }

    protected function lookupMinute(AdapterInterface $adapter, string $x, $y): string
    {
        return 'EXTRACT(MINUTE FROM '.$adapter->quoteColumn($x).'::timestamp) = '.$adapter->quoteValue((string) $y);
    }

    protected function lookupHour(AdapterInterface $adapter, string $x, $y): string
    {
        return 'EXTRACT(HOUR FROM '.$adapter->quoteColumn($x).'::timestamp) = '.$adapter->quoteValue((string) $y);
    }

    protected function lookupDay(AdapterInterface $adapter, string $x, $y): string
    {
        return 'EXTRACT(DAY FROM '.$adapter->quoteColumn($x).'::timestamp) = '.$adapter->quoteValue((string) $y);
    }

    protected function lookupMonth(AdapterInterface $adapter, string $x, $y): string
    {
        return 'EXTRACT(MONTH FROM '.$adapter->quoteColumn($x).'::timestamp) = '.$adapter->quoteValue((string) $y);
    }

    protected function lookupWeekDay(AdapterInterface $adapter, string $x, $y): string
    {
        $y = (int) $y;
        if ($y < 1 || $y > 7) {
            throw new \LogicException('Incorrect day of week. Available range 0-6 where 0 - monday.');
        }

        /*
        EXTRACT('dow' FROM timestamp)   0-6    Sunday=0
        TO_CHAR(timestamp, 'D')         1-7    Sunday=1
        */
        if (7 === $y) {
            $y = 1;
        } else {
            $y += 1;
        }

        return 'EXTRACT(DOW FROM '.$adapter->quoteColumn($x).'::timestamp) = '.$adapter->quoteValue((string) ($y - 1));
    }

    protected function lookupRegex(AdapterInterface $adapter, string $x, $y): string
    {
        return $adapter->quoteColumn($x).' ~ '.$adapter->quoteValue($y);
    }

    protected function lookupIregex(AdapterInterface $adapter, string $x, $y): string
    {
        return $adapter->quoteColumn($x).' ~* '.$adapter->quoteValue($y);
    }

    protected function lookupContains(AdapterInterface $adapter, string $x, $y): string
    {
        if (is_bool($y)) {
            $y = (int) $y;
        }

        return $adapter->quoteColumn($x).'::text LIKE '.$adapter->quoteValue('%'.(string) $y.'%');
    }

    protected function lookupIcontains(AdapterInterface $adapter, string $x, $y): string
    {
        if (is_bool($y)) {
            $y = (int) $y;
        }

        return 'LOWER('.$adapter->quoteColumn($x).'::text) LIKE '.$adapter->quoteValue('%'.mb_strtolower((string) $y, 'UTF-8').'%');
    }

    protected function lookupStartswith(AdapterInterface $adapter, string $x, $y): string
    {
        if (is_bool($y)) {
            $y = (int) $y;
        }

        return $adapter->quoteColumn($x).'::text LIKE '.$adapter->quoteValue((string) $y.'%');
    }

    protected function lookupIstartswith(AdapterInterface $adapter, string $x, $y): string
    {
        if (is_bool($y)) {
            $y = (int) $y;
        }

        return 'LOWER('.$adapter->quoteColumn($x).'::text) LIKE '.$adapter->quoteValue(mb_strtolower((string) $y, 'UTF-8').'%');
    }

    protected function lookupEndswith(AdapterInterface $adapter, string $x, $y): string
    {
        if (is_bool($y)) {
            $y = (int) $y;
        }

        return $adapter->quoteColumn($x).'::text LIKE '.$adapter->quoteValue('%'.(string) $y);
    }

    protected function lookupIendswith(AdapterInterface $adapter, string $x, $y): string
    {
        if (is_bool($y)) {
            $y = (int) $y;
        }

        return 'LOWER('.$adapter->quoteColumn($x).'::text) LIKE '.$adapter->quoteValue('%'.mb_strtolower((string) $y, 'UTF-8'));
    }
}
