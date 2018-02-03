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
     * @param $tableName
     * @param bool $ifExists
     * @param bool $cascade
     *
     * @return string
     */
    public function sqlDropTable($tableName, $ifExists = false, $cascade = false)
    {
        return parent::sqlDropTable($tableName, $ifExists, $cascade).($cascade ? ' CASCADE' : '');
    }

    /**
     * @param $tableName
     * @param bool $cascade
     *
     * @return string
     */
    public function sqlTruncateTable($tableName, $cascade = false)
    {
        return parent::sqlTruncateTable($tableName, $cascade).($cascade ? ' CASCADE' : '');
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
        return 'SELECT SETVAL('.$this->quoteColumn($sequenceName).', '.$this->quoteValue($value).', false)';
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
                $this->quoteColumn($table),
                $this->quoteColumn($schema),
                $check ? 'ENABLE' : 'DISABLE'
            );
    }

    /**
     * Builds a SQL statement for changing the definition of a column.
     *
     * @param string $table  the table whose column is to be changed. The table name will be properly quoted by the method.
     * @param string $column the name of the column to be changed. The name will be properly quoted by the method.
     * @param string $type   the new column type. The [[getColumnType()]] method will be invoked to convert abstract
     *                       column type (if any) into the physical one. Anything that is not recognized as abstract type will be kept
     *                       in the generated SQL. For example, 'string' will be turned into 'varchar(255)', while 'string not null'
     *                       will become 'varchar(255) not null'. You can also use PostgreSQL-specific syntax such as `SET NOT NULL`.
     *
     * @return string the SQL statement for changing the definition of a column
     */
    public function sqlAlterColumn($table, $column, $type)
    {
        // https://github.com/yiisoft/yii2/issues/4492
        // http://www.postgresql.org/docs/9.1/static/sql-altertable.html
        if (!preg_match('/^(DROP|SET|RESET)\s+/i', $type)) {
            $type = 'TYPE '.$type;
        }

        return 'ALTER TABLE '.$this->quoteTableName($table).' ALTER COLUMN '
            .$this->quoteColumn($column).' '.$type;
    }

    /**
     * @return ExpressionBuilder
     */
    public function getLookupCollection()
    {
        return new ExpressionBuilder();
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
     * @param $oldTableName
     * @param $newTableName
     *
     * @return string
     */
    public function sqlRenameTable($oldTableName, $newTableName)
    {
        return 'ALTER TABLE '.$this->quoteTableName($oldTableName).' RENAME TO '.$this->quoteTableName($newTableName);
    }

    /**
     * @param $tableName
     * @param $name
     *
     * @return string
     */
    public function sqlDropIndex($tableName, $name)
    {
        return 'DROP INDEX '.$this->quoteColumn($name);
    }

    /**
     * @param string $tableName
     * @param string $name
     *
     * @return string
     */
    public function sqlDropPrimaryKey($tableName, $name)
    {
        return 'ALTER TABLE '.$this->quoteTableName($tableName).' DROP CONSTRAINT '.$this->quoteColumn($name);
    }

    /**
     * @param $tableName
     * @param $oldName
     * @param $newName
     *
     * @return mixed
     */
    public function sqlRenameColumn($tableName, $oldName, $newName)
    {
        return "ALTER TABLE {$this->quoteTableName($tableName)} RENAME COLUMN ".$this->quoteColumn($oldName).' TO '.$this->quoteColumn($newName);
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

    /**
     * @param $tableName
     * @param $column
     * @param $type
     *
     * @return string
     */
    public function sqlAddColumn($tableName, $column, $type)
    {
        return 'ALTER TABLE '.$this->quoteTableName($tableName).' ADD '.$this->quoteColumn($column).' '.$type;
    }

    /**
     * @param $tableName
     * @param $name
     *
     * @return mixed
     */
    public function sqlDropForeignKey($tableName, $name)
    {
        return 'ALTER TABLE '.$this->quoteTableName($tableName).' DROP CONSTRAINT '.$this->quoteColumn($name);
    }
}
