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

use Exception;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use Psr\Log\LoggerInterface;
use function array_pop;
use function explode;
use function in_array;
use function join;

class FilterFunction extends NodeVisitorAbstract
{
    private string $namespace = '';
    private array $keep = [];

    /**
     * @param string[] $keep
     * @param LoggerInterface|null $logger
     */
    public function __construct(array $keep, private readonly ?LoggerInterface $logger = null)
    {
        foreach ($keep as $fn) {
            $parts = explode('\\', $fn);
            $name = array_pop($parts);
            $this->keep[join('\\', $parts)][] = $name;
        }
    }

    public function enterNode(Node $node): int|null|Node
    {
        if ($node instanceof Node\Stmt\Namespace_) {
            $this->namespace = $node->name->toString() ?? '';
        } else if ($node instanceof Node\Stmt\Function_) {
            if (in_array($node->name->toString(), $this->keep[$this->namespace] ?? [])) {
                return null;
            }
            return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
        } else if ($node instanceof Node\Stmt\Class_) {
            throw new Exception('Class encountered, this shouldn\'t happen');
        }

        return null;
    }

    public function leaveNode(Node $node): int|null|Node|array
    {
        if ($node instanceof Node\Stmt\Function_) {
            if (in_array($node->name->toString(), $this->keep[$this->namespace] ?? [])) {
                return null;
            }
            return NodeTraverser::REMOVE_NODE;
        }

        return null;
    }
}