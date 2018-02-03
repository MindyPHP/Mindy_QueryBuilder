<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder;

class Expression implements ToSqlInterface
{
    /**
     * @var string
     */
    private $expression = '';

    /**
     * Expression constructor.
     * @param $expression
     */
    public function __construct($expression)
    {
        $this->expression = $expression;
    }

    /**
     * {@inheritdoc}
     */
    public function toSQL(): string
    {
        return $this->expression;
    }
}
