<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Q;

use Exception;
use Mindy\QueryBuilder\AdapterInterface;
use Mindy\QueryBuilder\Expression;
use Mindy\QueryBuilder\LookupBuilderInterface;
use Mindy\QueryBuilder\QueryBuilder;
use Mindy\QueryBuilder\QueryBuilderAwareInterface;
use Mindy\QueryBuilder\QueryBuilderInterface;

abstract class Q implements QueryBuilderAwareInterface
{
    /**
     * @var array|string|Q
     */
    protected $where;
    /**
     * @var string
     */
    protected $operator;
    /**
     * @var LookupBuilderInterface
     */
    protected $lookupBuilder;
    /**
     * @var AdapterInterface
     */
    protected $adapter;
    /**
     * @var QueryBuilderInterface|null
     */
    protected $queryBuilder;
    /**
     * @var string|null
     */
    private $_tableAlias;

    public function __construct($where)
    {
        $this->where = $where;
    }

    /**
     * {@inheritdoc}
     */
    public function setQueryBuilder(QueryBuilderInterface $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    public function setTableAlias($tableAlias)
    {
        $this->_tableAlias = $tableAlias;
    }

    public function setLookupBuilder(LookupBuilderInterface $lookupBuilder)
    {
        $this->lookupBuilder = $lookupBuilder;

        return $this;
    }

    public function setAdapter(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }

    public function getWhere()
    {
        return $this->where;
    }

    public function addWhere($where)
    {
        $this->where[] = $where;

        return $this;
    }

    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * {@inheritdoc}
     */
    public function toSQL(): string
    {
        return $this->parseWhere($this->queryBuilder);
    }

    /**
     * @return string
     */
    protected function parseWhere(QueryBuilderInterface $queryBuilder)
    {
        return $this->parseConditions($queryBuilder, $this->where);
    }

    private function isWherePart($where)
    {
        return is_array($where) &&
        array_key_exists('___operator', $where) &&
        array_key_exists('___where', $where) &&
        array_key_exists('___condition', $where);
    }

    /**
     * @param array $where
     *
     * @return string
     */
    protected function parseConditions(QueryBuilderInterface $queryBuilder, $where)
    {
        if (empty($where)) {
            return '';
        }

        $sql = '';
        if ($this->isWherePart($where)) {
            $operator = $where['___operator'];
            $childWhere = $where['___where'];
            $condition = $where['___condition'];
            if ($this->isWherePart($childWhere)) {
                $whereSql = $this->parseConditions($queryBuilder, $childWhere);
                $sql .= '('.$whereSql.') '.strtoupper($operator).' ('.$this->parsePart($condition, $operator).')';
            } else {
                $sql .= $this->parsePart($queryBuilder, $childWhere, $operator);
            }
        } else {
            $sql .= $this->parsePart($queryBuilder, $where);
        }

        if (empty($sql)) {
            return '';
        }

        return $sql;
    }

    /**
     * @param $part
     *
     * @throws Exception
     *
     * @return string
     */
    protected function parsePart(QueryBuilderInterface $queryBuilder, $part, $operator = null)
    {
        if (null === $operator) {
            $operator = $this->getOperator();
        }

        if (is_string($part)) {
            return $part;
        } elseif (is_array($part)) {
            $sql = [];
            foreach ($part as $key => $value) {
                // TODO test me
                if (is_bool($value)) {
                    $value = (int) $value;
                }

                if ($part instanceof QueryBuilder) {
                    $sql[] = $part->toSQL();
                } elseif ($value instanceof self) {
                    $sql[] = '('.$this->parsePart($queryBuilder, $value).')';
                } elseif (is_numeric($key) && is_array($value)) {
                    $sql[] = '('.$this->parsePart($queryBuilder, $value).')';
                } else {
                    list($lookup, $column, $lookupValue) = $this->lookupBuilder->parseLookup($queryBuilder, $key, $value);
                    if (false === empty($this->_tableAlias) && false === strpos($column, '.')) {
                        $column = $this->_tableAlias.'.'.$column;
                    }
                    $sql[] = $this->lookupBuilder->runLookup($this->adapter, $lookup, $column, $lookupValue);
                }
            }

            return implode(' '.$operator.' ', $sql);
        } elseif ($part instanceof Expression) {
            return $this->adapter->quoteSql($part->toSQL());
        } elseif ($part instanceof self) {
            $part->setLookupBuilder($this->lookupBuilder);
            $part->setAdapter($this->adapter);
            $part->setTableAlias($this->_tableAlias);

            return $part->toSQL($queryBuilder);
        } elseif ($part instanceof QueryBuilder) {
            return $part->toSQL();
        }
        throw new Exception('Unknown sql part type');
    }
}
