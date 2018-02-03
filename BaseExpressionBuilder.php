<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder;

class BaseExpressionBuilder extends ExpressionBuilder implements LookupCollectionInterface
{
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
        /* @var $adapter \Mindy\QueryBuilder\BaseAdapter */
        if ($y instanceof \DateTime) {
            $y = $adapter->getDateTime($y);
        }

        if ($y instanceof Expression) {
            $sqlValue = $y->toSQL();
        } elseif ($y instanceof QueryBuilder) {
            $sqlValue = '('.$y->toSQL().')';
        } elseif (false !== strpos((string) $y, 'SELECT')) {
            $sqlValue = '('.$y.')';
        } else {
            $sqlValue = $adapter->quoteValue($y);
        }

        return $this->eq(
            $adapter->quoteColumn($x),
            $sqlValue
        );
    }

    protected function lookupGte(AdapterInterface $adapter, string $x, $y): string
    {
        if ($y instanceof \DateTime) {
            $y = $adapter->getDateTime($y);
        }

        return $this->gte(
            $adapter->quoteColumn($x),
            $adapter->quoteValue($y)
        );
    }

    protected function lookupGt(AdapterInterface $adapter, string $x, $y): string
    {
        if ($y instanceof \DateTime) {
            $y = $adapter->getDateTime($y);
        }

        return $this->gt(
            $adapter->quoteColumn($x),
            $adapter->quoteValue($y)
        );
    }

    protected function lookupLte(AdapterInterface $adapter, string $x, $y): string
    {
        if ($y instanceof \DateTime) {
            $y = $adapter->getDateTime($y);
        }

        return $this->lte(
            $adapter->quoteColumn($x),
            $adapter->quoteValue($y)
        );
    }

    protected function lookupLt(AdapterInterface $adapter, string $x, $y): string
    {
        if ($y instanceof \DateTime) {
            $y = $adapter->getDateTime($y);
        }

        return $this->lt(
            $adapter->quoteColumn($x),
            $adapter->quoteValue($y)
        );
    }

    protected function lookupRange(AdapterInterface $adapter, string $x, $y): string
    {
        list($min, $max) = $y;

        return $this->between($adapter->quoteColumn($x), [
            $adapter->quoteValue($min),
            $adapter->quoteValue($max),
        ]);
    }

    protected function lookupIsnt(AdapterInterface $adapter, string $x, $y): string
    {
        /** @var $adapter \Mindy\QueryBuilder\BaseAdapter */
        if (in_array($adapter->getSqlType($y), ['TRUE', 'FALSE', 'NULL'])) {
            return $adapter->quoteColumn($x).' IS NOT '.$adapter->getSqlType($y);
        }

        return $this->neq(
            $adapter->quoteColumn($x),
            $adapter->quoteValue($y)
        );
    }

    protected function lookupIsnull(AdapterInterface $adapter, string $x, $y): string
    {
        if ($y) {
            return $this->isNull($adapter->quoteColumn($x));
        }

        return $this->isNotNull($adapter->quoteColumn($x));
    }

    protected function lookupContains(AdapterInterface $adapter, string $x, $y): string
    {
        if (is_bool($y)) {
            $y = (int) $y;
        }

        return $this->like(
            $adapter->quoteColumn($x),
            $adapter->quoteValue('%'.$y.'%')
        );
    }

    protected function lookupIcontains(AdapterInterface $adapter, string $x, $y): string
    {
        if (is_bool($y)) {
            $y = (int) $y;
        }

        return $this->like(
            'LOWER('.$adapter->quoteColumn($x).')',
            $adapter->quoteValue('%'.mb_strtolower((string) $y, 'UTF-8').'%')
        );
    }

    protected function lookupStartswith(AdapterInterface $adapter, string $x, $y): string
    {
        if (is_bool($y)) {
            $y = (int) $y;
        }

        return $this->like(
            $adapter->quoteColumn($x),
            $adapter->quoteValue((string) $y.'%')
        );
    }

    protected function lookupIstartswith(AdapterInterface $adapter, string $x, $y): string
    {
        if (is_bool($y)) {
            $y = (int) $y;
        }

        return $this->like(
            'LOWER('.$adapter->quoteColumn($x).')',
            $adapter->quoteValue(mb_strtolower((string) $y, 'UTF-8').'%')
        );
    }

    protected function lookupEndswith(AdapterInterface $adapter, string $x, $y): string
    {
        if (is_bool($y)) {
            $y = (int) $y;
        }

        return $this->like(
            $adapter->quoteColumn($x),
            $adapter->quoteValue('%'.(string) $y)
        );
    }

    protected function lookupIendswith(AdapterInterface $adapter, string $x, $y): string
    {
        if (is_bool($y)) {
            $y = (int) $y;
        }

        return $this->like(
            'LOWER('.$adapter->quoteColumn($x).')',
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
        } elseif ($y instanceof QueryBuilder) {
            $sqlValue = $y->toSQL();
        } else {
            $sqlValue = $adapter->quoteSql($y);
        }

        return $this->in(
            $adapter->quoteColumn($x),
            $sqlValue
        );
    }

    protected function lookupRaw(AdapterInterface $adapter, string $x, $y): string
    {
        return sprintf(
            '%s %s',
            $adapter->quoteColumn($x),
            $adapter->quoteSql($y)
        );
    }
}
