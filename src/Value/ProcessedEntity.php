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

namespace Nodiskindrivea\Gelatinous\Value;

use PhpParser\Node\Name;
use function array_map;

class ProcessedEntity
{
    private array $compileTimeDependencies = [];

    private function __construct(
        private readonly string $fqn,
        private readonly EntityType $type,
        private readonly array $stmts,
        private readonly array $callTimeDependencies,
    ) {
    }

    public static function create(
        string $fqn,
        EntityType $type = EntityType::T_CLASS,
        array $stmts = [],
        array $callTimeDependencies = [],
    ): ProcessedEntity {
        return new static($fqn, $type, $stmts, $callTimeDependencies);
    }

    public function fqn(): string
    {
        return $this->fqn;
    }

    public function stmts(): array
    {
        return $this->stmts;
    }

    public function callTimeDependencies(): array
    {
        return array_map(
            fn(Name $stmt) => $stmt->toString(),
            $this->callTimeDependencies
        );
    }

    public function compileTimeDependencies(): array
    {
        return $this->compileTimeDependencies;
    }

    public function setCompileTimeDependencies(array $deps): void
    {
        $this->compileTimeDependencies = $deps;
    }

    public function type(): EntityType
    {
        return $this->type;
    }
}