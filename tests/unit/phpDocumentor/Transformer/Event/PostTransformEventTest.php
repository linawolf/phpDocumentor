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

namespace phpDocumentor\Transformer\Event;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use phpDocumentor\Descriptor\ProjectDescriptor;
use stdClass;

/**
 * @coversDefaultClass \phpDocumentor\Transformer\Event\PostTransformEvent
 * @covers ::__construct
 */
final class PostTransformEventTest extends MockeryTestCase
{
    /** @var PostTransformEvent $fixture */
    private $fixture;

    /**
     * Creates a new (empty) fixture object.
     */
    protected function setUp() : void
    {
        $this->fixture = new PostTransformEvent(new stdClass());
    }

    /**
     * @covers ::createInstance
     * @covers ::getSubject
     */
    public function testCreatingAnInstance() : void
    {
        $subject = new stdClass();
        $this->fixture = PostTransformEvent::createInstance($subject);
        $this->assertSame($subject, $this->fixture->getSubject());
    }

    /**
     * @covers ::getProject
     * @covers ::setProject
     */
    public function testSetAndGetProject() : void
    {
        $project = m::mock(ProjectDescriptor::class);
        $this->assertNull($this->fixture->getProject());

        $this->fixture->setProject($project);

        $this->assertSame($project, $this->fixture->getProject());
    }
}
