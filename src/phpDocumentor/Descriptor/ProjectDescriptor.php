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

namespace phpDocumentor\Descriptor;

use phpDocumentor\Descriptor\Interfaces\FileInterface;
use phpDocumentor\Descriptor\ProjectDescriptor\Settings;
use phpDocumentor\Descriptor\Traits\HasDescription;
use phpDocumentor\Descriptor\Traits\HasName;
use phpDocumentor\Descriptor\Traits\HasNamespace;
use phpDocumentor\Descriptor\Traits\HasPackage;
use phpDocumentor\Reflection\Fqsen;
use Webmozart\Assert\Assert;

/**
 * Represents the entire project with its files, namespaces and indexes.
 *
 * @api
 * @package phpDocumentor\AST
 */
class ProjectDescriptor implements Interfaces\ProjectInterface, Descriptor
{
    use HasName;
    use HasDescription;
    use HasPackage;
    use HasNamespace;

    private Settings $settings;

    /** @var Collection<string> $partials */
    private Collection $partials;

    /** @var Collection<VersionDescriptor> $versions */
    private Collection $versions;

    /**
     * Initializes this descriptor.
     */
    public function __construct(string $name)
    {
        $this->setName($name);
        $this->setSettings(new Settings());

        $namespace = new NamespaceDescriptor();
        $namespace->setName('\\');
        $namespace->setFullyQualifiedStructuralElementName(new Fqsen('\\'));
        $this->setNamespace($namespace);

        $package = new PackageDescriptor();
        $package->setName('\\');
        $package->setFullyQualifiedStructuralElementName(new Fqsen('\\'));
        $this->setPackage($package);

        $this->setPartials(new Collection());
        $this->versions = Collection::fromClassString(VersionDescriptor::class);
    }

    /**
     * Returns all files with their sub-elements.
     *
     * @deprecated Please use {@see DocumentationSetDescriptor::getFiles()}
     *
     * @return Collection<FileInterface>
     */
    public function getFiles(): Collection
    {
        return $this->getApiDocumentationSet()->getFiles();
    }

    /**
     * Returns all indexes in this project.
     *
     * @deprecated Please use {@see DocumentationSetDescriptor::getIndexes()}
     */
    public function getIndexes(): Collection
    {
        return $this->getApiDocumentationSet()->getIndexes();
    }

    /**
     * Sets the settings used to build the documentation for this project.
     */
    public function setSettings(Settings $settings): void
    {
        $this->settings = $settings;
    }

    /**
     * Returns the settings used to build the documentation for this project.
     */
    public function getSettings(): Settings
    {
        return $this->settings;
    }

    /**
     * Sets all partials that can be used in a template.
     *
     * Partials are blocks of text that can be inserted anywhere in a template using a special indicator. An example is
     * the introduction partial that can add a custom piece of text to the homepage.
     *
     * @param Collection<string> $partials
     */
    public function setPartials(Collection $partials): void
    {
        $this->partials = $partials;
    }

    /**
     * Returns a list of all partials.
     *
     * @see setPartials() for more information on partials.
     *
     * @return Collection<string>
     */
    public function getPartials(): Collection
    {
        return $this->partials;
    }

    /**
     * @return Collection<VersionDescriptor>
     */
    public function getVersions(): Collection
    {
        return $this->versions;
    }

    /**
     * Retrieves the first API Documentation set from the first version.
     *
     * @deprecated As soon as we are done migrating to multiple API Documentation sets, this method becomes invalid
     *     and should be removed.
     */
    private function getApiDocumentationSet(): ApiSetDescriptor
    {
        $firstVersion = $this->versions->first();
        Assert::isInstanceOf($firstVersion, VersionDescriptor::class);

        $firstApiSet = $firstVersion->getDocumentationSets()->filter(ApiSetDescriptor::class)->first();
        Assert::isInstanceOf($firstApiSet, ApiSetDescriptor::class);

        return $firstApiSet;
    }
}
