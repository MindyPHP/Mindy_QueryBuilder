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
use Mindy\QueryBuilder\BaseLookupCollection;

class LookupCollection extends BaseLookupCollection
{
    public function has($lookup)
    {
        $lookups = [
            'regex', 'iregex', 'second', 'year', 'minute',
            'hour', 'day', 'month', 'week_day',
        ];
        if (in_array($lookup, $lookups)) {
            return true;
        }

        return parent::has($lookup);
    }

    /**
     * @param AdapterInterface $adapter
     * @param $lookup
     * @param $column
     * @param $value
     *
     * @return string
     */
    public function process(AdapterInterface $adapter, $lookup, $column, $value)
    {
        switch ($lookup) {
            case 'second':
                return 'EXTRACT(SECOND FROM '.$adapter->quoteColumn($column).'::timestamp)='.$adapter->quoteValue((string) $value);

            case 'year':
                return 'EXTRACT(YEAR FROM '.$adapter->quoteColumn($column).'::timestamp)='.$adapter->quoteValue((string) $value);

            case 'minute':
                return 'EXTRACT(MINUTE FROM '.$adapter->quoteColumn($column).'::timestamp)='.$adapter->quoteValue((string) $value);

            case 'hour':
                return 'EXTRACT(HOUR FROM '.$adapter->quoteColumn($column).'::timestamp)='.$adapter->quoteValue((string) $value);

            case 'day':
                return 'EXTRACT(DAY FROM '.$adapter->quoteColumn($column).'::timestamp)='.$adapter->quoteValue((string) $value);

            case 'month':
                return 'EXTRACT(MONTH FROM '.$adapter->quoteColumn($column).'::timestamp)='.$adapter->quoteValue((string) $value);

            case 'week_day':
                $value = (int) $value;
                if ($value < 1 || $value > 7) {
                    throw new \LogicException('Incorrect day of week. Available range 0-6 where 0 - monday.');
                }

                /*
                EXTRACT('dow' FROM timestamp)   0-6    Sunday=0
                TO_CHAR(timestamp, 'D')         1-7    Sunday=1
                */
                if (7 === $value) {
                    $value = 1;
                } else {
                    $value += 1;
                }

                return 'EXTRACT(DOW FROM '.$adapter->quoteColumn($column).'::timestamp)='.$adapter->quoteValue((string) ($value - 1));

            case 'regex':
                return $adapter->quoteColumn($column).'~'.$adapter->quoteValue($value);

            case 'iregex':
                return $adapter->quoteColumn($column).'~*'.$adapter->quoteValue($value);

            case 'contains':
                if (is_bool($value)) {
                    $value = (int) $value;
                }

                return $adapter->quoteColumn($column).'::text LIKE '.$adapter->quoteValue('%'.(string) $value.'%');

            case 'icontains':
                if (is_bool($value)) {
                    $value = (int) $value;
                }

                return 'LOWER('.$adapter->quoteColumn($column).'::text) LIKE '.$adapter->quoteValue('%'.mb_strtolower((string) $value, 'UTF-8').'%');

            case 'startswith':
                if (is_bool($value)) {
                    $value = (int) $value;
                }

                return $adapter->quoteColumn($column).'::text LIKE '.$adapter->quoteValue((string) $value.'%');

            case 'istartswith':
                if (is_bool($value)) {
                    $value = (int) $value;
                }

                return 'LOWER('.$adapter->quoteColumn($column).'::text) LIKE '.$adapter->quoteValue(mb_strtolower((string) $value, 'UTF-8').'%');

            case 'endswith':
                if (is_bool($value)) {
                    $value = (int) $value;
                }

                return $adapter->quoteColumn($column).'::text LIKE '.$adapter->quoteValue('%'.(string) $value);

            case 'iendswith':
                if (is_bool($value)) {
                    $value = (int) $value;
                }

                return 'LOWER('.$adapter->quoteColumn($column).'::text) LIKE '.$adapter->quoteValue('%'.mb_strtolower((string) $value, 'UTF-8'));
        }

        return parent::process($adapter, $lookup, $column, $value);
    }
}
