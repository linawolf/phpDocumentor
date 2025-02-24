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

namespace phpDocumentor\Compiler\Pass;

use phpDocumentor\Compiler\CompilerPassInterface;
use phpDocumentor\Descriptor\ApiSetDescriptor;
use phpDocumentor\Descriptor\DocumentDescriptor;
use phpDocumentor\Descriptor\GuideSetDescriptor;
use phpDocumentor\Descriptor\Interfaces\NamespaceInterface;
use phpDocumentor\Descriptor\Interfaces\ProjectInterface;
use phpDocumentor\Descriptor\TableOfContents\Entry;
use phpDocumentor\Descriptor\TocDescriptor;
use phpDocumentor\Guides\Meta\DocumentReferenceEntry;
use phpDocumentor\Guides\Meta\SectionEntry;
use phpDocumentor\Transformer\Router\Router;

use function ltrim;
use function sprintf;

final class TableOfContentsBuilder implements CompilerPassInterface
{
    private Router $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function getDescription(): string
    {
        return 'Builds table of contents for api documentation sets';
    }

    public function __invoke(ProjectInterface $project): ProjectInterface
    {
        //This looks ugly, when versions are introduced we get rid of these 2 foreach loops.
        foreach ($project->getVersions() as $version) {
            foreach ($version->getDocumentationSets() as $documentationSet) {
                if ($documentationSet instanceof ApiSetDescriptor) {
                    if ($project->getNamespace()->getChildren()->count() > 0) {
                        $namespacesToc = new TocDescriptor('Namespaces');
                        foreach ($project->getNamespace()->getChildren() as $child) {
                            $this->createNamespaceEntries($child, $namespacesToc);
                        }

                        $documentationSet->addTableOfContents($namespacesToc);
                    }

                    if ($project->getPackage()->getChildren()->count() > 0) {
                        $packagesToc = new TocDescriptor('Packages');
                        foreach ($project->getPackage()->getChildren() as $child) {
                            $this->createNamespaceEntries($child, $packagesToc);
                        }

                        $documentationSet->addTableOfContents($packagesToc);
                    }
                }

                if (!($documentationSet instanceof GuideSetDescriptor)) {
                    continue;
                }

                $documents = $documentationSet->getDocuments();
                $index     = $documents->fetch('index');
                if ($index === null) {
                    continue;
                }

                $guideToc = new TocDescriptor($index->getTitle());
                $this->createGuideEntries(
                    $index,
                    $documentationSet->getMetas()->findDocument($index->getFile()),
                    $documentationSet,
                    $guideToc
                );

                $documentationSet->addTableOfContents($guideToc);
            }
        }

        return $project;
    }

    private function createNamespaceEntries(
        NamespaceInterface $namespace,
        TocDescriptor $namespacesToc,
        ?Entry $parent = null
    ): void {
        $entry = new Entry(
            ltrim($this->router->generate($namespace), '/'),
            (string) $namespace->getFullyQualifiedStructuralElementName(),
            $parent !== null ? $parent->getUrl() : null
        );

        if ($parent !== null) {
            $parent->addChild($entry);
        }

        $namespacesToc->addEntry($entry);

        foreach ($namespace->getChildren() as $child) {
            $this->createNamespaceEntries($child, $namespacesToc, $entry);
        }
    }

    private function createGuideEntries(
        DocumentDescriptor $documentDescriptor,
        \phpDocumentor\Guides\Meta\Entry $metaEntry,
        GuideSetDescriptor $guideSetDescriptor,
        TocDescriptor $guideToc,
        ?Entry $parent = null
    ): void {
        $metas = $guideSetDescriptor->getMetas();

        foreach ($metaEntry->getChildren() as $metaChild) {
            if ($metaChild instanceof DocumentReferenceEntry) {
                $refMetaData = $metas->findDocument(ltrim($metaChild->getFile(), '/'));
                if ($refMetaData !== null) {
                    $refDocument = $guideSetDescriptor->getDocuments()->get($refMetaData->getFile());
                    $entry = new Entry(
                        sprintf(
                            '%s/%s#%s',
                            $guideSetDescriptor->getOutputLocation(),
                            ltrim($this->router->generate($refDocument), '/'),
                            $refMetaData->getTitle()->getId()
                        ),
                        $refMetaData->getTitle()->toString(),
                        $parent !== null ? $parent->getUrl() : null
                    );

                    if ($parent !== null) {
                        $parent->addChild($entry);
                    }

                    $guideToc->addEntry($entry);

                    if ($refDocument->getFile() === $documentDescriptor->getFile()) {
                        continue;
                    }

                    $this->createGuideEntries($refDocument, $refMetaData, $guideSetDescriptor, $guideToc, $entry);
                }
            }

            if (!($metaChild instanceof SectionEntry)) {
                continue;
            }

            if ($metaChild->getTitle()->getId() === $documentDescriptor->getDocumentNode()->getTitle()->getId()) {
                $this->createGuideEntries($documentDescriptor, $metaChild, $guideSetDescriptor, $guideToc, $parent);
                continue;
            }

            $entry = new Entry(
                'guide/' . ltrim($this->router->generate($documentDescriptor), '/')
                . '#' . $metaChild->getTitle()->getId(),
                $metaChild->getTitle()->toString(),
                $parent !== null ? $parent->getUrl() : null
            );

            if ($parent !== null) {
                $parent->addChild($entry);
            }

            $guideToc->addEntry($entry);

            $this->createGuideEntries($documentDescriptor, $metaChild, $guideSetDescriptor, $guideToc, $entry);
        }
    }
}
