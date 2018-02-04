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
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\DateTimeTzType;
use Doctrine\DBAL\Types\DateType;
use Doctrine\DBAL\Types\Type;

class BaseExpressionBuilder extends ExpressionBuilder implements LookupCollectionInterface
{
    /**
     * @var Connection
     */
    protected $connection;
    /**
     * @var \Doctrine\DBAL\Schema\Schema
     */
    protected $schema;
    /**
     * @var string
     */
    protected $tableName;

    /**
     * BaseExpressionBuilder constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->schema = $connection->getSchemaManager()->createSchema();
    }

    /**
     * @param string $table
     */
    public function setTableName(string $table)
    {
        $this->tableName = $table;
    }

    /**
     * @param $value
     * @param string $type
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return mixed
     */
    protected function castToType($value, string $type)
    {
        $platform = $this->connection->getDatabasePlatform();

        return Type::getType($type)->convertToDatabaseValue($value, $platform);
    }

    /**
     * @param $value
     *
     * @return \DateTime
     */
    public function formatDateTime($value): \DateTime
    {
        if ($value instanceof \DateTime) {
            return $value;
        }

        if (is_numeric($value)) {
            $date = new \DateTime();
            $date->setTimestamp((int) $value);

            return $date;
        } elseif (null === $value) {
            return new \DateTime();
        } else {
            return new \DateTime($value);
        }
    }

    /**
     * @param string $x
     * @param $y
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Schema\SchemaException
     *
     * @return mixed|null
     */
    protected function castToDatabaseValue(string $x, $y)
    {
        $table = $this->schema->getTable($this->tableName);
        if ($table->hasColumn($x)) {
            $type = $table->getColumn($x)->getType();

            if (
                $type instanceof DateType ||
                $type instanceof DateTimeType ||
                $type instanceof DateTimeTzType
            ) {
                // if value is mixed and column type is date
                // convert to \DateTime
                $y = $this->formatDateTime($y);
            } elseif ($y instanceof \DateTime) {
                // if value is datetime, but column not a date or datetime
                // convert value to string
                $y = $this->castToDateTime($y);
            }

            return $type->convertToDatabaseValue($y, $this->connection->getDatabasePlatform());
        }

        return $this->castToType($y, Type::STRING);
    }

    protected function castToDate($value)
    {
        return $this->castToType($this->formatDateTime($value), Type::DATE);
    }

    protected function castToDateTime($value)
    {
        return $this->castToType($this->formatDateTime($value), Type::DATETIME);
    }

    /**
     * @param string $str
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return string
     */
    public function getQuotedName(string $str): string
    {
        $platform = $this->connection->getDatabasePlatform();
        $keywords = $platform->getReservedKeywordsList();
        $parts = explode('.', $str);
        foreach ($parts as $k => $v) {
            $parts[$k] = ($keywords->isKeyword($v)) ? $platform->quoteIdentifier($v) : $v;
        }

        return implode('.', $parts);
    }

    public function parse(string $str): array
    {
        $lookups = explode('__', $str);
        $column = array_shift($lookups);
        if (0 == count($lookups)) {
            $lookups[] = 'exact';
        }

        return [$column, $lookups];
    }

