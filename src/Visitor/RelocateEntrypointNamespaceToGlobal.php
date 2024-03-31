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

use PhpParser\Node;
use Psr\Log\LoggerInterface;
use function array_pop;
use function explode;
use function join;

class RelocateEntrypointNamespaceToGlobal extends NodeVisitorAbstract
{
    private string $namespace;

    public function __construct(private string $entrypointFqn, private readonly ?LoggerInterface $logger = null)
    {
        $parts = explode('\\', $this->entrypointFqn);
        array_pop($parts);
        $this->namespace = join('\\', $parts);
    }

    public function enterNode(Node $node): int|null|Node
    {
        // this assumes that at this point, FilterFunction has filtered all but one function
        // and no piece of code calls the entrypoint
        if ($node instanceof Node\Stmt\Namespace_ && $node->name->toString() === $this->namespace) {
            return new Node\Stmt\Namespace_(null, $node->stmts, $node->getAttributes());
        }

        return null;
    }
}