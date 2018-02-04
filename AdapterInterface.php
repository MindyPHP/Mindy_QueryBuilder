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
     * @return string
     */
    public function getRandomOrder();

    /**
     * @param $value
     *
     * @return string
     */
    public function getBoolean($value = null);

    /**
     * @param null $value
     *
     * @return mixed
     */
    public function getTimestamp($value = null);

    /**
     * @param string $sql
     *
     * @return string
     */
    public function quoteSql(string $sql): string;

    /**
     * @param $value
     *
     * @return string
     */
    public function quoteValue($value);
}
