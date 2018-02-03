<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder;

use Doctrine\DBAL\Query\Expression\CompositeExpression;

/**
 * Class ExpressionBuilder
 */
class ExpressionBuilder
{
    const EQ = '=';
    const NEQ = '<>';
    const LT = '<';
    const LTE = '<=';
    const GT = '>';
    const GTE = '>=';

    /**
     * Creates a conjunction of the given boolean expressions.
     *
     * Example:
     *
     *     [php]
     *     // (u.type = ?) AND (u.role = ?)
     *     $expr->andX('u.type = ?', 'u.role = ?'));
     *
     * @param mixed $x Optional clause. Defaults = null, but requires
     *                 at least one defined when converting to string.
     *
     * @return \Doctrine\DBAL\Query\Expression\CompositeExpression
     */
    public function andX($x = null): CompositeExpression
    {
        return new CompositeExpression(CompositeExpression::TYPE_AND, func_get_args());
    }

    /**
     * Creates a disjunction of the given boolean expressions.
     *
     * Example:
     *
     *     [php]
     *     // (u.type = ?) OR (u.role = ?)
     *     $qb->where($qb->expr()->orX('u.type = ?', 'u.role = ?'));
     *
     * @param mixed $x Optional clause. Defaults = null, but requires
     *                 at least one defined when converting to string.
     *
     * @return \Doctrine\DBAL\Query\Expression\CompositeExpression
     */
    public function orX($x = null): CompositeExpression
    {
        return new CompositeExpression(CompositeExpression::TYPE_OR, func_get_args());
    }

    /**
     * Creates a comparison expression.
     *
     * @param mixed  $x        the left expression
     * @param string $operator one of the ExpressionBuilder::* constants
     * @param mixed  $y        the right expression
     *
     * @return string
     */
    public function comparison($x, $operator, $y): string
    {
        return $x.' '.$operator.' '.$y;
    }

    /**
     * Creates an equality comparison expression with the given arguments.
     *
     * First argument is considered the left expression and the second is the right expression.
     * When converted to string, it will generated a <left expr> = <right expr>. Example:
     *
     *     [php]
     *     // u.id = ?
     *     $expr->eq('u.id', '?');
     *
     * @param mixed $x the left expression
     * @param mixed $y the right expression
     *
     * @return string
     */
    public function eq($x, $y): string
    {
        return $this->comparison($x, self::EQ, $y);
    }

    /**
     * Creates a non equality comparison expression with the given arguments.
     * First argument is considered the left expression and the second is the right expression.
     * When converted to string, it will generated a <left expr> <> <right expr>. Example:
     *
     *     [php]
     *     // u.id <> 1
     *     $q->where($q->expr()->neq('u.id', '1'));
     *
     * @param mixed $x the left expression
     * @param mixed $y the right expression
     *
     * @return string
     */
    public function neq($x, $y): string
    {
        return $this->comparison($x, self::NEQ, $y);
    }

    /**
     * Creates a lower-than comparison expression with the given arguments.
     * First argument is considered the left expression and the second is the right expression.
     * When converted to string, it will generated a <left expr> < <right expr>. Example:
     *
     *     [php]
     *     // u.id < ?
     *     $q->where($q->expr()->lt('u.id', '?'));
     *
     * @param mixed $x the left expression
     * @param mixed $y the right expression
     *
     * @return string
     */
    public function lt($x, $y): string
    {
        return $this->comparison($x, self::LT, $y);
    }

    /**
     * Creates a lower-than-equal comparison expression with the given arguments.
     * First argument is considered the left expression and the second is the right expression.
     * When converted to string, it will generated a <left expr> <= <right expr>. Example:
     *
     *     [php]
     *     // u.id <= ?
     *     $q->where($q->expr()->lte('u.id', '?'));
     *
     * @param mixed $x the left expression
     * @param mixed $y the right expression
     *
     * @return string
     */
    public function lte($x, $y): string
    {
        return $this->comparison($x, self::LTE, $y);
    }

    /**
     * Creates a greater-than comparison expression with the given arguments.
     * First argument is considered the left expression and the second is the right expression.
     * When converted to string, it will generated a <left expr> > <right expr>. Example:
     *
     *     [php]
     *     // u.id > ?
     *     $q->where($q->expr()->gt('u.id', '?'));
     *
     * @param mixed $x the left expression
     * @param mixed $y the right expression
     *
     * @return string
     */
    public function gt($x, $y): string
    {
        return $this->comparison($x, self::GT, $y);
    }

    /**
     * Creates a greater-than-equal comparison expression with the given arguments.
     * First argument is considered the left expression and the second is the right expression.
     * When converted to string, it will generated a <left expr> >= <right expr>. Example:
     *
     *     [php]
     *     // u.id >= ?
     *     $q->where($q->expr()->gte('u.id', '?'));
     *
     * @param mixed $x the left expression
     * @param mixed $y the right expression
     *
     * @return string
     */
    public function gte($x, $y): string
    {
        return $this->comparison($x, self::GTE, $y);
    }

    /**
     * Creates an IS NULL expression with the given arguments.
     *
     * @param string $x the field in string format to be restricted by IS NULL
     *
     * @return string
     */
    public function isNull($x): string
    {
        return $x.' IS NULL';
    }

    /**
     * Creates an IS NOT NULL expression with the given arguments.
     *
     * @param string $x the field in string format to be restricted by IS NOT NULL
     *
     * @return string
     */
    public function isNotNull($x): string
    {
        return $x.' IS NOT NULL';
    }

    /**
     * Creates a LIKE() comparison expression with the given arguments.
     *
     * @param string $x field in string format to be inspected by LIKE() comparison
     * @param mixed  $y argument to be used in LIKE() comparison
     *
     * @return string
     */
    public function like($x, $y): string
    {
        return $this->comparison($x, 'LIKE', $y);
    }

    /**
     * Creates a NOT LIKE() comparison expression with the given arguments.
     *
     * @param string $x field in string format to be inspected by NOT LIKE() comparison
     * @param mixed  $y argument to be used in NOT LIKE() comparison
     *
     * @return string
     */
    public function notLike($x, $y): string
    {
        return $this->comparison($x, 'NOT LIKE', $y);
    }

    /**
     * Creates a IN () comparison expression with the given arguments.
     *
     * @param string       $x the field in string format to be inspected by IN() comparison
     * @param string|array $y the placeholder or the array of values to be used by IN() comparison
     *
     * @return string
     */
    public function in($x, $y): string
    {
        return $this->comparison($x, 'IN', '('.implode(', ', (array) $y).')');
    }

    /**
     * Creates a NOT IN () comparison expression with the given arguments.
     *
     * @param string       $x the field in string format to be inspected by NOT IN() comparison
     * @param string|array $y the placeholder or the array of values to be used by NOT IN() comparison
     *
     * @return string
     */
    public function notIn($x, $y): string
    {
        return $this->comparison($x, 'NOT IN', '('.implode(', ', (array) $y).')');
    }

    /**
     * Creates a BETWEEN () comparison expression with the given arguments.
     *
     * @param string       $x the field in string format to be inspected by BETWEEN comparison
     * @param string|array $y the placeholder or the array of values to be used by BETWEEN comparison
     *
     * @return string
     */
    public function between($x, $y): string
    {
        return $this->comparison($x, 'BETWEEN', implode(' AND ', $y));
    }
}
