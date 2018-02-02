<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Database\Sqlite;

use Doctrine\DBAL\Types\Type;
use Mindy\QueryBuilder\AbstractBuilder;

/**
 * Class Builder
 */
class Builder extends AbstractBuilder
{
    protected function parseExact(string $column, $parameters): string
    {
        return $this->expr->eq($column, $this->expr->literal($parameters, Type::STRING));
    }

    protected function parseGte(string $column, $parameters): string
    {
        return $this->expr->gte($column, $this->expr->literal($parameters, Type::INTEGER));
    }

    protected function parseGt(string $column, $parameters): string
    {
        return $this->expr->gt($column, $this->expr->literal($parameters, Type::INTEGER));
    }

    protected function parseLte(string $column, $parameters): string
    {
        return $this->expr->lte($column, $this->expr->literal($parameters, Type::INTEGER));
    }

    protected function parseLt(string $column, $parameters): string
    {
        return $this->expr->lt($column, $this->expr->literal($parameters, Type::INTEGER));
    }

    protected function parseIcontains(string $column, $parameters): string
    {
        return $this->expr->like(
            sprintf('LOWER(%s)', $column),
            '%'.$this->expr->literal(mb_strtolower($parameters, 'UTF-8'), Type::STRING).'%'
        );
    }

    protected function parseContains(string $column, $parameters): string
    {
        return $this->expr->like(
            $column,
            '%'.$this->expr->literal($parameters, Type::STRING).'%'
        );
    }

    protected function parseIstartswith(string $column, $parameters): string
    {
        return $this->expr->like(
            sprintf('LOWER(%s)', $column),
            $this->expr->literal(mb_strtolower($parameters, 'UTF-8'), Type::STRING).'%'
        );
    }

    protected function parseStartswith(string $column, $parameters): string
    {
        return $this->expr->like(
            $column,
            $this->expr->literal($parameters, Type::STRING).'%'
        );
    }

    protected function parseIendswith(string $column, $parameters): string
    {
        return $this->expr->like(
            sprintf('LOWER(%s)', $column),
            '%'.$this->expr->literal(mb_strtolower($parameters, 'UTF-8'), Type::STRING)
        );
    }

    protected function parseEndswith(string $column, $parameters): string
    {
        return $this->expr->like(
            $column,
            '%'.$this->expr->literal($parameters, Type::STRING)
        );
    }

    protected function parseJson(string $column, $parameters): string
    {
        $result = [];
        foreach ($parameters as $field => $value) {
            list($name, $lookups) = $this->parse($field);
            $first = current($lookups);

            if ($this->isSupport($first)) {
                $method = $this->formatMethod($first);

                $result[] = call_user_func_array([$this, $method], [
                    sprintf('JSON_EXTRACT(%s, $.%s)', $column, $name),
                    $value,
                ]);
            }
        }

        return implode(' AND ', $result);
    }

    public function parseDay(string $column, $parameters): string
    {
        return $this->expr->extractDay($column, $parameters);
    }

    public function parseMonth(string $column, $parameters): string
    {
        return $this->expr->extractMonth($column, $parameters);
    }

    public function parseYear(string $column, $parameters): string
    {
        return $this->expr->extractYear($column, $parameters);
    }

    public function parseMinute(string $column, $parameters): string
    {
        return $this->expr->extractMinute($column, $parameters);
    }

    public function parseHour(string $column, $parameters): string
    {
        return $this->expr->extractHour($column, $parameters);
    }

    public function parseSecond(string $column, $parameters): string
    {
        return $this->expr->extractSecond($column, $parameters);
    }

    public function parseWeekday(string $column, $parameters): string
    {
        return $this->expr->weekDay($column, $parameters);
    }

    public function parseRegex(string $column, $parameters): string
    {
        return $this->expr->regex($column, $parameters);
    }

    public function parseIRegex(string $column, $parameters): string
    {
        return $this->expr->iregex($column, $parameters);
    }

    public function parseIsnull(string $column, $parameters): string
    {
        if ($parameters) {
            return $this->expr->isNull($column);
        }

        return $this->expr->isNotNull($column);
    }

    public function parseIn(string $column, $parameters): string
    {
        return $this->expr->in($column, $parameters);
    }

    public function parseNotin(string $column, $parameters): string
    {
        return $this->expr->notIn($column, $parameters);
    }

    public function extractYear($x, $y)
    {
        return $this->expr->comparison('strftime(\'%Y\', '.$x.')', ExpressionBuilder::EQ, $y);
    }

    public function extractSecond($x, $y)
    {
        return $this->expr->comparison('strftime(\'%S\', '.$x.')', ExpressionBuilder::EQ, $y);
    }

    public function extractMinute($x, $y)
    {
        return $this->expr->comparison('strftime(\'%M\', '.$x.')', ExpressionBuilder::EQ, $y);
    }

    public function extractHour($x, $y)
    {
        return $this->expr->comparison('strftime(\'%H\', '.$x.')', ExpressionBuilder::EQ, $y);
    }

    public function extractDay($x, $y)
    {
        return $this->expr->comparison('strftime(\'%d\', '.$x.')', ExpressionBuilder::EQ, $y);
    }

    public function extractMonth($x, $y)
    {
        $y = (int) $y;
        if (1 == strlen((string) $y)) {
            $y = '0'.(string) $y;
        }
        return $this->expr->comparison('strftime(\'%m\', '.$x.')', ExpressionBuilder::EQ, $y);
    }

    public function weekDay($x, $y)
    {
        $y = (int) $y;
        if ($y < 1 || $y > 7) {
            throw new \LogicException('Incorrect day of week. Available range 0-6 where 0 - monday.');
        }

        /*
         * %w - day of week 0-6 with Sunday==0
         */
        if (7 === $y) {
            $y = 1;
        } else {
            $y += 1;
        }

        return $this->expr->comparison('strftime(\'%w\', '.$x.')', ExpressionBuilder::EQ, $y);
    }

    public function regex($x, $y)
    {
        return $x.' REGEXP /'.$y.'/';
    }

    public function iregex($x, $y)
    {
        return $x.' REGEXP /'.$y.'/i';
    }
}
