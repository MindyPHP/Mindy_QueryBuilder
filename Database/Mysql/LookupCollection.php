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
use Mindy\QueryBuilder\BaseLookupCollection;
use Mindy\QueryBuilder\QueryBuilder;

class LookupCollection extends BaseLookupCollection
{
    public function has($lookup)
    {
        $lookups = [
            'regex', 'iregex', 'second', 'year', 'minute',
            'hour', 'day', 'month', 'week_day', 'json',
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
                if (is_bool($value)) {
                    $value = (int) $value;
                }

                return 'BINARY '.$adapter->quoteColumn($column).' REGEXP '.$adapter->quoteValue((string) $value);

            case 'iregex':
                if (is_bool($value)) {
                    $value = (int) $value;
                }

                return $adapter->quoteColumn($column).' REGEXP '.$adapter->quoteValue((string) $value);

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

            case 'json':
                $result = [];
                foreach ($value as $key => $v) {
                    $data = explode('__', $key);
                    if (1 === count($data)) {
                        $attr = $key;
                        $operator = 'exact';
                    } else {
                        list($attr, $operator) = $data;
                    }

                    switch ($operator) {
                        case 'gt':
                            $result[] = 'JSON_EXTRACT('.$adapter->quoteColumn($column).', '.$adapter->quoteValue('$.'.$attr).') > '.$adapter->quoteValue($v);
                            break;

                        case 'gte':
                            $result[] = 'JSON_EXTRACT('.$adapter->quoteColumn($column).', '.$adapter->quoteValue('$.'.$attr).') >= '.$adapter->quoteValue($v);
                            break;

                        case 'lt':
                            $result[] = 'JSON_EXTRACT('.$adapter->quoteColumn($column).', '.$adapter->quoteValue('$.'.$attr).') < '.$adapter->quoteValue($v);
                            break;

                        case 'lte':
                            $result[] = 'JSON_EXTRACT('.$adapter->quoteColumn($column).', '.$adapter->quoteValue('$.'.$attr).') <= '.$adapter->quoteValue($v);
                            break;

                        case 'startswith':
                            $result[] = 'JSON_EXTRACT('.$adapter->quoteColumn($column).', '.$adapter->quoteValue('$.'.$attr).') LIKE '.$adapter->quoteValue($v.'%');
                            break;

                        case 'istartswith':
                            $result[] = 'LOWER(JSON_EXTRACT('.$adapter->quoteColumn($column).', '.$adapter->quoteValue('$.'.$attr).')) LIKE '.$adapter->quoteValue(mb_strtolower($v, 'UTF-8').'%');
                            break;

                        case 'endswith':
                            $result[] = 'JSON_EXTRACT('.$adapter->quoteColumn($column).', '.$adapter->quoteValue('$.'.$attr).') LIKE '.$adapter->quoteValue('%'.$v);
                            break;

                        case 'iendswith':
                            $result[] = 'LOWER(JSON_EXTRACT('.$adapter->quoteColumn($column).', '.$adapter->quoteValue('$.'.$attr).')) LIKE '.$adapter->quoteValue('%'.mb_strtolower($v, 'UTF-8'));
                            break;

                        case 'range':
                            list($min, $max) = $v;

                            $result[] = 'JSON_EXTRACT('.$adapter->quoteColumn($column).', '.$adapter->quoteValue('$.'.$attr).') BETWEEN '.$adapter->quoteValue($min).' AND '.$adapter->quoteValue($max);
                            break;

                        case 'isnull':
                            $result[] = 'JSON_EXTRACT('.$adapter->quoteColumn($column).', '.$adapter->quoteValue('$.'.$attr).') '.((bool) $v ? 'IS NULL' : 'IS NOT NULL');
                            break;

                        case 'in':
                            if (is_array($v)) {
                                $quotedValues = array_map(function ($item) use ($adapter) {
                                    return $adapter->quoteValue($item);
                                }, $v);
                                $sqlValue = implode(', ', $quotedValues);
                            } elseif ($value instanceof QueryBuilder) {
                                $sqlValue = $value->toSQL();
                            } else {
                                $sqlValue = $adapter->quoteSql($v);
                            }

                            $result[] = 'JSON_EXTRACT('.$adapter->quoteColumn($column).', '.$adapter->quoteValue('$.'.$attr).') IN ('.$sqlValue.')';
                            break;

                        case 'contains':
                            $result[] = 'JSON_EXTRACT('.$adapter->quoteColumn($column).', '.$adapter->quoteValue('$.'.$attr).') LIKE '.$adapter->quoteValue('%'.$v.'%');
                            break;

                        case 'icontains':
                            $result[] = 'LOWER(JSON_EXTRACT('.$adapter->quoteColumn($column).', '.$adapter->quoteValue('$.'.$attr).')) LIKE '.$adapter->quoteValue('%'.mb_strtolower($v, 'UTF-8').'%');
                            break;

                        case 'exact':
                        default:
                            $result[] = 'JSON_EXTRACT('.$adapter->quoteColumn($column).', '.$adapter->quoteValue('$.'.$attr).')='.$adapter->quoteValue((string) $v);
                            break;
                    }
                }

                return implode(' AND ', $result);

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
