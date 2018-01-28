<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder as DbalQueryBuilder;

class NewQueryBuilder
{
    /**
     * @var Connection
     */
    protected $connection;
    /**
     * @var DbalQueryBuilder
     */
    protected $queryBuilder;

    /**
     * NewQueryBuilder constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return DbalQueryBuilder
     */
    protected function createQueryBuilder(): DbalQueryBuilder
    {
        return new DbalQueryBuilder($this->connection);
    }

    /**
     * @return DbalQueryBuilder
     */
    protected function getQueryBuilder(): DbalQueryBuilder
    {
        if (null === $this->queryBuilder) {
            $this->queryBuilder = $this->createQueryBuilder();
        }

        return $this->queryBuilder;
    }

    /**
     * @return string
     */
    public function getSQL(): string
    {
        return $this->getQueryBuilder()->getSQL();
    }

    /**
     * @param null $select
     *
     * @return $this
     */
    public function select($select = null)
    {
        $this
            ->getQueryBuilder()
            ->select($select);

        return $this;
    }

    /**
     * @param string      $tableName
     * @param string|null $alias
     *
     * @return $this
     */
    public function from(string $tableName, string $alias = null)
    {
        $this
            ->getQueryBuilder()
            ->from($tableName, $alias);

        return $this;
    }
}
