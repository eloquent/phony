<?php

declare(strict_types=1);

namespace Eloquent\Phony;

use Countable;
use Eloquent\Phony\Assertion\Exception\AssertionException;
use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Event\EventSequence;
use Eloquent\Phony\Facade as TestNamespace;
use Eloquent\Phony\Matcher\AnyMatcher;
use Eloquent\Phony\Matcher\EqualToMatcher;
use Eloquent\Phony\Matcher\InstanceOfMatcher;
use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Matcher\WildcardMatcher;
use Eloquent\Phony\Mock\Builder\MockBuilder;
use Eloquent\Phony\Mock\Handle\HandleFactory;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Mock\Handle\StaticHandle;
use Eloquent\Phony\Mock\Mock;
use Eloquent\Phony\Spy\SpyVerifier;
use Eloquent\Phony\Stub\StubVerifier;
use Eloquent\Phony\Test\TestClassA;
use Eloquent\Phony\Test\TestClassB;
use Eloquent\Phony\Test\TestEvent;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;

class PhonyTest extends TestCase
{
    protected function setUp(): void
    {
        $this->handleFactory = HandleFactory::instance();
        $this->matcherFactory = MatcherFactory::instance();

        $this->eventA = new TestEvent(0, 0.0);
        $this->eventB = new TestEvent(1, 1.0);
    }

    public function testMockBuilder()
    {
        $actual = Phony::mockBuilder(TestClassA::class);

        $this->assertInstanceOf(MockBuilder::class, $actual);
        $this->assertInstanceOf(Mock::class, $actual->get());
        $this->assertInstanceOf(TestClassA::class, $actual->get());
    }

    public function testMockBuilderFunction()
    {
        $actual = mockBuilder(TestClassA::class);

        $this->assertInstanceOf(MockBuilder::class, $actual);
        $this->assertInstanceOf(Mock::class, $actual->get());
        $this->assertInstanceOf(TestClassA::class, $actual->get());
    }

    public function testPartialMock()
    {
        $types = [TestClassB::class, Countable::class];
        $arguments = new Arguments(['a', 'b']);
        $actual = Phony::partialMock($types, $arguments);

        $this->assertInstanceOf(InstanceHandle::class, $actual);
        $this->assertInstanceOf(Mock::class, $actual->get());
        $this->assertInstanceOf(TestClassB::class, $actual->get());
        $this->assertInstanceOf(Countable::class, $actual->get());
        $this->assertSame(['a', 'b'], $actual->get()->constructorArguments);
        $this->assertSame('ab', $actual->get()->testClassAMethodA('a', 'b'));
    }

    public function testPartialMockWithNullArguments()
    {
        $types = [TestClassB::class, Countable::class];
        $arguments = null;
        $actual = Phony::partialMock($types, $arguments);

        $this->assertInstanceOf(InstanceHandle::class, $actual);
        $this->assertInstanceOf(Mock::class, $actual->get());
        $this->assertInstanceOf(TestClassB::class, $actual->get());
        $this->assertInstanceOf(Countable::class, $actual->get());
        $this->assertNull($actual->get()->constructorArguments);
        $this->assertSame('ab', $actual->get()->testClassAMethodA('a', 'b'));
    }

    public function testPartialMockWithNoArguments()
    {
        $types = [TestClassB::class, Countable::class];
        $actual = Phony::partialMock($types);

        $this->assertInstanceOf(InstanceHandle::class, $actual);
        $this->assertInstanceOf(Mock::class, $actual->get());
        $this->assertInstanceOf(TestClassB::class, $actual->get());
        $this->assertInstanceOf(Countable::class, $actual->get());
        $this->assertEquals([], $actual->get()->constructorArguments);
        $this->assertSame('ab', $actual->get()->testClassAMethodA('a', 'b'));
    }

    public function testPartialMockDefaults()
    {
        $actual = Phony::partialMock();

        $this->assertInstanceOf(InstanceHandle::class, $actual);
        $this->assertInstanceOf(Mock::class, $actual->get());
    }

    public function testPartialMockFunction()
    {
        $types = [TestClassB::class, Countable::class];
        $arguments = new Arguments(['a', 'b']);
        $actual = partialMock($types, $arguments);

        $this->assertInstanceOf(InstanceHandle::class, $actual);
        $this->assertInstanceOf(Mock::class, $actual->get());
        $this->assertInstanceOf(TestClassB::class, $actual->get());
        $this->assertInstanceOf(Countable::class, $actual->get());
        $this->assertSame(['a', 'b'], $actual->get()->constructorArguments);
        $this->assertSame('ab', $actual->get()->testClassAMethodA('a', 'b'));
    }

