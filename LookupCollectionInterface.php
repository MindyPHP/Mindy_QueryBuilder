<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder;

interface LookupCollectionInterface
{
    /**
     * @param $lookup
     *
     * @return bool
     */
    public function has($lookup);

    /**
     * @param $lookup
     * @param $column
     * @param $value
     *
     * @return mixed
     */
    public function process(AdapterInterface $adapter, $lookup, $column, $value);
}
