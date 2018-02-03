<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder;

use Doctrine\DBAL\Driver;

/**
 * Trait LookupBuilderAwareTrait
 *
 * @method Driver getDriver()
 */
trait LookupBuilderAwareTrait
{
    protected $lookupBuilder;

    public function getLookupBuilder()
    {
        if (null === $this->lookupBuilder) {
            $this->lookupBuilder = $this->createLookupBuilder();
        }

        return $this->lookupBuilder;
    }

    protected function createLookupBuilder()
    {
        switch ($this->getDriver()->getName()) {
            case 'pdo_sqlite':
                return new Database\Sqlite\Builder($this);

            case 'pdo_mysql':
                return new Database\Mysql\Builder($this);

            case 'pdo_pgsql':
                return new Database\Pgsql\Builder($this);
        }

        return null;
    }
}
