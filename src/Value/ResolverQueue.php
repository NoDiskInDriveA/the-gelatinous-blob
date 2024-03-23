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

use function array_diff;
use function array_merge;
use function array_shift;
use function array_unique;

class ResolverQueue
{
    private const SPECIAL_NAMES = ['self', 'static', 'parent'];

    private array $unprocessed = [];
    private array $processed = [];

    public function getNextUnprocessed(): ?string
    {
        return array_shift($this->unprocessed);
    }

    public function hasUnprocessed(string ...$fqns): bool
    {
         return !empty(array_diff($fqns, $this->processed, static::SPECIAL_NAMES));
    }

    public function empty(): bool
    {
        return empty($this->unprocessed);
    }

    public function addUnprocessed(string ...$fqns): void
    {
        $this->unprocessed = array_unique(
            array_diff(
                array_merge(
                    $this->unprocessed,
                    $fqns
                ),
                $this->processed,
                static::SPECIAL_NAMES
            )
        );
    }

    public function addProcessed(string ...$fqns): void
    {
        $this->processed = array_unique(array_merge($this->processed, $fqns));
    }
}