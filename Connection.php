<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder;

use Doctrine\DBAL\Connection as BaseConnection;
use Mindy\QueryBuilder\Database;

class Connection extends BaseConnection
{
    use LookupBuilderAwareTrait;
}
