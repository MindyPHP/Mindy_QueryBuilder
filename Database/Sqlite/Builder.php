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
    protected function parseExact(string $x, $y): string
    {
        if (is_numeric($y)) {
            return $this->expr->eq($x, $this->expr->literal($y, Type::INTEGER));
        }

        return $this->expr->eq($x, $this->expr->literal($y, Type::STRING));
    }

    protected function parseGte(string $x, $y): string
    {
        return $this->expr->gte($x, $this->expr->literal($y, Type::INTEGER));
    }

    protected function parseGt(string $x, $y): string
    {
        return $this->expr->gt($x, $this->expr->literal($y, Type::INTEGER));
    }

    protected function parseLte(string $x, $y): string
    {
        return $this->expr->lte($x, $this->expr->literal($y, Type::INTEGER));
    }

    protected function parseLt(string $x, $y): string
    {
        return $this->expr->lt($x, $this->expr->literal($y, Type::INTEGER));
    }

    protected function parseIcontains(string $x, $y): string
    {
        return $this->expr->like(
            sprintf('LOWER(%s)', $x),
            $this->expr->literal(mb_strtolower('%'.$y.'%', 'UTF-8'), Type::STRING)
        );
    }

    protected function parseContains(string $x, $y): string
    {
        return $this->expr->like(
            $x,
            $this->expr->literal('%'.$y.'%', Type::STRING)
        );
    }

    protected function parseIstartswith(string $x, $y): string
    {
        return $this->expr->like(
            sprintf('LOWER(%s)', $x),
            $this->expr->literal(mb_strtolower($y.'%', 'UTF-8'), Type::STRING)
        );
    }

    protected function parseStartswith(string $x, $y): string
    {
        return $this->expr->like(
            $x,
            $this->expr->literal($y.'%', Type::STRING)
        );
    }

    protected function parseIendswith(string $x, $y): string
    {
        return $this->expr->like(
            sprintf('LOWER(%s)', $x),
            $this->expr->literal(mb_strtolower('%'.$y, 'UTF-8'), Type::STRING)
        );
    }

    protected function parseEndswith(string $x, $y): string
    {
        return $this->expr->like(
            $x,
            $this->expr->literal('%'.$y, Type::STRING)
        );
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
        return $this->expr->eq('strftime(\'%Y\', '.$x.')', $y);
    }

    public function extractSecond($x, $y)
    {
        return $this->expr->eq('strftime(\'%S\', '.$x.')', $y);
    }

    public function extractMinute($x, $y)
    {
        return $this->expr->eq('strftime(\'%M\', '.$x.')', $y);
    }

    public function extractHour($x, $y)
    {
        return $this->expr->eq('strftime(\'%H\', '.$x.')', $y);
    }

    public function extractDay($x, $y)
    {
        return $this->expr->eq('strftime(\'%d\', '.$x.')', $y);
    }

    public function extractMonth($x, $y)
    {
        $y = (int) $y;
        if (1 == strlen((string) $y)) {
            $y = '0'.(string) $y;
        }

        return $this->expr->eq('strftime(\'%m\', '.$x.')', $y);
    }

    public function weekDay($x, $y)
    {
        return $this->expr->eq('strftime(\'%w\', '.$x.')', WeekDayFormat::format((int) $y));
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
