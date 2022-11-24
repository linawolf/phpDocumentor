<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\Descriptor\Interfaces;

use phpDocumentor\Descriptor\Collection;
use phpDocumentor\Descriptor\Tag\VarDescriptor;
use phpDocumentor\Reflection\Type;

/**
 * Descriptor representing a constant on a class, trait, property or file.
 */
interface ConstantInterface extends ElementInterface, TypeInterface, ChildInterface
{
    /**
     * Sets the types that this constant may contain.
     */
    public function setType(Type $type): void;

    /**
     * Returns the types that may be present in this constant.
     *
     * @return list<Type>
     */
    public function getTypes(): array;

    /**
     * Sets the value representation for this constant.
     */
    public function setValue(string $value): void;

    /**
     * Retrieves a textual representation of the value in this constant.
     */
    public function getValue(): string;

    public function setFinal(bool $final): void;

    public function isFinal(): bool;

    /**
     * @return Collection<VarDescriptor>
     */
    public function getVar(): Collection;
}
