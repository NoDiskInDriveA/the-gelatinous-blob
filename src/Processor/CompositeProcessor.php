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

use Exception;
use Nodiskindrivea\Gelatinous\Value\ProcessedEntity;
use Psr\Log\LoggerInterface;

class CompositeProcessor implements EntityProcessor
{
    /**
     * @var EntityProcessor[]
     */
    private array $entityProcessors;

    public function __construct(
        private readonly ?LoggerInterface $logger = null,
        EntityProcessor ...$entityProcessors,
    ) {
        $this->entityProcessors = $entityProcessors;
    }

    /**
     * @param string $classOrFunctionFqn
     * @return ProcessedEntity
     *
     * @throws Exception
     */
    public function processFqn(string $classOrFunctionFqn): ProcessedEntity
    {
        $this->logger?->debug('Resolving {fqn}...', ['fqn' => $classOrFunctionFqn]);
        foreach ($this->entityProcessors as $entityProcessor) {
            $this->logger?->debug('Trying {processor}...', ['processor' => $entityProcessor::class]);
            if (null !== ($processedEntity = $entityProcessor->processFqn($classOrFunctionFqn))) {
                return $processedEntity;
            }
        }

        throw new Exception(sprintf('Could not resolve FQN %s', $classOrFunctionFqn));
    }
}