    public function testPartialMockFunctionWithNullArguments()
    {
        $types = [TestClassB::class, Countable::class];
        $arguments = null;
        $actual = partialMock($types, $arguments);

        $this->assertInstanceOf(InstanceHandle::class, $actual);
        $this->assertInstanceOf(Mock::class, $actual->get());
        $this->assertInstanceOf(TestClassB::class, $actual->get());
        $this->assertInstanceOf(Countable::class, $actual->get());
        $this->assertNull($actual->get()->constructorArguments);
        $this->assertSame('ab', $actual->get()->testClassAMethodA('a', 'b'));
    }

    public function testPartialMockFunctionWithNoArguments()
    {
        $types = [TestClassB::class, Countable::class];
        $actual = partialMock($types);

        $this->assertInstanceOf(InstanceHandle::class, $actual);
        $this->assertInstanceOf(Mock::class, $actual->get());
        $this->assertInstanceOf(TestClassB::class, $actual->get());
        $this->assertInstanceOf(Countable::class, $actual->get());
        $this->assertEquals([], $actual->get()->constructorArguments);
        $this->assertSame('ab', $actual->get()->testClassAMethodA('a', 'b'));
    }

    public function testPartialMockFunctionDefaults()
    {
        $actual = partialMock();

        $this->assertInstanceOf(InstanceHandle::class, $actual);
        $this->assertInstanceOf(Mock::class, $actual->get());
    }

    public function testMock()
    {
        $types = [TestClassB::class, Countable::class];
        $actual = Phony::mock($types);

        $this->assertInstanceOf(InstanceHandle::class, $actual);
        $this->assertInstanceOf(Mock::class, $actual->get());
        $this->assertInstanceOf(TestClassB::class, $actual->get());
        $this->assertInstanceOf(Countable::class, $actual->get());
        $this->assertNull($actual->get()->constructorArguments);
        $this->assertNull($actual->get()->testClassAMethodA('a', 'b'));
    }

    public function testMockFunction()
    {
        $types = [TestClassB::class, Countable::class];
        $actual = mock($types);

        $this->assertInstanceOf(InstanceHandle::class, $actual);
        $this->assertInstanceOf(Mock::class, $actual->get());
        $this->assertInstanceOf(TestClassB::class, $actual->get());
        $this->assertInstanceOf(Countable::class, $actual->get());
        $this->assertNull($actual->get()->constructorArguments);
        $this->assertNull($actual->get()->testClassAMethodA('a', 'b'));
    }

    public function testOnStatic()
    {
        $class = Phony::mockBuilder()->build();
        $actual = Phony::onStatic($class);

        $this->assertInstanceOf(StaticHandle::class, $actual);
        $this->assertSame($class, $actual->class());
    }

    public function testOnStaticFunction()
    {
        $class = mockBuilder()->build();
        $actual = onStatic($class);

        $this->assertInstanceOf(StaticHandle::class, $actual);
        $this->assertSame($class, $actual->class());
    }

    public function testOn()
    {
        $mock = Phony::mockBuilder()->partial();
        $actual = Phony::on($mock);

        $this->assertInstanceOf(InstanceHandle::class, $actual);
        $this->assertSame($mock, $actual->get());
    }

    public function testOnFunction()
    {
        $mock = mockBuilder()->partial();
        $actual = on($mock);

        $this->assertInstanceOf(InstanceHandle::class, $actual);
        $this->assertSame($mock, $actual->get());
    }

    public function testSpy()
    {
        $callback = function () {};
        $actual = Phony::spy($callback);

        $this->assertInstanceOf(SpyVerifier::class, $actual);
        $this->assertSame($callback, $actual->callback());
    }

    public function testSpyFunction()
    {
        $callback = function () {};
        $actual = spy($callback);

        $this->assertInstanceOf(SpyVerifier::class, $actual);
        $this->assertSame($callback, $actual->callback());
    }

    public function testSpyGlobal()
    {
        $actual = Phony::spyGlobal('sprintf', TestNamespace::class);

        $this->assertInstanceOf(SpyVerifier::class, $actual);
        $this->assertSame('a, b', TestNamespace\sprintf('%s, %s', 'a', 'b'));
        $this->assertTrue((bool) $actual->calledWith('%s, %s', 'a', 'b'));
    }

