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

trait ConnectionAwareTrait
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @param Connection $connection
     *
     * @return $this
     */
    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }
}
