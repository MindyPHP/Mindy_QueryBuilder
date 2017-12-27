<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Database\Mysql;

use Mindy\QueryBuilder\BaseLookupCollection;
use Mindy\QueryBuilder\Interfaces\IAdapter;

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
     * @param IAdapter $adapter
     * @param $lookup
     * @param $column
     * @param $value
     *
     * @return string
     */
    public function process(IAdapter $adapter, $lookup, $column, $value)
    {
        switch ($lookup) {
            case 'regex':
                if (is_bool($value)) {
                    $value = (int)$value;
                }
                return 'BINARY '.$adapter->quoteColumn($column).' REGEXP '.$adapter->quoteValue((string)$value);

            case 'iregex':
                if (is_bool($value)) {
                    $value = (int)$value;
                }
                return $adapter->quoteColumn($column).' REGEXP '.$adapter->quoteValue((string)$value);

            case 'second':
                return 'EXTRACT(SECOND FROM '.$adapter->quoteColumn($column).')='.$adapter->quoteValue((string) $value);

            case 'year':
                return 'EXTRACT(YEAR FROM '.$adapter->quoteColumn($column).')='.$adapter->quoteValue((string) $value);

            case 'minute':
                return 'EXTRACT(MINUTE FROM '.$adapter->quoteColumn($column).')='.$adapter->quoteValue((string) $value);

            case 'hour':
                return 'EXTRACT(HOUR FROM '.$adapter->quoteColumn($column).')='.$adapter->quoteValue((string) $value);

            case 'day':
                return 'EXTRACT(DAY FROM '.$adapter->quoteColumn($column).')='.$adapter->quoteValue((string) $value);

            case 'month':
                return 'EXTRACT(MONTH FROM '.$adapter->quoteColumn($column).')='.$adapter->quoteValue((string) $value);

            case 'week_day':
                $value = (int) $value;
                if ($value < 1 || $value > 7) {
                    throw new \LogicException('Incorrect day of week. Available range 0-6 where 0 - monday.');
                }

                /*
                DAYOFWEEK(timestamp)            1-7    Sunday=1
                WEEKDAY(timestamp)              0-6    Monday=0
                 */
                if (7 === $value) {
                    $value = 1;
                } else {
                    $value += 1;
                }

                return 'DAYOFWEEK('.$adapter->quoteColumn($column).')='.$adapter->quoteValue((string) $value);
        }

        return parent::process($adapter, $lookup, $column, $value);
    }
}
