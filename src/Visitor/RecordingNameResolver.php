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

use PhpParser\Node\Name;
use PhpParser\NodeVisitor\NameResolver;
use Psr\Log\LoggerInterface;

class RecordingNameResolver extends NameResolver
{
    private array $classUsages = [];
    private array $nameUsages = [];

    public function __construct(private readonly LoggerInterface $logger)
    {
        parent::__construct();
    }

    public function beforeTraverse(array $nodes)
    {
        $this->classUsages = [];
        $this->nameUsages = [];

        return parent::beforeTraverse($nodes);
    }

    public function getRecordedClassResolutions(): array
    {
        return $this->classUsages;
    }

    public function getRecordedNameResolutions(): array
    {
        return $this->nameUsages;
    }

    protected function resolveClassName(Name $name)
    {
        $resolved = parent::resolveClassName($name);
        $this->classUsages[] = $resolved;

        return $resolved;
    }

    protected function resolveName(Name $name, int $type): Name
    {
        $resolved = parent::resolveName($name, $type);
        $this->nameUsages[] = $resolved;

        return $resolved;
    }
}