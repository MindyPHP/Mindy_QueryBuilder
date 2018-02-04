<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Mindy\QueryBuilder\Aggregation\Aggregation;
use Mindy\QueryBuilder\Q\Q;
use Mindy\QueryBuilder\Utils\TableNameResolver;

abstract class BaseAdapter implements AdapterInterface
{
    /**
     * @var string
     */
    protected $tablePrefix = '';
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * BaseAdapter constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return string
     */
    public function getTablePrefix()
    {
        return $this->tablePrefix;
    }

    /**
     * @return BaseExpressionBuilder|LookupCollectionInterface
     */
    abstract public function getLookupCollection();

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * TODO remove
     * {@inheritdoc}
     */
    public function quoteSql(string $sql): string
    {
        $tablePrefix = $this->tablePrefix;

        return preg_replace_callback(
            '/(\\{\\{(%?[\w\-\. ]+%?)\\}\\}|\\[\\[([\w\-\. ]+)\\]\\])|\\@([\w\-\. \/\%\:]+)\\@/',
            function ($matches) use ($tablePrefix) {
                if (isset($matches[4])) {
                    return $this->connection->quote($this->convertToDbValue($matches[4]));
                } elseif (isset($matches[3])) {
                    return $this->getQuotedName($matches[3]);
                }

                return str_replace('%', $tablePrefix, $this->getQuotedName($matches[2]));
            },
            $sql
        );
    }

    /**
     * TODO remove
     *
     * @param $rawValue
     * @return string
     */
    public function convertToDbValue($rawValue)
    {
        if (true === $rawValue || false === $rawValue || 'true' === $rawValue || 'false' === $rawValue) {
            return $this->getBoolean($rawValue);
        } elseif ('null' === $rawValue || null === $rawValue) {
            return 'NULL';
        }

        return $rawValue;
    }

    public function sqlUpdate($tableName, array $columns)
    {
        $tableName = TableNameResolver::getTableName($tableName, $this->tablePrefix);
        $parts = [];
        foreach ($columns as $column => $value) {
            if ($value instanceof ToSqlInterface) {
                $val = $this->quoteSql($value->toSQL());
            } else {
                // TODO refact, use getSqlType
                if ('true' === $value || true === $value) {
                    $val = 'TRUE';
                } elseif (null === $value || 'null' === $value) {
                    $val = 'NULL';
                } elseif (false === $value || 'false' === $value) {
                    $val = 'FALSE';
                } else {
                    $val = $this->connection->quote($value);
                }
            }
            $parts[] = $this->getQuotedName($column).'='.$val;
        }

        return 'UPDATE '.$this->getQuotedName($tableName).' SET '.implode(', ', $parts);
    }

    /**
     * @param $select
     * @param $from
     * @param $where
     * @param $order
     * @param $group
     * @param $limit
     * @param $offset
     * @param $join
     * @param $having
     * @param $union
     * @param $distinct
     *
     * @return string
     */
    public function generateSelectSQL($select, $from, $where, $order, $group, $limit, $offset, $join, $having, $union, $distinct)
    {
        if (empty($order)) {
            $orderColumns = [];
            $orderOptions = null;
        } else {
            list($orderColumns, $orderOptions) = $order;
        }

        $where = $this->sqlWhere($where);
        $orderSql = $this->sqlOrderBy($orderColumns, $orderOptions);
        $unionSql = $this->sqlUnion($union);

        return strtr('{select}{from}{join}{where}{group}{having}{order}{limit_offset}{union}', [
            '{select}' => $this->sqlSelect($select, $distinct),
            '{from}' => $this->sqlFrom($from),
            '{where}' => $where,
            '{group}' => $this->sqlGroupBy($group),
            '{order}' => empty($union) ? $orderSql : '',
            '{having}' => $this->sqlHaving($having),
            '{join}' => $join,
            '{limit_offset}' => $this->sqlLimitOffset($limit, $offset),
            '{union}' => empty($union) ? '' : $unionSql.$orderSql,
        ]);
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function getSqlType($value)
    {
        if ('true' === $value || true === $value) {
            return 'TRUE';
        } elseif (null === $value || 'null' === $value) {
            return 'NULL';
        } elseif (false === $value || 'false' === $value) {
            return 'FALSE';
        }

        return $value;
    }

    /**
     * @return string
     */
    abstract public function getRandomOrder();

    /**
     * @param $value
     *
     * @return string
     */
    public function getBoolean($value = null)
    {
        return $this->connection->getDatabasePlatform()->convertBooleans($value);
    }

    /**
     * @param $where string|array
     *
     * @return string
     */
    public function sqlWhere($where)
    {
        if (empty($where)) {
            return '';
        }

        return ' WHERE '.$this->quoteSql($where);
    }

    /**
     * @param bool   $check
     * @param string $schema
     * @param string $table
     *
     * @return string
     */
    abstract public function sqlCheckIntegrity($check = true, $schema = '', $table = '');

    /**
     * // TODO move from here to expression builder
     * @param string $str
     * @return string
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getQuotedName($str): string
    {
        $platform = $this->connection->getDatabasePlatform();
        $keywords = $platform->getReservedKeywordsList();
        $parts = explode(".", (string)$str);
        foreach ($parts as $k => $v) {
            $parts[$k] = ($keywords->isKeyword($v)) ? $platform->quoteIdentifier($v) : $v;
        }

        return implode(".", $parts);
    }
}
