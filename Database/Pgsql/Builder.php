<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Database\Pgsql;

use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use Doctrine\DBAL\Types\Type;
use Mindy\QueryBuilder\AbstractBuilder;

class Builder extends AbstractBuilder
{
    protected function parseExact(string $x, $y): string
    {
        return $this->expr->eq($x, $this->expr->literal($y, Type::STRING));
    }

    protected function parseGte(string $x, $y): string
    {
        return $this->expr->gte(sprintf('CAST(%s AS integer)', $x), $this->expr->literal($y, Type::INTEGER));
    }

    protected function parseGt(string $x, $y): string
    {
        return $this->expr->gt(sprintf('CAST(%s AS integer)', $x), $this->expr->literal($y, Type::INTEGER));
    }

    protected function parseLte(string $x, $y): string
    {
        return $this->expr->lte(sprintf('CAST(%s AS integer)', $x), $this->expr->literal($y, Type::INTEGER));
    }

    protected function parseLt(string $x, $y): string
    {
        return $this->expr->lt(sprintf('CAST(%s AS integer)', $x), $this->expr->literal($y, Type::INTEGER));
    }

    protected function parseIcontains(string $x, $y): string
    {
        return $this->expr->like(
            sprintf('LOWER(%s::text)', $x),
            $this->expr->literal(mb_strtolower('%'.$y.'%', 'UTF-8'), Type::STRING)
        );
    }

    protected function parseContains(string $x, $y): string
    {
        return $this->expr->like(
            $x.'::text',
            $this->expr->literal('%'.$y.'%', Type::STRING)
        );
    }

    protected function parseIstartswith(string $x, $y): string
    {
        return $this->expr->like(
            sprintf('LOWER(%s::text)', $x),
            $this->expr->literal(mb_strtolower($y.'%', 'UTF-8'), Type::STRING)
        );
    }

    protected function parseStartswith(string $x, $y): string
    {
        return $this->expr->like(
            $x.'::text',
            $this->expr->literal($y.'%', Type::STRING)
        );
    }

    protected function parseIendswith(string $x, $y): string
    {
        return $this->expr->like(
            sprintf('LOWER(%s::text)', $x),
            $this->expr->literal(mb_strtolower('%'.$y, 'UTF-8'), Type::STRING)
        );
    }

    protected function parseEndswith(string $x, $y): string
    {
        return $this->expr->like(
            $x.'::text',
            $this->expr->literal('%'.$y, Type::STRING)
        );
    }

    protected function parseJson(string $x, $y): string
    {
        $result = [];
        foreach ($y as $field => $value) {
            list($name, $lookups) = $this->parse($field);
            $first = current($lookups);

            if ($this->isSupport($first)) {
                $method = $this->formatMethod($first);

                $result[] = call_user_func_array([$this, $method], [
                    sprintf("%s ->> '%s'", $x, $name),
                    $value,
                ]);
            }
        }

        return implode(' AND ', $result);
    }

    public function parseDay(string $x, $y): string
    {
        return $this->extractDay($x, $y);
    }

    public function parseMonth(string $x, $y): string
    {
        return $this->extractMonth($x, $y);
    }

    public function parseYear(string $x, $y): string
    {
        return $this->extractYear($x, $y);
    }

    public function parseMinute(string $x, $y): string
    {
        return $this->extractMinute($x, $y);
    }

    public function parseHour(string $x, $y): string
    {
        return $this->extractHour($x, $y);
    }

    public function parseSecond(string $x, $y): string
    {
        return $this->extractSecond($x, $y);
    }

    public function parseWeekday(string $x, $y): string
    {
        return $this->weekDay($x, $y);
    }

    public function parseRegex(string $x, $y): string
    {
        return $this->regex($x, $y);
    }

    public function parseIRegex(string $x, $y): string
    {
        return $this->iregex($x, $y);
    }

    public function parseIsnull(string $x, $y): string
    {
        if ($y) {
            return $this->expr->isNull($x);
        }

        return $this->expr->isNotNull($x);
    }

    public function parseIn(string $x, $y): string
    {
        return $this->expr->in($x, $y);
    }

    public function parseNotin(string $x, $y): string
    {
        return $this->expr->notIn($x, $y);
    }

    public function extractYear($x, $y)
    {
        return $this->expr->comparison(sprintf('EXTRACT(YEAR FROM %s::timestamp)', $x), ExpressionBuilder::EQ, $y);
    }

    public function extractSecond($x, $y)
    {
        return $this->expr->comparison(sprintf('EXTRACT(SECOND FROM %s::timestamp)', $x), ExpressionBuilder::EQ, $y);
    }

    public function extractMinute($x, $y)
    {
        return $this->expr->comparison(sprintf('EXTRACT(MINUTE FROM %s::timestamp)', $x), ExpressionBuilder::EQ, $y);
    }

    public function extractHour($x, $y)
    {
        return $this->expr->comparison(sprintf('EXTRACT(HOUR FROM %s::timestamp)', $x), ExpressionBuilder::EQ, $y);
    }

    public function extractDay($x, $y)
    {
        return $this->expr->comparison(sprintf('EXTRACT(DAY FROM %s::timestamp)', $x), ExpressionBuilder::EQ, $y);
    }

    public function extractMonth($x, $y)
    {
        return $this->expr->comparison(sprintf('EXTRACT(MONTH FROM %s::timestamp)', $x), ExpressionBuilder::EQ, $y);
    }

    public function weekDay($x, $y)
    {
        $y = (int) $y;
        if ($y < 1 || $y > 7) {
            throw new \LogicException('Incorrect day of week. Available range 0-6 where 0 - monday.');
        }

        /*
        EXTRACT('dow' FROM timestamp)   0-6    Sunday=0
        TO_CHAR(timestamp, 'D')         1-7    Sunday=1
        */
        if (7 === $y) {
            $y = 1;
        } else {
            $y += 1;
        }

        return $this->expr->comparison(sprintf('EXTRACT(DOW FROM %s::timestamp)', $x), ExpressionBuilder::EQ, $y);
    }

    public function regex($x, $y)
    {
        return $this->expr->comparison($x, '~', $y);
    }

    public function iregex($x, $y)
    {
        return $this->expr->comparison($x, '~*', $y);
    }
}
