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

namespace Nodiskindrivea\Gelatinous;

use Exception;
use Nodiskindrivea\Gelatinous\Processor\ClassProcessor;
use Nodiskindrivea\Gelatinous\Processor\CompositeProcessor;
use Nodiskindrivea\Gelatinous\Processor\ConstantProcessor;
use Nodiskindrivea\Gelatinous\Processor\FunctionProcessor;
use Nodiskindrivea\Gelatinous\Value\ResolverQueue;
use PhpParser\Node;
use Psr\Log\LoggerInterface;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflector\Exception\IdentifierNotFound;

class Aggregator
{
    public function __construct(
        private readonly BetterReflection $betterReflection,
        private readonly ?LoggerInterface $logger = null
    ) {
    }

    /**
     * @return Node[]
     * @throws Exception
     */
    public function fromEntryPoint(string $entrypointFqn): array
    {
        $entitiesProcessor = new CompositeProcessor(
            $this->logger,
            new ClassProcessor($this->betterReflection, $this->logger),
            new FunctionProcessor($entrypointFqn, $this->betterReflection, $this->logger),
            new ConstantProcessor($this->betterReflection, $this->logger)
        );

        $q = new ResolverQueue();
        $q->addUnprocessed($entrypointFqn);

        $aggregatedStmts = [];
        while (!$q->empty()) {
            $fqn = $q->getNextUnprocessed();
            try {
                $processedEntity = $entitiesProcessor->processFqn($fqn);
                $q->addUnprocessed(...$processedEntity->callTimeDependencies());

                if ($q->hasUnprocessed(...$processedEntity->compileTimeDependencies())) {
                    $q->addUnprocessed($fqn);
                    $this->logger?->debug(sprintf("Requeueing FQN %s on missing compile time dependencies", $fqn));
                    continue;
                }
                $aggregatedStmts = array_merge($aggregatedStmts, $processedEntity->stmts());

            } catch (IdentifierNotFound $e) {
                $this->logger->error($e->getMessage());
            }

            $q->addProcessed($fqn);
        }

        return $aggregatedStmts;
    }
}