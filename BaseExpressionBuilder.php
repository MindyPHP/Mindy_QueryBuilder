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
     * BaseExpressionBuilder constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
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
     * @param $y
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return mixed|null
     */
    protected function castToDatabaseValue($y)
    {
        if ($y instanceof \DateTime) {
            return $this->castToDateTime($y);
        } else if (gettype($y) === 'boolean') {
            return $this->castToType($y, Type::BOOLEAN);
        } else if (is_numeric($y)) {
            return $this->castToType($y, Type::INTEGER);
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
        $y = $this->castToDatabaseValue($y);

        if ($y instanceof Expression) {
            $sqlValue = $y->toSQL();
        } elseif ($y instanceof ToSqlInterface) {
            $sqlValue = '('.$y->toSQL().')';
        } elseif (false !== strpos((string) $y, 'SELECT')) {
            $sqlValue = '('.$y.')';
        } else {
            $sqlValue = $this->literal($y);
        }

        return $this->eq(
            $this->getQuotedName($x),
            $sqlValue
        );
    }

    protected function lookupGte(AdapterInterface $adapter, string $x, $y): string
    {
        $y = $this->castToDatabaseValue($y);

        return $this->gte(
            $this->getQuotedName($x),
            $this->literal($y)
        );
    }

    protected function lookupGt(AdapterInterface $adapter, string $x, $y): string
    {
        $y = $this->castToDatabaseValue($y);

        return $this->gt(
            $this->getQuotedName($x),
            $this->literal($y)
        );
    }

    protected function lookupLte(AdapterInterface $adapter, string $x, $y): string
    {
        $y = $this->castToDatabaseValue($y);

        return $this->lte(
            $this->getQuotedName($x),
            $this->literal($y)
        );
    }

    protected function lookupLt(AdapterInterface $adapter, string $x, $y): string
    {
        $y = $this->castToDatabaseValue($y);

        return $this->lt(
            $this->getQuotedName($x),
            $this->literal($y)
        );
    }

    protected function lookupRange(AdapterInterface $adapter, string $x, $y): string
    {
        list($min, $max) = $y;
        $minValue = $this->castToDatabaseValue($min);
        $maxValue = $this->castToDatabaseValue($max);

        return $this->between(
            $this->getQuotedName($x),
            [
                $this->literal($minValue),
                $this->literal($maxValue)
            ]
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
            $this->literal($y)
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
            $this->literal('%'.$y.'%')
        );
    }

    protected function lookupIcontains(AdapterInterface $adapter, string $x, $y): string
    {
        if (is_bool($y)) {
            $y = (int) $y;
        }

        return $this->like(
            'LOWER('.$this->getQuotedName($x).')',
            $this->literal('%'.mb_strtolower((string) $y, 'UTF-8').'%')
        );
    }

    protected function lookupStartswith(AdapterInterface $adapter, string $x, $y): string
    {
        if (is_bool($y)) {
            $y = (int) $y;
        }

        return $this->like(
            $this->getQuotedName($x),
            $this->literal((string) $y.'%')
        );
    }

    protected function lookupIstartswith(AdapterInterface $adapter, string $x, $y): string
    {
        if (is_bool($y)) {
            $y = (int) $y;
        }

        return $this->like(
            'LOWER('.$this->getQuotedName($x).')',
            $this->literal(mb_strtolower((string) $y, 'UTF-8').'%')
        );
    }

    protected function lookupEndswith(AdapterInterface $adapter, string $x, $y): string
    {
        if (is_bool($y)) {
            $y = (int) $y;
        }

        return $this->like(
            $this->getQuotedName($x),
            $this->literal('%'.(string) $y)
        );
    }

    protected function lookupIendswith(AdapterInterface $adapter, string $x, $y): string
    {
        if (is_bool($y)) {
            $y = (int) $y;
        }

        return $this->like(
            'LOWER('.$this->getQuotedName($x).')',
            $this->literal('%'.mb_strtolower((string) $y, 'UTF-8'))
        );
    }

    protected function lookupIn(AdapterInterface $adapter, string $x, $y): string
    {
        if (is_array($y)) {
            $quotedValues = array_map(function ($item) use ($adapter) {
                return $this->literal($item);
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

    /**
     * Quotes a given input parameter.
     *
     * @param mixed       $input The parameter to be quoted.
     * @param string|null $type  The type of the parameter.
     *
     * @return string
     */
    public function literal($input, $type = null)
    {
        // TODO remove
        if (!is_string($input)) {
            return $input;
        }
        // TODO remove

        return $this->connection->quote($input, $type);
    }
}
