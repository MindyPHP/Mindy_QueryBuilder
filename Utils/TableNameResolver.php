<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\QueryBuilder\Utils;

class TableNameResolver
{
    /**
     * Returns the actual name of a given table name.
     * This method will strip off curly brackets from the given table name
     * and replace the percentage character '%' with [[Connection::tablePrefix]].
     *
     * @param string      $name   the table name to be converted
     * @param string|null $prefix
     *
     * @return string the real name of the given table name
     */
    public static function getTableName(string $name, string $prefix = null): string
    {
        if (false !== strpos($name, '{{')) {
            $name = preg_replace('/\\{\\{(.*?)\\}\\}/', '\1', $name);

            return str_replace('%', $prefix, $name);
        }

        return $name;
    }
}
