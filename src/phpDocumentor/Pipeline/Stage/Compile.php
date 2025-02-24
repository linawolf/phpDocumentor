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

namespace phpDocumentor\Pipeline\Stage;

use Exception;
use League\Pipeline\Pipeline;

/**
 * Compiles and links the ast objects into the full ast
 */
final class Compile
{
    private Pipeline $compilerPipeline;

    /**
     * Initializes the command with all necessary dependencies to construct human-suitable output from the AST.
     */
    public function __construct(Pipeline $compilerPipeline)
    {
        $this->compilerPipeline = $compilerPipeline;
    }

    /**
     * Executes the business logic involved with this command.
     *
     * @throws Exception If the target location is not a folder.
     */
    public function __invoke(Payload $payload): Payload
    {
        $this->compilerPipeline->process($payload->getBuilder()->getProjectDescriptor());

        return $payload;
    }
}
