<?php
/*
 * The Gelatinous Blob, a PHP source code aggregrator
 * Copyright (c) 2024 Patrick Durold
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Nodiskindrivea\Gelatinous\Processor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use function array_key_exists;
use function array_merge;
use function array_splice;
use function count;

class NamespacePostProcessor
{
    /**
     * @param Node[] $nodes
     * @return void
     */
    public function aggregateNamespaces(array &$nodes): void
    {
        $namespaceMap = [];
        $i = 0;

        while ($i < count($nodes)) {
            if ($nodes[$i] instanceof Namespace_ && $nodes[$i]->name !== null) {
                $ns = (string)$nodes[$i]->name;

                if (!array_key_exists($ns, $namespaceMap)) {
                    $namespaceMap[$ns] = &$nodes[$i];
                    $i++;
                    continue;
                } else {
                    $namespaceMap[$ns]->stmts = array_merge($namespaceMap[$ns]->stmts, array_splice($nodes, $i, 1)[0]->stmts);
                    continue;
                }
            }
            $i++;
        }
    }
}