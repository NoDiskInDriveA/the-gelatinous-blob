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
use Nodiskindrivea\Gelatinous\Value\EntityType;
use Nodiskindrivea\Gelatinous\Value\ProcessedEntity;
use Psr\Log\LoggerInterface;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflector\Exception\IdentifierNotFound;

class ConstantProcessor implements EntityProcessor
{
    public function __construct(
        private readonly BetterReflection $betterReflection,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * @throws Exception
     */
    public function processFqn(string $classOrFunctionFqn): ?ProcessedEntity
    {
        try {
            $reflected = $this->betterReflection->reflector()->reflectConstant($classOrFunctionFqn);
            if (!$reflected->isInternal()) {
                throw new Exception('Constants are not supported yet');
            } else {
                $this->logger?->debug('Constant {fqn} is internal, skipping', ['fqn' => $reflected->getName()]);
                return ProcessedEntity::create($reflected->getName(), EntityType::T_CONSTANT);
            }
        } catch (IdentifierNotFound $e) {
            $this->logger?->debug('FQN {fqn} is not a constant', ['fqn' => $e->getIdentifier()->getName()]);
        }

        return null;
    }
}