    public function testSpyGlobalFunction()
    {
        $actual = spyGlobal('vsprintf', TestNamespace::class);

        $this->assertInstanceOf(SpyVerifier::class, $actual);
        $this->assertSame('a, b', TestNamespace\vsprintf('%s, %s', ['a', 'b']));
        $this->assertTrue((bool) $actual->calledWith('%s, %s', ['a', 'b']));
    }

    public function testStub()
    {
        $callback = function () { return 'a'; };
        $actual = Phony::stub($callback);

        $this->assertInstanceOf(StubVerifier::class, $actual);
        $this->assertSame('a', call_user_func($actual->stub()->callback()));
        $this->assertSame($actual->stub(), $actual->spy()->callback());
    }

    public function testStubFunction()
    {
        $callback = function () { return 'a'; };
        $actual = stub($callback);

        $this->assertInstanceOf(StubVerifier::class, $actual);
        $this->assertSame('a', call_user_func($actual->stub()->callback()));
        $this->assertSame($actual->stub(), $actual->spy()->callback());
    }

    public function testStubGlobal()
    {
        $actual = Phony::stubGlobal('sprintf', TestNamespace::class);
        $actual->with('%s, %s', 'a', 'b')->forwards();

        $this->assertInstanceOf(StubVerifier::class, $actual);
        $this->assertSame('a, b', TestNamespace\sprintf('%s, %s', 'a', 'b'));
        $this->assertEmpty(TestNamespace\sprintf('x', 'y'));
        $this->assertTrue((bool) $actual->calledWith('%s, %s', 'a', 'b'));
    }

    public function testStubGlobalFunction()
    {
        $actual = stubGlobal('vsprintf', TestNamespace::class);
        $actual->with('%s, %s', ['a', 'b'])->forwards();

        $this->assertInstanceOf(StubVerifier::class, $actual);
        $this->assertSame('a, b', TestNamespace\vsprintf('%s, %s', ['a', 'b']));
        $this->assertEmpty(TestNamespace\vsprintf('x', ['y']));
        $this->assertTrue((bool) $actual->calledWith('%s, %s', ['a', 'b']));
    }

    public function testRestoreGlobalFunctions()
    {
        Phony::stubGlobal('sprintf', TestNamespace::class);
        Phony::stubGlobal('vsprintf', TestNamespace::class);

        $this->assertEmpty(TestNamespace\sprintf('%s, %s', 'a', 'b'));
        $this->assertEmpty(TestNamespace\vsprintf('%s, %s', ['a', 'b']));

        Phony::restoreGlobalFunctions();

        $this->assertSame('a, b', TestNamespace\sprintf('%s, %s', 'a', 'b'));
        $this->assertSame('a, b', TestNamespace\vsprintf('%s, %s', ['a', 'b']));
    }

    public function testRestoreGlobalFunctionsFunction()
    {
        stubGlobal('sprintf', TestNamespace::class);
        stubGlobal('vsprintf', TestNamespace::class);

        $this->assertEmpty(TestNamespace\sprintf('%s, %s', 'a', 'b'));
        $this->assertEmpty(TestNamespace\vsprintf('%s, %s', ['a', 'b']));

        restoreGlobalFunctions();

        $this->assertSame('a, b', TestNamespace\sprintf('%s, %s', 'a', 'b'));
        $this->assertSame('a, b', TestNamespace\vsprintf('%s, %s', ['a', 'b']));
    }

    public function testEventOrderMethods()
    {
        $this->assertTrue((bool) Phony::checkInOrder($this->eventA, $this->eventB));
        $this->assertFalse((bool) Phony::checkInOrder($this->eventB, $this->eventA));

        $result = Phony::inOrder($this->eventA, $this->eventB);

        $this->assertInstanceOf(EventSequence::class, $result);
        $this->assertEquals([$this->eventA, $this->eventB], $result->allEvents());

        $this->assertTrue((bool) Phony::checkAnyOrder($this->eventA, $this->eventB));
        $this->assertFalse((bool) Phony::checkAnyOrder());

        $result = Phony::anyOrder($this->eventA, $this->eventB);

        $this->assertInstanceOf(EventSequence::class, $result);
        $this->assertEquals([$this->eventA, $this->eventB], $result->allEvents());

        $this->assertFalse((bool) Phony::checkAnyOrder());
    }

    public function testInOrderMethodFailure()
    {
        $this->expectException(AssertionException::class);
        Phony::inOrder($this->eventB, $this->eventA);
    }

    public function testAnyOrderMethodFailure()
    {
        $this->expectException(AssertionException::class);
        Phony::anyOrder();
    }

