<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\LookupBuilder;

use Exception;
use Mindy\QueryBuilder\QueryBuilder;

class LookupBuilder extends Base
{
    public function parseLookup(QueryBuilder $queryBuilder, $rawLookup, $value)
    {
        if (substr_count($rawLookup, $this->separator) > 1) {
            if (empty($this->callback)) {
                throw new Exception('Unknown lookup: '.$rawLookup);
            }

            return $this->runCallback($queryBuilder, explode($this->separator, $rawLookup), $value);
        }

        if (0 == substr_count($rawLookup, $this->separator)) {
            $rawLookup = $this->fetchColumnName($rawLookup);

            return [$this->default, $rawLookup, $value];
        }
        $lookupNodes = explode($this->separator, $rawLookup);
        if ($this->hasLookup(end($lookupNodes)) && 1 == substr_count($rawLookup, $this->separator)) {
            list($column, $lookup) = explode($this->separator, $rawLookup);
            if (false == $this->hasLookup($lookup)) {
                throw new Exception('Unknown lookup:'.$lookup);
            }
            $column = $this->fetchColumnName($column);

            return [$lookup, $column, $value];
        }

        return $this->runCallback($queryBuilder, $lookupNodes, $value);
    }

    public function buildJoin(QueryBuilder $queryBuilder, $lookup)
    {
        if (substr_count($lookup, $this->getSeparator()) > 0) {
            return $this->runJoinCallback($queryBuilder, explode($this->getSeparator(), $lookup));
        }

        return false;
    }

    public function parse(QueryBuilder $queryBuilder, array $where)
    {
        $conditions = [];
        foreach ($where as $lookup => $value) {
            /*
             * Parse new QOr([[username => 1], [username => 2]])
             */
            if (is_numeric($lookup) && is_array($value)) {
                $lookup = key($value);
                $value = array_shift($value);
            }
            $conditions[] = $this->parseLookup($queryBuilder, $lookup, $value);
        }

        return $conditions;
    }
}