    protected function formatMethod(string $lookup): string
    {
        $toCamelCase = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $lookup))));

        return sprintf('lookup%s', $toCamelCase);
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $lookup): bool
    {
        return method_exists($this, $this->formatMethod($lookup));
    }

    /**
     * {@inheritdoc}
     */
    public function process(AdapterInterface $adapter, $lookup, $x, $y)
    {
        if (false === $this->has($lookup)) {
            throw new \RuntimeException(sprintf(
                'Unsupported lookup: %s',
                $lookup
            ));
        }

        return call_user_func_array([$this, $this->formatMethod($lookup)], [$adapter, $x, $y]);
    }

    protected function lookupExact(AdapterInterface $adapter, string $x, $y): string
    {
        $y = $this->castToDatabaseValue($x, $y);

        if ($y instanceof Expression) {
            $sqlValue = $y->toSQL();
        } elseif ($y instanceof ToSqlInterface) {
            $sqlValue = '('.$y->toSQL().')';
        } elseif (false !== strpos((string) $y, 'SELECT')) {
            $sqlValue = '('.$y.')';
        } else {
            $sqlValue = $adapter->quoteValue($y);
        }

        return $this->eq(
            $this->getQuotedName($x),
            $sqlValue
        );
    }

    protected function lookupGte(AdapterInterface $adapter, string $x, $y): string
    {
        $y = $this->castToDatabaseValue($x, $y);

        return $this->gte(
            $this->getQuotedName($x),
            $adapter->quoteValue($y)
        );
    }

    protected function lookupGt(AdapterInterface $adapter, string $x, $y): string
    {
        $y = $this->castToDatabaseValue($x, $y);

        return $this->gt(
            $this->getQuotedName($x),
            $adapter->quoteValue($y)
        );
    }

    protected function lookupLte(AdapterInterface $adapter, string $x, $y): string
    {
        $y = $this->castToDatabaseValue($x, $y);

        return $this->lte(
            $this->getQuotedName($x),
            $adapter->quoteValue($y)
        );
    }

    protected function lookupLt(AdapterInterface $adapter, string $x, $y): string
    {
        $y = $this->castToDatabaseValue($x, $y);

        return $this->lt(
            $this->getQuotedName($x),
            $adapter->quoteValue($y)
        );
    }

    protected function lookupRange(AdapterInterface $adapter, string $x, $y): string
    {
        list($min, $max) = $y;

        return $this->between(
            $this->getQuotedName($x),
            [$adapter->quoteValue($min), $adapter->quoteValue($max)]
        );
    }

    protected function lookupIsnt(AdapterInterface $adapter, string $x, $y): string
    {
        /** @var $adapter \Mindy\QueryBuilder\BaseAdapter */
        if (in_array($adapter->getSqlType($y), ['TRUE', 'FALSE', 'NULL'])) {
            return $this->getQuotedName($x).' IS NOT '.$adapter->getSqlType($y);
        }

        return $this->neq(
            $this->getQuotedName($x),
            $adapter->quoteValue($y)
        );
    }

    protected function lookupIsnull(AdapterInterface $adapter, string $x, $y): string
    {
        if ($y) {
            return $this->isNull($this->getQuotedName($x));
        }

        return $this->isNotNull($this->getQuotedName($x));
    }

    protected function lookupContains(AdapterInterface $adapter, string $x, $y): string
    {
        if (is_bool($y)) {
            $y = (int) $y;
        }

        return $this->like(
            $this->getQuotedName($x),
            $adapter->quoteValue('%'.$y.'%')
        );
    }

    protected function lookupIcontains(AdapterInterface $adapter, string $x, $y): string
    {
        if (is_bool($y)) {
            $y = (int) $y;
        }

        return $this->like(
            'LOWER('.$this->getQuotedName($x).')',
            $adapter->quoteValue('%'.mb_strtolower((string) $y, 'UTF-8').'%')
        );
    }

    protected function lookupStartswith(AdapterInterface $adapter, string $x, $y): string
    {
        if (is_bool($y)) {
            $y = (int) $y;
        }

        return $this->like(
            $this->getQuotedName($x),
            $adapter->quoteValue((string) $y.'%')
        );
    }

    protected function lookupIstartswith(AdapterInterface $adapter, string $x, $y): string
    {
        if (is_bool($y)) {
            $y = (int) $y;
        }

        return $this->like(
            'LOWER('.$this->getQuotedName($x).')',
            $adapter->quoteValue(mb_strtolower((string) $y, 'UTF-8').'%')
        );
    }

    protected function lookupEndswith(AdapterInterface $adapter, string $x, $y): string
    {
        if (is_bool($y)) {
            $y = (int) $y;
        }

        return $this->like(
            $this->getQuotedName($x),
            $adapter->quoteValue('%'.(string) $y)
        );
    }

    protected function lookupIendswith(AdapterInterface $adapter, string $x, $y): string
    {
        if (is_bool($y)) {
            $y = (int) $y;
        }

        return $this->like(
            'LOWER('.$this->getQuotedName($x).')',
            $adapter->quoteValue('%'.mb_strtolower((string) $y, 'UTF-8'))
        );
    }

    protected function lookupIn(AdapterInterface $adapter, string $x, $y): string
    {
        if (is_array($y)) {
            $quotedValues = array_map(function ($item) use ($adapter) {
                return $adapter->quoteValue($item);
            }, $y);
            $sqlValue = implode(', ', $quotedValues);
        } elseif ($y instanceof ToSqlInterface) {
            $sqlValue = $y->toSQL();
        } else {
            $sqlValue = $adapter->quoteSql((string) $y);
        }

        return $this->in(
            $this->getQuotedName($x),
            $sqlValue
        );
    }

    protected function lookupRaw(AdapterInterface $adapter, string $x, $y): string
    {
        return sprintf(
            '%s %s',
            $this->getQuotedName($x),
            $adapter->quoteSql($y)
        );
    }
}
