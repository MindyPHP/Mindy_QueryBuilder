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
use Doctrine\DBAL\Types\Type;
use Mindy\QueryBuilder\Aggregation\Aggregation;
use Mindy\QueryBuilder\Q\Q;
use Mindy\QueryBuilder\Utils\TableNameResolver;

abstract class BaseAdapter implements AdapterInterface
{
    /**
     * @var string
     */
    protected $tablePrefix = '';
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * BaseAdapter constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return BaseExpressionBuilder|LookupCollectionInterface
     */
    abstract public function getLookupCollection();

    /**
     * TODO remove
     * {@inheritdoc}
     */
    public function quoteSql(string $sql): string
    {
        $tablePrefix = $this->tablePrefix;

        return preg_replace_callback(
            '/(\\{\\{(%?[\w\-\. ]+%?)\\}\\}|\\[\\[([\w\-\. ]+)\\]\\])|\\@([\w\-\. \/\%\:]+)\\@/',
            function ($matches) use ($tablePrefix) {
                if (isset($matches[4])) {
                    return $this->connection->quote($this->getSqlType($matches[4]));
                } elseif (isset($matches[3])) {
                    return $this->getQuotedName($matches[3]);
                }

                return str_replace('%', $tablePrefix, $this->getQuotedName($matches[2]));
            },
            $sql
        );
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function getSqlType($value)
    {
        if (gettype($value) === 'boolean') {
            return $this->getBoolean($value);
        } elseif (null === $value || 'null' === $value) {
            return 'NULL';
        }

        return $this->connection->quote($value);
    }

    /**
     * @return string
     */
    abstract public function getRandomOrder();

    /**
     * @param $value
     *
     * @return string
     */
    public function getBoolean($value = null)
    {
        return $this->connection->getDatabasePlatform()->convertBooleans($value);
    }

    /**
     * @param bool   $check
     * @param string $schema
     * @param string $table
     *
     * @return string
     */
    abstract public function sqlCheckIntegrity($check = true, $schema = '', $table = '');

    /**
     * // TODO move from here to expression builder
     * @param string $str
     * @return string
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getQuotedName($str): string
    {
        $platform = $this->connection->getDatabasePlatform();
        $keywords = $platform->getReservedKeywordsList();
        $parts = explode(".", (string)$str);
        foreach ($parts as $k => $v) {
            $parts[$k] = ($keywords->isKeyword($v)) ? $platform->quoteIdentifier($v) : $v;
        }

        return implode(".", $parts);
    }
}
