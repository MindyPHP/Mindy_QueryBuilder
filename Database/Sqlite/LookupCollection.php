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
            case 'regex':
                return $adapter->quoteColumn($column).' REGEXP '.$adapter->quoteValue('/'.$value.'/');

            case 'iregex':
                return $adapter->quoteColumn($column).' REGEXP '.$adapter->quoteValue('/'.$value.'/i');

            case 'second':
                return "strftime('%S', ".$adapter->quoteColumn($column).')='.$adapter->quoteValue((string) $value);

            case 'year':
                return "strftime('%Y', ".$adapter->quoteColumn($column).')='.$adapter->quoteValue((string) $value);

            case 'minute':
                return "strftime('%M', ".$adapter->quoteColumn($column).')='.$adapter->quoteValue((string) $value);

            case 'hour':
                return "strftime('%H', ".$adapter->quoteColumn($column).')='.$adapter->quoteValue((string) $value);

            case 'day':
                return "strftime('%d', ".$adapter->quoteColumn($column).')='.$adapter->quoteValue((string) $value);

            case 'month':
                $value = (int) $value;
                if (1 == strlen((string) $value)) {
                    $value = '0'.(string) $value;
                }

                return "strftime('%m', ".$adapter->quoteColumn($column).')='.$adapter->quoteValue((string) $value);

            case 'week_day':
                $value = (int) $value;
                if ($value < 1 || $value > 7) {
                    throw new \LogicException('Incorrect day of week. Available range 0-6 where 0 - monday.');
                }

                /*
                 * %w - day of week 0-6 with Sunday==0
                 */
                if (7 === $value) {
                    $value = 1;
                } else {
                    $value += 1;
                }

                return "strftime('%w', ".$adapter->quoteColumn($column).')='.$adapter->quoteValue((string) ($value - 1));

            case 'range':
                list($min, $max) = $value;

                return $adapter->quoteColumn($column).' BETWEEN '.(int) $min.' AND '.(int) $max;
        }

        return parent::process($adapter, $lookup, $column, $value);
    }
}
