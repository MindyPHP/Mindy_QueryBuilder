<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Database\Pgsql;

use Exception;
use Mindy\QueryBuilder\AdapterInterface;
use Mindy\QueryBuilder\BaseAdapter;

class Adapter extends BaseAdapter implements AdapterInterface
{
    /**
     * @param string $str
     *
     * @return string
     */
    public function quoteValue($str)
    {
        if (true === $str || 'true' === $str) {
            return 'TRUE';
        } elseif (false === $str || 'false' === $str) {
            return 'FALSE';
        } elseif (null === $str || 'null' === $str) {
            return 'NULL';
        }

        return parent::quoteValue($str);
    }

    /**
     * Creates a SQL statement for resetting the sequence value of a table's primary key.
     * The sequence will be reset such that the primary key of the next new row inserted
     * will have the specified value or 1.
     *
     * @param string $sequenceName the name of the table whose primary key sequence will be reset
     * @param mixed  $value        the value for the primary key of the next new row inserted. If this is not set,
     *                             the next new row's primary key will have a value 1.
     *
     * @throws Exception if the table does not exist or there is no sequence associated with the table
     *
     * @return string the SQL statement for resetting sequence
     */
    public function sqlResetSequence($sequenceName, $value)
    {
        return 'SELECT SETVAL('.$this->getQuotedName($sequenceName).', '.$this->quoteValue($value).', false)';
    }

    /**
     * Builds a SQL statement for enabling or disabling integrity check.
     *
     * @param bool   $check  whether to turn on or off the integrity check
     * @param string $schema the schema of the tables
     * @param string $table  the table name
     *
     * @return string the SQL statement for checking integrity
     */
    public function sqlCheckIntegrity($check = true, $schema = '', $table = '')
    {
        if (empty($schema) && empty($table)) {
            return 'SET CONSTRAINTS ALL '.($check ? 'IMMEDIATE' : 'DEFERRED');
        }

        return sprintf(
                'ALTER TABLE %s.%s %s TRIGGER ALL',
                $this->getQuotedName($table),
                $this->getQuotedName($schema),
                $check ? 'ENABLE' : 'DISABLE'
            );
    }

    /**
     * @return ExpressionBuilder
     */
    public function getLookupCollection()
    {
        return new ExpressionBuilder($this->connection);
    }

    /**
     * @return string
     */
    public function getRandomOrder()
    {
        return 'RANDOM()';
    }

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
        return false !== strpos($name, '"') ? $name : '"'.$name.'"';
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function getBoolean($value = null)
    {
        return (bool) $value ? 'TRUE' : 'FALSE';
    }

    /**
     * @param $value
     * @param $format
     *
     * @return bool|string
     */
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
}
