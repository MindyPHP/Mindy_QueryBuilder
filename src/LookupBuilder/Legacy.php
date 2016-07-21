<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 20/06/16
 * Time: 10:39
 */

namespace Mindy\QueryBuilder\LookupBuilder;

use Exception;

class Legacy extends Base
{
    protected function parseLookup($rawLookup, $value)
    {
        if (substr_count($rawLookup, $this->separator) > 1) {
            if (empty($this->callback)) {
                throw new Exception('Unknown lookup: ' . $rawLookup);
            } else {
                $this->callback->setLookupBuilder($this);
                $this->callback->setQueryBuilder($this->qb);
                return $this->callback->fetch(explode($this->separator, $rawLookup), $value);
            }
        } else if (substr_count($rawLookup, $this->separator) == 1) {
            list($column, $lookup) = explode($this->separator, $rawLookup);
            if ($this->hasLookup($lookup) == false) {
                throw new Exception('Unknown lookup:' . $lookup);
            }
            return [$lookup, $column, $value];
        } else {
            return [$this->default, $rawLookup, $value];
        }
    }

    public function parse(array $where)
    {
        $conditions = [];
        foreach ($where as $lookup => $value) {
            if (is_numeric($lookup)) {
                $lookup = key($value);
                $value = array_shift($value);
            }
            $conditions[] = $this->parseLookup($lookup, $value);
        }
        return $conditions;
    }
}