    public function testEventOrderFunctions()
    {
        $this->assertTrue((bool) checkInOrder($this->eventA, $this->eventB));
        $this->assertFalse((bool) checkInOrder($this->eventB, $this->eventA));

        $result = inOrder($this->eventA, $this->eventB);

        $this->assertInstanceOf(EventSequence::class, $result);
        $this->assertEquals([$this->eventA, $this->eventB], $result->allEvents());

        $this->assertTrue((bool) checkAnyOrder($this->eventA, $this->eventB));
        $this->assertFalse((bool) checkAnyOrder());

        $result = anyOrder($this->eventA, $this->eventB);

        $this->assertInstanceOf(EventSequence::class, $result);
        $this->assertEquals([$this->eventA, $this->eventB], $result->allEvents());

        $this->assertFalse((bool) checkAnyOrder());
    }

    public function testInOrderFunctionFailure()
    {
        $this->expectException(AssertionException::class);
        inOrder($this->eventB, $this->eventA);
    }

    public function testAnyOrderFunctionFailure()
    {
        $this->expectException(AssertionException::class);
        anyOrder();
    }

    public function testAny()
    {
        $actual = Phony::any();

        $this->assertInstanceOf(AnyMatcher::class, $actual);
    }

    public function testAnyFunction()
    {
        $actual = any();

        $this->assertInstanceOf(AnyMatcher::class, $actual);
    }

    public function testEqualTo()
    {
        $actual = Phony::equalTo('a');

        $this->assertInstanceOf(EqualToMatcher::class, $actual);
        $this->assertSame('a', $actual->value());
    }

    public function testEqualToFunction()
    {
        $actual = equalTo('a');

        $this->assertInstanceOf(EqualToMatcher::class, $actual);
        $this->assertSame('a', $actual->value());
    }

    public function testAnInstanceOf()
    {
        $actual = Phony::anInstanceOf(TestClassA::class);

        $this->assertInstanceOf(InstanceOfMatcher::class, $actual);
        $this->assertSame(TestClassA::class, $actual->type());
    }

    public function testAnInstanceOfFunction()
    {
        $actual = anInstanceOf(TestClassA::class);

        $this->assertInstanceOf(InstanceOfMatcher::class, $actual);
        $this->assertSame(TestClassA::class, $actual->type());
    }

    public function testWildcard()
    {
        $actual = Phony::wildcard('a', 1, 2);

        $this->assertInstanceOf(WildcardMatcher::class, $actual);
        $this->assertInstanceOf(EqualToMatcher::class, $actual->matcher());
        $this->assertSame('a', $actual->matcher()->value());
        $this->assertSame(1, $actual->minimumArguments());
        $this->assertSame(2, $actual->maximumArguments());
    }

    public function testWildcardFunction()
    {
        $actual = wildcard('a', 1, 2);

        $this->assertInstanceOf(WildcardMatcher::class, $actual);
        $this->assertInstanceOf(EqualToMatcher::class, $actual->matcher());
        $this->assertSame('a', $actual->matcher()->value());
        $this->assertSame(1, $actual->minimumArguments());
        $this->assertSame(2, $actual->maximumArguments());
    }

    public function testEmptyValue()
    {
        $typeA = (new ReflectionFunction(function (): bool {}))->getReturnType();
        $typeB = (new ReflectionFunction(function (): int {}))->getReturnType();
        $typeC = (new ReflectionFunction(function (): string {}))->getReturnType();

        $this->assertFalse(Phony::emptyValue($typeA));
        $this->assertSame(0, Phony::emptyValue($typeB));
        $this->assertSame('', Phony::emptyValue($typeC));
    }

    public function testEmptyValueFunction()
    {
        $typeA = (new ReflectionFunction(function (): bool {}))->getReturnType();
        $typeB = (new ReflectionFunction(function (): int {}))->getReturnType();
        $typeC = (new ReflectionFunction(function (): string {}))->getReturnType();

        $this->assertFalse(emptyValue($typeA));
        $this->assertSame(0, emptyValue($typeB));
        $this->assertSame('', emptyValue($typeC));
    }

    public function testSetExportDepth()
    {
        $this->assertSame(1, Phony::setExportDepth(111));
        $this->assertSame(111, Phony::setExportDepth(1));
    }

    public function testSetExportDepthFunction()
    {
        $this->assertSame(1, setExportDepth(111));
        $this->assertSame(111, setExportDepth(1));
    }

    public function testSetUseColor()
    {
        $this->assertNull(Phony::setUseColor(false));
    }

    public function testSetUseColorFunction()
    {
        $this->assertNull(setUseColor(false));
    }
}
