<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Tests;

use Exception;
use Mindy\QueryBuilder\AdapterInterface;
use Mindy\QueryBuilder\LookupCollectionInterface;

class LookupLibrary implements LookupCollectionInterface
{
    /**
     * {@inheritdoc}
     */
    public function has(string $lookup): bool
    {
        return 'foo' === $lookup;
    }

    /**
     * @param AdapterInterface $adapter
     * @param $lookup
     * @param $x
     * @param $y
     *
     * @throws Exception
     *
     * @return string
     */
    public function process(AdapterInterface $adapter, $lookup, $x, $y)
    {
        switch ($lookup) {
            case 'foo':
                return $adapter->getQuotedName($x).' ??? '.$y;

            default:
                throw new Exception('Unknown lookup: '.$lookup);
        }
    }
}

class CustomLookupTest extends BaseTest
{
    public function testCustom()
    {
        $qb = $this->getQueryBuilder();
        $qb->addLookupCollection(new LookupLibrary());
        list($lookup, $column, $value) = $qb->getLookupBuilder()->parseLookup($qb, 'name__foo', 1);
        $sql = $qb->getLookupBuilder()->runLookup($qb->getAdapter(), $lookup, $column, $value);
        $this->assertEquals($sql, 'name ??? 1');
    }
}
