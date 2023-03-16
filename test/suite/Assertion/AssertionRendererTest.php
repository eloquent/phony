<?php

declare(strict_types=1);

namespace Eloquent\Phony\Assertion;

use AllowDynamicProperties;
use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use Eloquent\Phony\Test\TestClassA;
use Eloquent\Phony\Verification\Cardinality;
use IteratorAggregate;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[AllowDynamicProperties]
class AssertionRendererTest extends TestCase
{
    protected function setUp(): void
    {
        $container = FacadeContainer::withTestCallFactory();
        $callFactory = $container->callFactory;
        $container->differenceEngine->setUseColor(false);
        $this->subject = $container->assertionRenderer;
        $this->subject->setUseColor(false);

        $this->thisObjectA = new TestClassA();

        $handleFactory = $container->handleFactory;
        $mockBuilderFactory = $container->mockBuilderFactory;

        $mockBuilder = $mockBuilderFactory->create(TestClassA::class);
        $this->thisObjectB = $mockBuilder->get();
        $this->thisObjectBHandle = $handleFactory->instanceHandle($this->thisObjectB, 'label');
        $this->thisObjectB->testClassAMethodA();

        $mockBuilder = $mockBuilderFactory->create(IteratorAggregate::class);
        $this->thisObjectC = $mockBuilder->get();
        $this->thisObjectCHandle = $handleFactory->instanceHandle($this->thisObjectC, 'label');

        $eventFactory = $callFactory->eventFactory();
        $this->callA = $callFactory->create(
            $eventFactory
                ->createCalled([$this->thisObjectA, 'testClassAMethodA'], [], Arguments::create('a', 'b')),
            $eventFactory->createReturned('x'),
            null,
            $eventFactory->createReturned('x')
        );
        $this->callB = $callFactory->create(
            $eventFactory->createCalled('implode'),
            $eventFactory->createThrew(new RuntimeException('You done goofed.')),
            null,
            $eventFactory->createThrew(new RuntimeException('You done goofed.'))
        );
        $this->callC = $callFactory->create(
            $eventFactory->createCalled('implode')
        );
        $this->callD = $callFactory->create(
            $eventFactory->createCalled([$this->thisObjectB, 'testClassAMethodA'])
        );
        $this->callE = $callFactory->create(
            $eventFactory->createCalled([$this->thisObjectC, 'getIterator'])
        );
        $this->callF = $callFactory->create(
            $eventFactory
                ->createCalled($this->thisObjectBHandle->testClassAMethodA, [], Arguments::create()),
            $eventFactory->createReturned(null),
            null,
            $eventFactory->createReturned(null)
        );

        $this->cardinality = new Cardinality(0, 1);

        $this->matcherFactory = $container->matcherFactory;
    }

    public function testRenderValue()
    {
        $this->assertSame('"x"', $this->subject->renderValue('x'));
        $this->assertSame('111', $this->subject->renderValue(111));
        $this->assertSame('"x\ny"', $this->subject->renderValue("x\ny"));
        $this->assertSame(
            '"12345678901234567890123456789012345678901234567890"',
            $this->subject->renderValue('12345678901234567890123456789012345678901234567890')
        );
    }

    public function testRenderMatchers()
    {
        $matcherA = $this->matcherFactory->equalTo('a');
        $matcherB = $this->matcherFactory->equalTo(111);

        $this->assertSame('<none>', $this->subject->renderMatchers([]));
        $this->assertSame('"a"', $this->subject->renderMatchers([$matcherA]));
        $this->assertSame('"a", 111', $this->subject->renderMatchers([$matcherA, $matcherB]));
    }
}
