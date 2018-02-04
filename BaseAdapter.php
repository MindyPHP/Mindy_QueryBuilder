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
     * {@inheritdoc}
     */
    public function quoteValue($str)
    {
        if (!is_string($str)) {
            return $str;
        }

        return $this->getConnection()->quote($str);
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
                    return $this->quoteValue($this->convertToDbValue($matches[4]));
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
                    $val = $this->quoteValue($value);
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
     * @param null $value
     *
     * @return mixed
     */
    public function getTimestamp($value = null)
    {
        return $value instanceof \DateTime ? $value->getTimestamp() : strtotime($value);
    }

    /**
     * @param $joinType string
     * @param $tableName string
     * @param $on string|array
     * @param $alias string
     *
     * @return string
     */
    public function sqlJoin($joinType, $tableName, $on, $alias)
    {
        if (is_string($tableName)) {
            $tableName = TableNameResolver::getTableName($tableName, $this->tablePrefix);
        } elseif ($tableName instanceof QueryBuilder) {
            $tableName = $tableName->toSQL();
        }

        $onSQL = [];
        if (is_string($on)) {
            $onSQL[] = $this->quoteSql($on);
        } else {
            foreach ($on as $leftColumn => $rightColumn) {
                if ($rightColumn instanceof Expression) {
                    $onSQL[] = $this->getQuotedName($leftColumn).'='.$this->quoteSql($rightColumn->toSQL());
                } else {
                    $onSQL[] = $this->getQuotedName($leftColumn).'='.$this->getQuotedName($rightColumn);
                }
            }
        }

        if (false !== strpos($tableName, 'SELECT')) {
            return $joinType.' ('.$this->quoteSql($tableName).')'.(empty($alias) ? '' : ' AS '.$this->getQuotedName($alias)).' ON '.implode(',', $onSQL);
        }

        return $joinType.' '.$this->getQuotedName($tableName).(empty($alias) ? '' : ' AS '.$this->getQuotedName($alias)).' ON '.implode(',', $onSQL);
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
     * @param $having
     * @param QueryBuilder $qb
     *
     * @return string
     */
    public function sqlHaving($having, QueryBuilder $qb)
    {
        if (empty($having)) {
            return '';
        }

        if ($having instanceof Q) {
            $sql = $having->toSQL($qb);
        } else {
            $sql = $this->quoteSql($having);
        }

        return empty($sql) ? '' : ' HAVING '.$sql;
    }

    /**
     * @param $unions
     *
     * @return string
     */
    public function sqlUnion($union, $all = false)
    {
        if (empty($union)) {
            return '';
        }

        if ($union instanceof QueryBuilder) {
            $unionSQL = $union->order(null)->toSQL();
        } else {
            $unionSQL = $this->quoteSql($union);
        }

        return ($all ? 'UNION ALL' : 'UNION').' ('.$unionSQL.')';
    }

    /**
     * @param $tableName
     * @param $sequenceName
     *
     * @return string
     */
    abstract public function sqlResetSequence($tableName, $sequenceName);

    /**
     * @param bool   $check
     * @param string $schema
     * @param string $table
     *
     * @return string
     */
    abstract public function sqlCheckIntegrity($check = true, $schema = '', $table = '');

    /**
     * @param $columns
     *
     * @return string
     */
    public function sqlGroupBy($columns)
    {
        if (empty($columns)) {
            return '';
        }

        if (is_string($columns)) {
            $quotedColumns = array_map(function ($column) {
                return $this->getQuotedName($column);
            }, explode(',', $columns));

            return implode(', ', $quotedColumns);
        }
        $group = [];
        foreach ($columns as $column) {
            $group[] = $this->getQuotedName($column);
        }

        return implode(', ', $group);
    }

    /**
     * @param $columns
     * @param null $options
     *
     * @return string
     */
    public function sqlOrderBy($columns, $options = null)
    {
        if (empty($columns)) {
            return '';
        }

        if (is_string($columns)) {
            $columns = preg_split('/\s*,\s*/', $columns, -1, PREG_SPLIT_NO_EMPTY);
            $quotedColumns = array_map(function ($column) {
                $temp = explode(' ', $column);
                if (2 == count($temp)) {
                    return $this->getQuotedName($temp[0]).' '.$temp[1];
                }

                return $this->getQuotedName($column);
            }, $columns);

            return implode(', ', $quotedColumns);
        }

        $order = [];
        foreach ($columns as $key => $column) {
            if (is_numeric($key)) {
                if (0 === strpos($column, '-', 0)) {
                    $column = substr($column, 1);
                    $direction = 'DESC';
                } else {
                    $direction = 'ASC';
                }
            } else {
                $direction = $column;
                $column = $key;
            }

            $order[] = $this->getQuotedName($column).' '.$direction;
        }

        return implode(', ', $order).(empty($options) ? '' : ' '.$options);
    }

    /**
     * // TODO move from here to expression builder
     * @param string $str
     * @return string
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getQuotedName(string $str): string
    {
        $platform = $this->connection->getDatabasePlatform();
        $keywords = $platform->getReservedKeywordsList();
        $parts = explode(".", $str);
        foreach ($parts as $k => $v) {
            $parts[$k] = ($keywords->isKeyword($v)) ? $platform->quoteIdentifier($v) : $v;
        }

        return implode(".", $parts);
    }

    /**
     * @param array|null|string $columns
     * @param null              $distinct
     *
     * @throws \Exception
     *
     * @return string
     */
    public function sqlSelect($columns, $distinct = null)
    {
        $selectSql = $distinct ? 'SELECT DISTINCT ' : 'SELECT ';
        if (empty($columns)) {
            return $selectSql.'*';
        }

        if (false === is_array($columns)) {
            $columns = [$columns];
        }

        $select = [];
        foreach ($columns as $column => $subQuery) {
            if ($subQuery instanceof QueryBuilder) {
                $subQuery = $subQuery->toSQL();
            } elseif ($subQuery instanceof Expression) {
                $subQuery = $this->quoteSql($subQuery->toSQL());
            } else {
                $subQuery = $this->quoteSql($subQuery);
            }

            if (is_numeric($column)) {
                $column = $subQuery;
                $subQuery = '';
            }

            if (!empty($subQuery)) {
                if (false !== strpos($subQuery, 'SELECT')) {
                    $value = '('.$subQuery.') AS '.$this->getQuotedName($column);
                } else {
                    $value = $this->getQuotedName($subQuery).' AS '.$this->getQuotedName($column);
                }
            } else {
                if (false === strpos($column, ',') && false !== strpos($column, 'AS')) {
                    if (false !== strpos($column, 'AS')) {
                        list($rawColumn, $rawAlias) = explode('AS', $column);
                    } else {
                        $rawColumn = $column;
                        $rawAlias = '';
                    }

                    $value = empty($rawAlias) ? $this->getQuotedName(trim($rawColumn)) : $this->getQuotedName(trim($rawColumn)).' AS '.$this->getQuotedName(trim($rawAlias));
                } elseif (false !== strpos($column, ',')) {
                    $newSelect = [];
                    foreach (explode(',', $column) as $item) {
                        // if (preg_match('/^(.*?)(?i:\s+as\s+|\s+)([\w\-_\.]+)$/', $item, $matches)) {
                        //     list(, $rawColumn, $rawAlias) = $matches;
                        // }

                        if (false !== strpos($item, 'AS')) {
                            list($rawColumn, $rawAlias) = explode('AS', $item);
                        } else {
                            $rawColumn = $item;
                            $rawAlias = '';
                        }

                        $newSelect[] = empty($rawAlias) ? $this->getQuotedName(trim($rawColumn)) : $this->getQuotedName(trim($rawColumn)).' AS '.$this->getQuotedName(trim($rawAlias));
                    }
                    $value = implode(', ', $newSelect);
                } else {
                    $value = $this->getQuotedName($column);
                }
            }
            $select[] = $value;
        }

        return $selectSql.implode(', ', $select);
    }
}
