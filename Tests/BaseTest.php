<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Tests;

use Mindy\QueryBuilder\QueryBuilder;

abstract class BaseTest extends ConnectionAwareTest
{
    /**
     * @throws \Exception
     *
     * @return \Mindy\QueryBuilder\BaseAdapter
     */
    protected function getAdapter()
    {
        return $this->getQueryBuilder()->getAdapter();
    }

    /**
     * @throws \Exception
     *
     * @return QueryBuilder
     */
    protected function getQueryBuilder()
    {
        return QueryBuilder::getInstance($this->connection);
    }
}
