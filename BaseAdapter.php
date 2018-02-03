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
     * Quotes a simple column name for use in a query.
     * A simple column name should contain the column name only without any prefix.
     * If the column name is already quoted or is the asterisk character '*', this method will do nothing.
     *
     * @param string $name column name
     *
     * @return string the properly quoted column name
     */
    public function quoteSimpleColumnName($name)
    {
        return false !== strpos($name, '"') || '*' === $name ? $name : '"'.$name.'"';
    }

    /**
     * @param $name
     *
     * @return string
     */
    public function getRawTableName($name): string
    {
        return TableNameResolver::getTableName($name, $this->getTablePrefix());
    }

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
     * Quotes a simple table name for use in a query.
     * A simple table name should contain the table name only without any schema prefix.
     * If the table name is already quoted, this method will do nothing.
     *
     * @param string $name table name
     *
     * @return string the properly quoted table name
     */
    public function quoteSimpleTableName($name)
    {
        return false !== strpos($name, "'") ? $name : "'".$name."'";
    }

    /**
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

    public function convertToDbValue($rawValue)
    {
        if (true === $rawValue || false === $rawValue || 'true' === $rawValue || 'false' === $rawValue) {
            return $this->getBoolean($rawValue);
        } elseif ('null' === $rawValue || null === $rawValue) {
            return 'NULL';
        }

        return $rawValue;
    }

    /**
     * @param int $limit
     * @param int $offset
     *
     * @return string the LIMIT and OFFSET clauses
     */
    public function sqlLimitOffset($limit = null, $offset = null)
    {
        $qb = $this->getConnection()->createQueryBuilder();
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);

        return trim(str_replace('SELECT', '', $qb->getSQL()));
    }

    /**
     * @param $columns
     *
     * @return array|string
     */
    public function buildColumns($columns)
    {
        if (!is_array($columns)) {
            if ($columns instanceof Aggregation) {
                $columns->setFieldsSql($this->buildColumns($columns->getFields()));

                return $this->quoteSql($columns->toSQL());
            } elseif (false !== strpos($columns, '(')) {
                return $this->quoteSql($columns);
            }
            $columns = preg_split('/\s*,\s*/', $columns, -1, PREG_SPLIT_NO_EMPTY);
        }
        foreach ($columns as $i => $column) {
            if ($column instanceof Expression) {
                $columns[$i] = $this->quoteSql($column->toSQL());
            } elseif (false !== strpos($column, 'AS')) {
                if (preg_match('/^(.*?)(?i:\s+as\s+|\s+)([\w\-_\.]+)$/', $column, $matches)) {
                    list(, $rawColumn, $rawAlias) = $matches;
                    $columns[$i] = $this->getQuotedName($rawColumn).' AS '.$this->getQuotedName($rawAlias);
                }
            } elseif (false === strpos($column, '(')) {
                $columns[$i] = $this->getQuotedName($column);
            }
        }

        return is_array($columns) ? implode(', ', $columns) : $columns;
    }

    /**
     * @param $tableName
     * @param array $rows
     *
     * @return string
     */
    public function sqlInsert($tableName, array $rows)
    {
        if (isset($rows[0]) && is_array($rows)) {
            $columns = array_map(function ($column) {
                return $this->getQuotedName($column);
            }, array_keys($rows[0]));

            $values = [];

            foreach ($rows as $row) {
                $record = [];
                foreach ($row as $value) {
                    if ($value instanceof Expression) {
                        $value = $value->toSQL();
                    } elseif (true === $value || 'true' === $value) {
                        $value = 'TRUE';
                    } elseif (false === $value || 'false' === $value) {
                        $value = 'FALSE';
                    } elseif (null === $value || 'null' === $value) {
                        $value = 'NULL';
                    } elseif (is_string($value)) {
                        $value = $this->quoteValue($value);
                    }

                    $record[] = $value;
                }
                $values[] = '('.implode(', ', $record).')';
            }

            $sql = 'INSERT INTO '.$this->getQuotedName($tableName).' ('.implode(', ', $columns).') VALUES '.implode(', ', $values);

            return $this->quoteSql($sql);
        }
        $columns = array_map(function ($column) {
            return $this->getQuotedName($column);
        }, array_keys($rows));

        $values = array_map(function ($value) {
            if ($value instanceof Expression) {
                $value = $value->toSQL();
            } elseif (true === $value || 'true' === $value) {
                $value = 'TRUE';
            } elseif (false === $value || 'false' === $value) {
                $value = 'FALSE';
            } elseif (null === $value || 'null' === $value) {
                $value = 'NULL';
            } elseif (is_string($value)) {
                $value = $this->quoteValue($value);
            }

            return $value;
        }, $rows);

        $sql = 'INSERT INTO '.$this->getQuotedName($tableName).' ('.implode(', ', $columns).') VALUES ('.implode(', ', $values).')';

        return $this->quoteSql($sql);
    }

    public function sqlUpdate($tableName, array $columns)
    {
        $tableName = $this->getRawTableName($tableName);
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
     * @param $tableName
     * @param array $columns
     * @param null  $options
     * @param bool  $ifNotExists
     *
     * @return string
     */
    public function sqlCreateTable($tableName, $columns, $options = null, $ifNotExists = false)
    {
        $tableName = $this->getRawTableName($tableName);
        if (is_array($columns)) {
            $cols = [];
            foreach ($columns as $name => $type) {
                if (is_string($name)) {
                    $cols[] = "\t".$this->getQuotedName($name).' '.$type;
                } else {
                    $cols[] = "\t".$type;
                }
            }
            $sql = ($ifNotExists ? 'CREATE TABLE IF NOT EXISTS ' : 'CREATE TABLE ').$this->getQuotedName($tableName)." (\n".implode(",\n", $cols)."\n)";
        } else {
            $sql = ($ifNotExists ? 'CREATE TABLE IF NOT EXISTS ' : 'CREATE TABLE ').$this->getQuotedName($tableName).' '.$this->quoteSql($columns);
        }

        return empty($options) ? $sql : $sql.' '.$options;
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

    public function formatDateTime($value)
    {
        if ($value instanceof \DateTime) {
            return $value;
        }

        if (is_numeric($value)) {
            $date = new \DateTime();
            $date->setTimestamp((int)$value);
            return $date;
        } else if (null === $value) {
            return new \DateTime();
        } else {
            return new \DateTime($value);
        }
    }

    /**
     * @param null $value
     *
     * @return string
     */
    public function getDateTime($value = null)
    {
        return Type::getType(Type::DATETIME)
            ->convertToDatabaseValue(
                $this->formatDateTime($value),
                $this->connection->getDatabasePlatform()
            );
    }

    /**
     * @param null $value
     *
     * @return string
     */
    public function getDate($value = null)
    {
        return Type::getType(Type::DATE)
            ->convertToDatabaseValue(
                $this->formatDateTime($value),
                $this->connection->getDatabasePlatform()
            );
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
            $tableName = $this->getRawTableName($tableName);
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

    public function generateInsertSQL($tableName, $values)
    {
        return $this->sqlInsert($tableName, $values);
    }

    public function generateDeleteSQL($from, $where)
    {
        return '';
    }

    public function generateUpdateSQL($tableName, $update, $where)
    {
        return strtr('{update}{where}', [
            '{update}' => $this->sqlUpdate($tableName, $update),
            '{where}' => $this->sqlWhere($where),
        ]);
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
        return $value;
    }
}
