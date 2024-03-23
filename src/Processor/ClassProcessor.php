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

use Nodiskindrivea\Gelatinous\Value\EntityType;
use Nodiskindrivea\Gelatinous\Value\ProcessedEntity;
use Nodiskindrivea\Gelatinous\Visitor\BracketNamespace;
use Nodiskindrivea\Gelatinous\Visitor\RaiseUnsupported;
use Nodiskindrivea\Gelatinous\Visitor\RecordingNameResolver;
use Nodiskindrivea\Gelatinous\Visitor\RemoveComments;
use Nodiskindrivea\Gelatinous\Visitor\RemoveDeclares;
use Nodiskindrivea\Gelatinous\Visitor\RemoveUse;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use Psr\Log\LoggerInterface;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflector\Exception\IdentifierNotFound;
use function array_merge;

class ClassProcessor implements EntityProcessor
{
    public function __construct(
        private readonly BetterReflection $betterReflection,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * @param string $fqn
     * @param Node[] $stmts
     *
     * @return ProcessedEntity
     */
    public function traverse(string $fqn, array $stmts): ProcessedEntity
    {
        $recorder = new RecordingNameResolver($this->logger);
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new RemoveDeclares($this->logger));
        $traverser->addVisitor(new BracketNamespace($this->logger));
        $traverser->addVisitor($recorder);
        $traverser->addVisitor(new RaiseUnsupported());
        $traverser->addVisitor(new RemoveUse());
        $traverser->addVisitor(new RemoveComments());
        $stmts = $traverser->traverse($stmts);

        return ProcessedEntity::create($fqn, EntityType::T_CLASS, $stmts, $recorder->getRecordedNameResolutions());
    }

    public function processFqn(string $classOrFunctionFqn): ?ProcessedEntity
    {
        try {
            $reflected = $this->betterReflection->reflector()->reflectClass($classOrFunctionFqn);

            if (!$reflected->isInternal()) {
                $stmts = $this->betterReflection->phpParser()->parse($reflected->getLocatedSource()->getSource());
                $processed = $this->traverse($reflected->getName(), $stmts);
                $processed->setCompileTimeDependencies(
                    array_merge(
                        $reflected->getParentClassNames(),
                        $reflected->getInterfaceClassNames(),
                        $reflected->getTraitClassNames(),
                    )
                );
                return $processed;
            } else {
                $this->logger?->debug('Class {fqn} is internal, skipping', ['fqn' => $reflected->getName()]);
                return ProcessedEntity::create($reflected->getName(), EntityType::T_CLASS);
            }
        } catch (IdentifierNotFound $e) {
            $this->logger?->debug('FQN {fqn} is not a class', ['fqn' => $e->getIdentifier()->getName()]);
        }

        return null;
    }
}