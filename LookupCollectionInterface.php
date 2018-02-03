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
     * @param string $lookup
     *
     * @return bool
     */
    public function has(string $lookup): bool;

    /**
     * @param $lookup
     * @param $x
     * @param $y
     *
     * @return mixed
     */
    public function process(AdapterInterface $adapter, $lookup, $x, $y);
}
