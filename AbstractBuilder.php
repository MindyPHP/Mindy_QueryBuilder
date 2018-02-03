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
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use Doctrine\DBAL\Schema\Schema;

class AbstractBuilder
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var BaseExpressionBuilder
     */
    protected $expr;

    /**
     * @var Schema
     */
    protected $schema;

    /**
     * Builder constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->schema = $connection->getSchemaManager()->createSchema();
        $this->expr = $connection->getExpressionBuilder();
    }

    public function isSupport(string $lookup): bool
    {
        return method_exists($this, $this->formatMethod($lookup));
    }

    protected function formatMethod(string $lookup): string
    {
        return sprintf('parse%s', ucfirst($lookup));
    }

    public function parse(string $str): array
    {
        $lookups = explode('__', $str);
        $column = array_shift($lookups);
        if (count($lookups) == 0) {
            $lookups[] = 'exact';
        }

        return [$column, $lookups];
    }

    public function build(string $lookup, $parameters): string
    {
        list($column, $lookups) = $this->parse($lookup);
        $first = current($lookups);

        $method = $this->formatMethod($first);
        if (false === $this->isSupport($first)) {
            throw new \RuntimeException(sprintf(
                '%s lookup not supported',
                $first
            ));
        }

        return call_user_func_array([$this, $method], [$column, $parameters]);
    }
}
