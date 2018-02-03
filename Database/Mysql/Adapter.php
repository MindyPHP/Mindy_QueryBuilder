<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Database\Mysql;

use Mindy\QueryBuilder\BaseAdapter;

class Adapter extends BaseAdapter
{
    /**
     * Quotes a table name for use in a query.
     * A simple table name has no schema prefix.
     *
     * @param string $name table name
     *
     * @return string the properly quoted table name
     */
    public function quoteSimpleTableName($name)
    {
        return false !== strpos($name, '`') ? $name : '`'.$name.'`';
    }

    /**
     * Quotes a column name for use in a query.
     * A simple column name has no prefix.
     *
     * @param string $name column name
     *
     * @return string the properly quoted column name
     */
    public function quoteSimpleColumnName($name)
    {
        return false !== strpos($name, '`') || '*' === $name ? $name : '`'.$name.'`';
    }

    /**
     * @return array
     */
    public function getLookupCollection()
    {
        return new ExpressionBuilder($this->connection);
    }

    public function getRandomOrder()
    {
        return 'RAND()';
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function getBoolean($value = null)
    {
        if ('boolean' === gettype($value)) {
            return (int) $value;
        }

        return $value ? 1 : 0;
    }

    public function formatDateTime($value, $format)
    {
        if ($value instanceof \DateTime) {
            $value = $value->format($format);
        } elseif (null === $value) {
            $value = date($format);
        } elseif (is_numeric($value)) {
            $value = date($format, (int) $value);
        } elseif (is_string($value)) {
            $value = date($format, strtotime($value));
        }

        return (string) $value;
    }

    /**
     * @param null $value
     *
     * @return string
     */
    public function getDateTime($value = null)
    {
        return $this->formatDateTime($value, 'Y-m-d H:i:s');
    }

    /**
     * @param null $value
     *
     * @return string
     */
    public function getDate($value = null)
    {
        return $this->formatDateTime($value, 'Y-m-d');
    }

    /**
     * @param $tableName
     * @param $value
     *
     * @return string
     *
     * @internal param $sequenceName
     */
    public function sqlResetSequence($tableName, $value)
    {
        return 'ALTER TABLE '.$this->getQuotedName($tableName).' AUTO_INCREMENT='.(int) $value;
    }

    /**
     * @param bool   $check
     * @param string $schema
     * @param string $table
     *
     * @return string
     */
    public function sqlCheckIntegrity($check = true, $schema = '', $table = '')
    {
        return 'SET FOREIGN_KEY_CHECKS = '.$this->getBoolean($check);
    }

    /**
     * Prepare value for db.
     *
     * @param $value
     *
     * @return int
     */
    public function prepareValue($value)
    {
        if ('boolean' === gettype($value)) {
            return (int) $value;
        }

        return parent::prepareValue($value);
    }
}
