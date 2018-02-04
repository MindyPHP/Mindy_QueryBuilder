<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Database\Sqlite;

use Exception;
use Mindy\QueryBuilder\AdapterInterface;
use Mindy\QueryBuilder\BaseAdapter;
use Mindy\QueryBuilder\Exception\NotSupportedException;

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
     * @param $sequenceName
     * @param $value
     *
     * @return string
     */
    public function sqlResetSequence($sequenceName, $value = null)
    {
        return 'UPDATE sqlite_sequence SET seq='.$this->quoteValue($value).' WHERE name='.$this->quoteValue($sequenceName);
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
        return 'PRAGMA foreign_keys='.$this->getBoolean($check);
    }
}
