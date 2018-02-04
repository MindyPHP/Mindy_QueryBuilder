<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder;

/**
 * Interface LookupBuilderInterface.
 */
interface LookupBuilderInterface
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $lookup
     * @param $value
     *
     * @return array
     */
    public function parseLookup(QueryBuilder $queryBuilder, $lookup, $value);

    /**
     * @param QueryBuilder $queryBuilder
     * @param array        $where
     *
     * @return mixed
     */
    public function parse(QueryBuilder $queryBuilder, array $where);

    /**
     * @param \Closure $callback
     *
     * @return mixed
     */
    public function setCallback($callback);

    /**
     * @param LookupCollectionInterface $lookupCollection
     *
     * @return $this
     */
    public function addLookupCollection(LookupCollectionInterface $lookupCollection);

    /**
     * @param AdapterInterface $adapter
     * @param $lookup
     * @param $column
     * @param $value
     *
     * @return mixed
     */
    public function runLookup(AdapterInterface $adapter, $lookup, $column, $value);

    /**
     * @param $callback
     *
     * @return $this
     */
    public function setJoinCallback($callback);
}
