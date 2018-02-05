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
use Mindy\QueryBuilder\BaseAdapter;

class Adapter extends BaseAdapter implements AdapterInterface
{
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
}
