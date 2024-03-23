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

namespace Nodiskindrivea\Gelatinous\Visitor;

use PhpParser\Builder\Namespace_ as NamespaceBuilder;
use PhpParser\Node;
use Psr\Log\LoggerInterface;

class WrapEntrypoint extends NodeVisitorAbstract
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function afterTraverse(array $nodes): null|array
    {
        $i = 0;

        while ($nodes[$i] instanceof Node\Stmt\Declare_) {
            $i++;
        }
        $nodesToWrap = array_splice($nodes, $i);
        $nodes[] = (new NamespaceBuilder(null))->addStmts($nodesToWrap)->getNode();

        return $nodes;
    }
}