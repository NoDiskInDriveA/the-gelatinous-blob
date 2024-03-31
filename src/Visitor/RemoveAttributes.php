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
use PhpParser\NodeTraverser;
use Psr\Log\LoggerInterface;

class RemoveAttributes extends NodeVisitorAbstract
{
    public function __construct(private readonly ?LoggerInterface $logger = null)
    {
    }

    public function enterNode(Node $node): int|null|Node
    {
        if ($node instanceof Node\Attribute || $node instanceof Node\AttributeGroup) {
            $this->logger?->debug('Removing Attribute enter');
            return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
        }

        return null;
    }

    public function leaveNode(Node $node): int|null|Node|array
    {
        if ($node instanceof Node\Attribute || $node instanceof Node\AttributeGroup) {
            $this->logger?->debug('Removing Attribute leave');
            return NodeTraverser::REMOVE_NODE;
        }

        return null;
    }
}