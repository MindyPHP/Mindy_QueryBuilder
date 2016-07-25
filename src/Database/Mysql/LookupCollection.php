<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 20/06/16
 * Time: 15:04
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
            'hour', 'day', 'month', 'week_day'
        ];
        if (in_array($lookup, $lookups)) {
            return true;
        } else {
            return parent::has($lookup);
        }
    }

    /**
     * @param IAdapter $adapter
     * @param $lookup
     * @param $column
     * @param $value
     * @return string
     */
    public function process(IAdapter $adapter, $lookup, $column, $value)
    {
        switch ($lookup) {
            case 'regex':
                return 'BINARY ' . $adapter->quoteColumn($column) . ' REGEXP ' . $value;

            case 'iregex':
                return $adapter->quoteColumn($column) . ' REGEXP ' . $value;

            case 'second':
                return 'EXTRACT(SECOND FROM ' . $adapter->quoteColumn($column) . ')=' . $value;

            case 'year':
                return 'EXTRACT(YEAR FROM ' . $adapter->quoteColumn($column) . ')=' . $value;

            case 'minute':
                return 'EXTRACT(MINUTE FROM ' . $adapter->quoteColumn($column) . ')=' . $value;

            case 'hour':
                return 'EXTRACT(HOUR FROM ' . $adapter->quoteColumn($column) . ')=' . $value;

            case 'day':
                return 'EXTRACT(DAY FROM ' . $adapter->quoteColumn($column) . ')=' . $value;

            case 'month':
                return 'EXTRACT(MONTH FROM ' . $adapter->quoteColumn($column) . ')=' . $value;

            case 'week_day':
                return 'EXTRACT(DAYOFWEEK FROM ' . $adapter->quoteColumn($column) . ')=' . $value;
        }

        return parent::process($adapter, $lookup, $column, $value);
    }
}