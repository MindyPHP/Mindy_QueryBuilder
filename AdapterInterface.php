<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder;

interface AdapterInterface
{
    /**
     * @param $column
     *
     * @return string
     */
    public function quoteColumn($column);

    /**
     * @param $value
     *
     * @return string
     */
    public function quoteValue($value);

    /**
     * @param $tableName
     *
     * @return string
     */
    public function quoteTableName($tableName);
}
