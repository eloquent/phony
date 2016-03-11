# Phony

- [Installation]
- [Help]
- [Usage]
    - [Example test suites]
    - [Standalone usage]
    - [Peridot usage]
    - [Pho usage]
    - [PHPUnit usage]
    - [SimpleTest usage]
    - [Integration with test frameworks]
    - [Importing]
        - [Importing with use function]
        - [Importing without use function]
        - [Importing a static facade]
- [Mocks]
    - [The mock API]
    - [The mock builder API]
    - [Mocking basics]
    - [Partial mocks]
    - [Proxy mocks]
    - [Mocking multiple types]
    - [Ad hoc mocks]
        - [Ad hoc definition values]
        - [Ad hoc definition magic "self" values]
    - [Static mocks]
    - [Calling a constructor manually]
    - [Labeling mocks]
    - [Mock handles]
        - [Stubbing handles]
        - [Verification handles]
        - [Mock handle substitution]
    - [Mock builders]
        - [Customizing the mock class]
        - [Creating mocks from a builder]
        - [Generating mock classes from a builder]
        - [Copying mock builders]
    - [Pausing mock recording]
    - [Terminology]
- [Stubs]
    - [The stub API]
    - [The generator answer API]
    - [Stubbing an existing callable]
    - [Anonymous stubs]
    - [Stub "self" values]
        - [Magic "self" values]
    - [Stub rules and answers]
        - [Multiple rules]
        - [Multiple answers]
        - [Overriding rules]
        - [The default rule and answer]
            - [The default answer callback]
    - [Matching stub arguments]
    - [Returning values]
        - [Default values for return types]
    - [Returning arguments]
    - [Returning the "self" value]
    - [Throwing exceptions]
    - [Using a callable as an answer]
    - [Forwarding to the original callable]
    - [Answers that perform multiple actions]
        - [Setting passed-by-reference arguments]
        - [Invoking arguments]
        - [Invoking callables]
    - [Stubbing generators]
        - [Yielding from a generator]
        - [Returning values from a generator]
        - [Returning arguments from a generator]
        - [Returning the "self" value from a generator]
        - [Throwing exceptions from a generator]
        - [Generator iterations that perform multiple actions]
            - [Setting passed-by-reference arguments in a generator]
            - [Invoking arguments in a generator]
            - [Invoking callables in a generator]
- [Spies]
    - [The spy API]
    - [Spying on an existing callable]
    - [Anonymous spies]
    - [Call verification]
        - [Call count]
        - [Individual calls]
    - [Verifying spy input]
        - [Verifying that a call was made]
        - [Verifying that a spy was called with specific arguments]
        - [Verifying spy closure binding]
    - [Verifying spy output]
        - [Verifying spy return values]
        - [Verifying spy exceptions]
    - [Verifying spies with generators or traversables]
        - [Verifying values produced by spies]
        - [Verifying values received by spies]
        - [Verifying exceptions received by spies]
    - [Verifying cardinality with spies]
        - [Verifying that a spy event happened an exact number of times]
        - [Verifying that a spy event happened a bounded number of times]
        - [Verifying that all spy events happen the same way]
    - [Labeling spies]
    - [Invoking spies]
    - [Pausing spy recording]
- [Calls]
    - [The call API]
    - [The arguments API]
    - [Retrieving calls from a spy]
    - [Verifying call input]
        - [Verifying that a call was made with specific arguments]
        - [Verifying call closure binding]
    - [Verifying call output]
        - [Verifying call return values]
        - [Verifying call exceptions]
    - [Verifying calls with generators or traversables]
        - [Verifying values produced by calls]
        - [Verifying values received by calls]
        - [Verifying exceptions received by calls]
    - [Verifying cardinality with calls]
        - [Verifying that a call event happened an exact number of times]
        - [Verifying that a call event happened a bounded number of times]
        - [Verifying that all call events happen the same way]
- [Verification]
    - [The verification result API]
    - [The event API]
    - [The order verification API]
    - [Standard verification]
    - [Check verification]
    - [Order verification]
        - [Dynamic order verification]
        - [Order verification caveats]
            - [Intermediate events in order verification]
            - [Similar events in order verification]
    - [Verifying that there was no interaction with a mock]
- [Matchers]
    - [The matcher API]
    - [The wildcard matcher API]
    - [Matcher integrations]
        - [Counterpart matchers]
        - [Hamcrest matchers]
        - [Mockery matchers]
        - [Phake matchers]
        - [PHPUnit constraints]
        - [Prophecy argument tokens]
        - [SimpleTest expectations]
    - [Shorthand matchers]
    - [The "any" matcher]
    - [The "equal to" matcher]
        - [When to use the "equal to" matcher]
        - [Special cases for the "equal to" matcher]
            - [Comparing exceptions]
            - [Comparing mocks]
    - [The "wildcard" matcher]
        - [Third-party wildcard matcher integrations]
            - [Phake wildcard matcher integration]
            - [Prophecy wildcard matcher integration]
- [The exporter]
    - [The exporter API]
    - [The export format]
        - [Export identifiers and references]
        - [Exporting recursive values]
        - [Exporter special cases]
            - [Exporting exceptions]
            - [Exporting mocks]
    - [Export depth]
        - [Setting the export depth]
- [Thrown exceptions]
    - [AssertionException]
    - [UndefinedArgumentException]
    - [UndefinedCallException]
    - [UndefinedEventException]
    - [UndefinedResponseException]
- [License]

## Installation

Available as [Composer] package [eloquent/phony].

This document represents the state of the `master` branch, and in rare cases
may describe features that are not yet tagged. In case of emergency,
`dev-master` can be used as the Composer version constraint.

## Help

For help with a difficult testing scenario, questions regarding how to use
*Phony*, or to report issues with *Phony* itself, please open a [GitHub issue]
so that others may benefit from the outcome.

Alternatively, [@ezzatron] may be contacted directly via [Twitter].

## Usage

### Example test suites

See the [example] directory.

### Standalone usage

```php
use function Eloquent\Phony\mock;

$handle = mock('ClassA');
$handle->methodA('argument')->returns('value');

$mock = $handle->mock();

assert($mock->methodA('argument') === 'value');
$handle->methodA->calledWith('argument');
```

### [Peridot] usage

```php
use function Eloquent\Phony\mock;

describe('Phony', function () {
    it('integrates with Peridot', function () {
        $handle = mock('ClassA');
        $handle->methodA('argument')->returns('value');

        $mock = $handle->mock();

        expect($mock->methodA('argument'))->to->equal('value');
        $handle->methodA->calledWith('argument');
    });
});
```

### [Pho] usage

```php
use function Eloquent\Phony\Pho\mock;

describe('Phony', function () {
    it('integrates with Pho', function () {
        $handle = mock('ClassA');
        $handle->methodA('argument')->returns('value');

        $mock = $handle->mock();

        expect($mock->methodA('argument'))->toBe('value');
        $handle->methodA->calledWith('argument');
    });
});
```

### [PHPUnit] usage

```php
use Eloquent\Phony\Phpunit\Phony;

class PhonyTest extends PHPUnit_Framework_TestCase
{
    public function testIntegration()
    {
        $handle = Phony::mock('ClassA');
        $handle->methodA('argument')->returns('value');

        $mock = $handle->mock();

        $this->assertSame('value', $mock->methodA('argument'));
        $handle->methodA->calledWith('argument');
    }
}
```

### [SimpleTest] usage

```php
use Eloquent\Phony\Simpletest\Phony;

class PhonyTest extends UnitTestCase
{
    public function testIntegration()
    {
        $handle = Phony::mock('ClassA');
        $handle->methodA('argument')->returns('value');

        $mock = $handle->mock();

        $this->assertSame($mock->methodA('argument'), 'value');
        $handle->methodA->calledWith('argument');
    }
}
```

### Integration with test frameworks

In order to provide the easiest integration with test frameworks, *Phony*
exposes the same API through multiple namespaces. Integration is as simple as
importing the most appropriate namespace for the framework in use:

- For [Pho], use `Eloquent\Phony\Pho`.
- For [PHPUnit], use `Eloquent\Phony\Phpunit`.
- For [SimpleTest], use `Eloquent\Phony\Simpletest`.
- For [Peridot], other frameworks, or standalone usage, use `Eloquent\Phony`.

### Importing

There are three ways to import *Phony*'s API. The most appropriate choice will
depend on the test framework in use, and the user's preferred coding style.

#### Importing with [use function]

If the version of PHP in use supports [use function], the top-level functions
can be imported from the appropriate namespace and used directly:

```php
use function Eloquent\Phony\mock;

$handle = mock('ClassA');
```

#### Importing without [use function]

When [use function] is unavailable, the namespace itself can be imported, and an
alias can be used to make short references to the same functions:

```php
use Eloquent\Phony as x;

$handle = x\mock('ClassA');
```

#### Importing a static facade

A static facade implementation is also provided for those who prefer a more
traditional approach:

```php
use Eloquent\Phony\Phony;

$handle = Phony::mock('ClassA');
```

## Mocks

*Mocks* are objects that can be used as a substitute for another object. This
can be useful when a "real" object becomes difficult to use in a test.

### The mock API

<a name="facade.mock" />

----

> *[handle][mock-api]* [**mock**](#facade.mock)($types = []) *(with [use function])*<br />
> *[handle][mock-api]* x\\[**mock**](#facade.mock)($types = []) *(without [use function])*<br />
> *[handle][mock-api]* Phony::[**mock**](#facade.mock)($types = []) *(static)*

Create a new [full mock], and return a [stubbing handle].

*Each value in `$types` can be either a class name, or an [ad hoc mock]
definition. If only a single type is being mocked, the class name or definition
can be passed without being wrapped in an array.*

*See [Mocking basics].*

<a name="facade.partialMock" />

----

> *[handle][mock-api]* [**partialMock**](#facade.partialMock)($types = [], $arguments = []) *(with [use function])*<br />
> *[handle][mock-api]* x\\[**partialMock**](#facade.partialMock)($types = [], $arguments = []) *(without [use function])*<br />
> *[handle][mock-api]* Phony::[**partialMock**](#facade.partialMock)($types = [], $arguments = []) *(static)*

Create a new [partial mock], and return a [stubbing handle].

*Each value in `$types` can be either a class name, or an [ad hoc mock]
definition. If only a single type is being mocked, the class name or definition
can be passed without being wrapped in an array.*

*Omitting `$arguments` will cause the original constructor to be called with an
empty argument list. However, if a `null` value is supplied for `$arguments`,
the original constructor will not be called at all.*

*See [Partial mocks], [Calling a constructor manually].*

<a name="facade.on" />

----

> *[handle][mock-api]* [**on**](#facade.on)($mock) *(with [use function])*<br />
> *[handle][mock-api]* x\\[**on**](#facade.on)($mock) *(without [use function])*<br />
> *[handle][mock-api]* Phony::[**on**](#facade.on)($mock) *(static)*

Returns a [stubbing handle] for `$mock`.

<a name="facade.verify" />

----

> *[handle][mock-api]* [**verify**](#facade.verify)($mock) *(with [use function])*<br />
> *[handle][mock-api]* x\\[**verify**](#facade.verify)($mock) *(without [use function])*<br />
> *[handle][mock-api]* Phony::[**verify**](#facade.verify)($mock) *(static)*

Returns a [verification handle] for `$mock`.

<a name="facade.onStatic" />

----

> *[handle][mock-api]* [**onStatic**](#facade.onStatic)($class) *(with [use function])*<br />
> *[handle][mock-api]* x\\[**onStatic**](#facade.onStatic)($class) *(without [use function])*<br />
> *[handle][mock-api]* Phony::[**onStatic**](#facade.onStatic)($class) *(static)*

Returns a static [stubbing handle] for `$class`.

*See [Static mocks].*

<a name="facade.verifyStatic" />

----

> *[handle][mock-api]* [**verifyStatic**](#facade.verifyStatic)($class) *(with [use function])*<br />
> *[handle][mock-api]* x\\[**verifyStatic**](#facade.verifyStatic)($class) *(without [use function])*<br />
> *[handle][mock-api]* Phony::[**verifyStatic**](#facade.verifyStatic)($class) *(static)*

Returns a static [verification handle] for `$class`.

*See [Static mocks].*

<a name="handle.mock" />

----

> *mock* $handle->[**mock**](#handle.mock)()

Get the [mock].

*This method is not available on [static mock handles].*

*See [Mocking basics], [Mock handles].*

<a name="handle.__call" />

----

> *[stub][stub-api]* $handle->[**$method**](#handle.__call)(...$arguments) (on a [stubbing handle])<br />
> *fluent* $handle->[**$method**](#handle.__call)(...$arguments) (on a [verification handle])

**When called on a [stubbing handle]:**

Get a stub, and modify its current criteria to match the supplied arguments.

*This is equivalent to `$handle->$method->with(...$arguments)`.*

*This method supports [mock handle substitution].*

*See [Mocking basics], [Stubbing handles].*

**When called on a [verification handle]:**

Throws an exception unless the specified method was called with the supplied
arguments.

*This is equivalent to `$handle->$method->calledWith(...$arguments)`, except
that it returns `$handle`, allowing a fluent interface for multiple
verifications.*

*This method supports [mock handle substitution].*

*See [Verification handles].*

<a name="handle.stub" />
<a name="handle.__get" />

----

> *[stub][stub-api]* $handle->[**stub**](#handle.stub)($method, $isNewRule = true) or
> $handle->[**$method**](#handle.__get)

Get the [stub] for `$method`.

*If `false` is passed for `$isNewRule`, a new rule will not be started.*

*See [Mocking basics].*

<a name="handle.label" />

----

> *string* $handle->[**label**](#handle.label)()

Get the [label][labeling mocks].

*This method is not available on [static mock handles].*

<a name="handle.setLabel" />

----

> *fluent* $handle->[**setLabel**](#handle.setLabel)()

Set the [label][labeling mocks].

*This method is not available on [static mock handles].*

<a name="handle.construct" />

----

> *fluent* $handle->[**construct**](#handle.construct)(...$arguments)

Call the original constructor.

*This method does not support reference parameters.*

*This method is not available on [static mock handles].*

*See [Calling a constructor manually].*

<a name="handle.constructWith" />

----

> *fluent* $handle->[**constructWith**](#handle.constructWith)($arguments = [])

Call the original constructor.

*This method supports reference parameters.*

*This method is not available on [static mock handles].*

*See [Calling a constructor manually].*

<a name="handle.clazz" />

----

> *[ReflectionClass]* $handle->[**clazz**](#handle.clazz)()

Get the generated mock class.

<a name="handle.className" />

----

> *string* $handle->[**className**](#handle.className)()

Get the class name of the generated mock class.

<a name="handle.full" />

----

> *fluent* $handle->[**full**](#handle.full)()

Turn the mock into a [full mock].

<a name="handle.partial" />

----

> *fluent* $handle->[**partial**](#handle.partial)()

Turn the mock into a [partial mock].

<a name="handle.proxy" />

----

> *fluent* $handle->[**proxy**](#handle.proxy)()

Use the supplied object as the implementation for all methods of the mock.

*This method may help when partial mocking of a particular implementation is not
possible; as in the case of a final class.*

*See [Proxy mocks].*

<a name="handle.defaultAnswerCallback" />

----

> *callable* $handle->[**defaultAnswerCallback**](#handle.defaultAnswerCallback)()

Get the [default answer callback].

<a name="handle.setDefaultAnswerCallback" />

----

> *fluent* $handle->[**setDefaultAnswerCallback**](#handle.setDefaultAnswerCallback)($callback)

Set the [default answer callback] for all method stubs of this mock.

*This method accepts a callback that takes the stub as the first argument.*

<a name="handle.noInteraction" />

----

> *[verification][verification-api]* $handle->[**noInteraction**](#handle.noInteraction)()
> throws [AssertionException]

Throws an exception unless there was no interaction with the mock.

*See [Verifying that there was no interaction with a mock].*

<a name="handle.checkNoInteraction" />

----

> *[verification][verification-api]|null* $handle->[**checkNoInteraction**](#handle.checkNoInteraction)()

Checks if there was no interaction with the mock.

*See [Verifying that there was no interaction with a mock],
[Check verification].*

<a name="handle.stopRecording" />

----

> *fluent* $handle->[**stopRecording**](#handle.stopRecording)()

Stop recording calls.

*See [Pausing mock recording].*

<a name="handle.startRecording" />

----

> *fluent* $handle->[**startRecording**](#handle.startRecording)()

Start recording calls.

*See [Pausing mock recording].*

### The mock builder API

<a name="facade.mockBuilder" />

----

> *[builder][mock-builder-api]* [**mockBuilder**](#facade.mockBuilder)($types = []) *(with [use function])*<br />
> *[builder][mock-builder-api]* x\\[**mockBuilder**](#facade.mockBuilder)($types = []) *(without [use function])*<br />
> *[builder][mock-builder-api]* Phony::[**mockBuilder**](#facade.mockBuilder)($types = []) *(static)*

Create a new [mock builder].

*Each value in `$types` can be either a class name, or an [ad hoc mock]
definition. If only a single type is being mocked, the class name or definition
can be passed without being wrapped in an array.*

<a name="builder.__clone" />

----

> *[builder][mock-builder-api]* [**clone**](#builder.__clone) $builder

Copy an existing mock builder.

*See [Copying mock builders].*

<a name="builder.types" />

----

> *array\<string,[ReflectionClass]>* $builder->[**types**](#builder.types)()

Get the types that will be mocked.

*Returns a map of class name to class.*

<a name="builder.like" />

----

> *fluent* $builder->[**like**](#builder.like)($type, ...$additionalTypes)

Add classes, interfaces, or traits.

*Each type value can be either a class name, or an [ad hoc mock] definition.*

<a name="builder.addMethod" />

----

> *fluent* $builder->[**addMethod**](#builder.addMethod)($name, $callback = null)

Add a custom method.

*See [Customizing the mock class].*

<a name="builder.addProperty" />

----

> *fluent* $builder->[**addProperty**](#builder.addProperty)($name, $value = null)

Add a custom property.

*See [Customizing the mock class].*

<a name="builder.addStaticMethod" />

----

> *fluent* $builder->[**addStaticMethod**](#builder.addStaticMethod)($name, $callback = null)

Add a custom static method.

*See [Customizing the mock class].*

<a name="builder.addStaticProperty" />

----

> *fluent* $builder->[**addStaticProperty**](#builder.addStaticProperty)($name, $value = null)

Add a custom static property.

*See [Customizing the mock class].*

<a name="builder.addConstant" />

----

> *fluent* $builder->[**addConstant**](#builder.addConstant)($name, $value = null)

Add a custom class constant.

*See [Customizing the mock class].*

<a name="builder.named" />

----

> *fluent* $builder->[**named**](#builder.named)($className = null)

Set the class name.

*See [Customizing the mock class].*

<a name="builder.isFinalized" />

----

> *boolean* $builder->[**isFinalized**](#builder.isFinalized)()

Returns `true` if this builder is finalized.

<a name="builder.finalize" />

----

> *fluent* $builder->[**finalize**](#builder.finalize)()

Finalize the mock builder.

<a name="builder.isBuilt" />

----

> *boolean* $builder->[**isBuilt**](#builder.isBuilt)()

Returns `true` if the mock class has been built.

<a name="builder.build" />

----

> *[ReflectionClass]* $builder->[**build**](#builder.build)($createNew = false)

Generate and define the mock class.

*Calling this method will finalize the mock builder.*

*See [Generating mock classes from a builder].*

<a name="builder.className" />

----

> *string* $builder->[**className**](#builder.className)()

Generate and define the mock class, and return the class name.

*Calling this method will finalize the mock builder.*

*See [Generating mock classes from a builder].*

<a name="builder.get" />

----

> *mock* $builder->[**get**](#builder.get)()

Get a mock.

*This method will return the last created mock, only creating a new mock if no
existing mock is available.*

*If no existing mock is available, the created mock will be a full mock.*

*Calling this method will finalize the mock builder.*

*See [Creating mocks from a builder].*

<a name="builder.full" />

----

> *mock* $builder->[**full**](#builder.full)()

Create a new [full mock].

*This method will always create a new mock.*

*Calling this method will finalize the mock builder.*

*See [Creating mocks from a builder].*

<a name="builder.partial" />

----

> *mock* $builder->[**partial**](#builder.partial)(...$arguments)

Create a new [partial mock].

*The constructor will be called with `$arguments`.*

*This method will always create a new mock.*

*Calling this method will finalize the mock builder.*

*This method does not support reference parameters.*

*See [Creating mocks from a builder].*

<a name="builder.partialWith" />

----

> *mock* $builder->[**partialWith**](#builder.partialWith)($arguments = [], $label = null)

Create a new [partial mock].

*The constructor will be called with `$arguments`, unless `$arguments` is
`null`, in which case the constructor will not be called at all.*

*This method will always create a new mock.*

*Calling this method will finalize the mock builder.*

*This method supports reference parameters.*

*See [Creating mocks from a builder].*

### Mocking basics

Any class, interface, or trait can be mocked. To create a mock, use
[`mock()`](#facade.mock):

```php
$handle = mock('ClassA');        // with `use function`
$handle = x\mock('ClassA');      // without `use function`
$handle = Phony::mock('ClassA'); // static
```

The object returned by [`mock()`](#facade.mock) is **not** the mock object
itself, but a [mock handle]. This handle provides a [stub] for each method of
the type being mocked. Each stub is exposed as a [property](#handle.__get) of
the same name as the stubbed method, and implements both [the stub API], and
[the spy API]:

```php
// stubbing
$handle->methodA->returns('1337 h4x');
$handle->methodB->with('bad', 'input')->throws('You done goofed.');

// verification
$handle->methodA->calledWith('swiggity', 'swooty');
$handle->methodB->called();
```

The mock handle returned by [`mock()`](#facade.mock) is a type of handle called
a [stubbing handle], which means it implements magic
[`__call()`](#handle.__call) methods that are equivalent to a call to
[`with()`](#stub.with):

```php
// these two statements are equivalent
$handle->methodA('a', 'b')->returns('c');
$handle->methodA->with('a', 'b')->returns('c');
```

To access the actual mock object, call the [`mock()`](#handle.mock) method of
the handle:

```php
$mock = $handle->mock();
```

### Partial mocks

*Phony* supports "partial mocks", or mocks that do not override methods by
default. To create a partial mock, use [`partialMock()`](#facade.partialMock):

```php
$handle = partialMock('ClassA');        // with `use function`
$handle = x\partialMock('ClassA');      // without `use function`
$handle = Phony::partialMock('ClassA'); // static
```

Constructor arguments can be passed to [`partialMock()`](#facade.partialMock) as
the second parameter:

```php
$handle = partialMock('ClassA', ['argumentA', 'argumentB']);
```

### Proxy mocks

In cases where direct mocking is not possible, such as with `final` classes and
methods, *Phony* offers an alternative strategy in the form of "proxy" mocks.
Any mock can proxy methods on to any other object by using
[`proxy()`](#handle.proxy):

```php
interface Animal
{
    public function speak();
}

final class Cat implements Animal
{
    final public function speak()
    {
        return 'Meow meow meow? Meow.';
    }
}

function listen(Animal $animal)
{
    echo 'It said: ' . $animal->speak();
}

$cat = new Cat();

$handle = mock('Animal'); // a generic animal mock
$handle->proxy($cat);     // now it behaves exactly like `$cat`

listen($handle->mock());  // outputs 'It said: Meow meow meow? Meow.'
```

The [`proxy()`](#handle.proxy) method is also fluent, meaning that mock creation
and proxying can be done in a single expression:

```php
$handle = mock('Animal')->proxy(new Cat());
```

### Mocking multiple types

Multiple interfaces and/or traits can be mocked simultaneously by passing an
array of types to [`mock()`](#facade.mock) or
[`partialMock()`](#facade.partialMock):

```php
$handle = mock(['InterfaceA', 'InterfaceB', 'TraitA']);        // with `use function`
$handle = x\mock(['InterfaceA', 'InterfaceB', 'TraitA']);      // without `use function`
$handle = Phony::mock(['InterfaceA', 'InterfaceB', 'TraitA']); // static
```

A single base class may also be mocked with other types:

```php
$handle = mock(['ClassA', 'InterfaceA', 'TraitA']);        // with `use function`
$handle = x\mock(['ClassA', 'InterfaceA', 'TraitA']);      // without `use function`
$handle = Phony::mock(['ClassA', 'InterfaceA', 'TraitA']); // static
```

### Ad hoc mocks

*Phony* supports the creation of mock objects with methods and/or properties
that are not pre-defined in some other class, interface, or trait. It does so
using special "definition" values, which can be passed to
[`mock()`](#facade.mock) or [`partialMock()`](#facade.partialMock):

```php
$handle = partialMock(
    [
        '__toString' => function () {
            return 'What is this sorcery?';
        },
        '__call' => function ($name, array $arguments) {
            return sprintf('%s(%s)', $name, implode(', ', $arguments));
        },
    ]
);

$mock = $handle->mock();

echo $mock;              // outputs 'What is this sorcery?'
echo $mock->a('b', 'c'); // outputs 'a(b, c)'
```

Definition values can be mocked at the same time as regular classes, traits, and
interfaces. Multiple definitions can even be mocked at the same time. Simply
specify all types to be mocked in a single array:

```php
$handle = partialMock(
    [
        'Countable',
        [
            '__invoke' => function () {
                return 'You called me?';
            }
        ],
        [
            '__toString' => function () {
                return 'Are you stringing me along?';
            }
        ],
    ]
);

$mock = $handle->mock();
$mock->count->returns(111);

echo $mock();      // outputs 'You called me?'
echo $mock;        // outputs 'Are you stringing me along?'
echo count($mock); // outputs '111'
```

#### Ad hoc definition values

Ad hoc definition values support methods, properties, and constants. By default,
callback functions will be converted to methods, and everything else will become
a property:

```php
$handle = partialMock(
    [
        'a' => function () {
            return 'A is for apple.';
        },
        'b' => 'B is for banana.',
    ]
);

$mock = $handle->mock();

echo $mock->a(); // outputs 'A is for apple.'
echo $mock->b;   // outputs 'B is for banana.'
```

To override the default treatment of values, or to define static methods or
properties, keywords can be added to the keys of the definition value:

```php
$handle = partialMock(
    [
        'const A' => 'A is for apple.',
        'static b' => function () {
            return 'B is for banana.';
        },
        'static c' => 'C is for cat.',
        'var d' => function () {
            return 'D is for dog.';
        },
        'static var e' => function () {
            return 'E is for egg.';
        },
        'function f' => 'implode',
    ]
);

$mock = $handle->mock();
$class = get_class($mock);

echo $class::A;   // outputs 'A is for apple.'
echo $class::b(); // outputs 'B is for banana.'
echo $class::c;   // outputs 'C is for cat.'

echo var_dump(isset($mock->d));  // outputs 'bool(true)'
echo var_dump(isset($class::e)); // outputs 'bool(true)'

echo $mock->f(', ', ['a', 'b']); // outputs 'a, b'
```

#### Ad hoc definition magic "self" values

Methods defined in an [ad hoc mock] definition can take advantage of
[magic "self" values]. When stubs are retrieved from a mock, their [self value]
is automatically set to the mock itself.

Any custom method with a first parameter named `$phonySelf`, regardless of the
parameter's type, will receive the self value as the first argument. This self
value can be used in place of `$this` to access instance state, and/or implement
fluent interfaces:

```php
$handle = partialMock(
    [
        'set' => function ($phonySelf, $key, $value) {
            $phonySelf->values[$key] = $value;

            return $phonySelf;
        },
        'get' => function ($phonySelf, $key) {
            return $phonySelf->values[$key] ?? null;
        },
        'values' => [],
    ]
);

$mock = $handle->mock();

echo $mock->set('a', 1)->get('a'); // outputs '1'
```

### Static mocks

*Phony* can be used to stub the behavior of static methods of generated mock
classes. To modify the behavior of a static method, use
[`onStatic()`](#facade.onStatic) to obtain a static stubbing handle from either
an existing handle, or a mock instance:

```php
$handle = mock('DateTime');
$mock = $handle->mock();

$static = onStatic($handle);        // with `use function`
$static = x\onStatic($handle);      // without `use function`
$static = Phony::onStatic($handle); // static

$static = onStatic($mock);        // with `use function`
$static = x\onStatic($mock);      // without `use function`
$static = Phony::onStatic($mock); // static
```

This static handle is just like a normal [stubbing handle], except that it
refers to static methods instead of instance methods:

```php
$static->createFromFormat->returns(new DateTime('2001-02-03T04:05:06Z'));

$class = $static->className(); // obtain the generated class name

$date = $class::createFromFormat('format', 'time');
echo $date->format('c'); // outputs '2001-02-03T04:05:06+00:00'
```

The static handle can also be used to verify interactions with static methods:

```php
$static->createFromFormat->calledWith('format', 'time');
```

There is also a static variant of the normal [verification handle], which can
be created from either an existing handle, or a mock instance, using
[`verifyStatic()`](#facade.verifyStatic):

```php
$handle = mock('DateTime');
$mock = $handle->mock();

$static = verifyStatic($handle);        // with `use function`
$static = x\verifyStatic($handle);      // without `use function`
$static = Phony::verifyStatic($handle); // static

$static = verifyStatic($mock);        // with `use function`
$static = x\verifyStatic($mock);      // without `use function`
$static = Phony::verifyStatic($mock); // static
```

### Calling a constructor manually

In order to stub methods called in the constructor of a partial mock, it is
necessary to defer construction of the mock object. To accomplish this, pass
`null` as the second argument to [`partialMock()`](#facade.partialMock), which
will cause *Phony* to bypass the constructor:

```php
$handle = partialMock('ClassA', null);
```

Note that omitting the second argument will **not** have the same effect as
explicitly passing `null`.

Behavior can then be defined before the constructor is called:

```php
$handle->methodA->returns(true);
```

Finally, the constructor can be manually called using
[`construct()`](#handle.construct), or
[`constructWith()`](#handle.constructWith):

```php
$handle->construct('argumentA', 'argumentB');       // variable arguments
$handle->constructWith(['argumentA', 'argumentB']); // array arguments
```

The [`constructWith()`](#handle.constructWith) additionally supports arguments
passed by reference:

```php
$a = null;
$b = null;

$handle->constructWith([&$a, &$b]);
```

### Labeling mocks

Every mock has a label, which is a free-form string used to help identify the
mock in verification failure messages. By default, each mock is assigned a
unique sequential integer label upon creation.

The label can be changed at any time by using [`setLabel()`](#handle.setLabel)
on any mock handle associated with the mock:

```php
$handle = mock('ClassA');
$mock = $handle->mock();

$handle->setLabel('a');

echo $handle->label(); // outputs 'a'
echo on($mock)->label(); // outputs 'a'
```

The [`setLabel()`](#handle.setLabel) method is also fluent, meaning that mock
creation and label setting can be done in a single expression:

```php
$mock = mock('ClassA')->setLabel('a')->mock();
```

When a verification fails for a labeled mock, the output is similar to the
following:

    Expected call on ClassA[label]->methodA with arguments like:
        "x", "y"
    Calls:
        - "x", "z"

### Mock handles

Mock handles come in two varieties. [Stubbing handles] are the default type
returned when using [`mock()`](#facade.mock), but there are also
[verification handles] that are designed to make multiple verifications easier.

Despite their names, both types of handles provide stubs that implement both
[the stub API] for stubbing, and [the spy API] for verification. The difference
is *only* in the handle's implementation of the magic
[`__call()`](#handle.__call) methods. These magic methods are tailored for
convenience when stubbing or verifying, depending on which type of handle is in
use.

#### Stubbing handles

Stubbing handles are the default handle type. They are returned when
[`mock()`](#facade.mock) or [`partialMock()`](#facade.partialMock) is called:

```php
$stubbingHandle = mock('ClassA');
$stubbingHandle = partialMock('ClassA');
```

They can also be retrieved at any time from a mock instance, or another handle,
by using [`on()`](#facade.on):

```php
$stubbingHandle = on($mock);        // with `use function`
$stubbingHandle = x\on($mock);      // without `use function`
$stubbingHandle = Phony::on($mock); // static

$stubbingHandle = on($otherHandle);        // with `use function`
$stubbingHandle = x\on($otherHandle);      // without `use function`
$stubbingHandle = Phony::on($otherHandle); // static
```

To access the actual mock object, call the [`mock()`](#handle.mock) method of
the handle:

```php
$mock = $stubbingHandle->mock();
```

Stubbing handles implement magic [`__call()`](#handle.__call) methods that are
equivalent to a call to [`with()`](#stub.with):

```php
// these two statements are equivalent
$stubbingHandle->methodA('a', 'b')->returns('c');
$stubbingHandle->methodA->with('a', 'b')->returns('c');
```

This provides the most convenience when setting up mock behaviors.

Note that a static variant of the stubbing handle exists. See [Static mocks].

#### Verification handles

Verification handles are a type of handle that provides the ability to make
verifications using a fluent interface.

A verification handle can also be created at any time from a mock instance, or
another handle, by using [`verify()`](#facade.verify):

```php
$verificationHandle = verify($mock);        // with `use function`
$verificationHandle = x\verify($mock);      // without `use function`
$verificationHandle = Phony::verify($mock); // static

$verificationHandle = verify($otherHandle);        // with `use function`
$verificationHandle = x\verify($otherHandle);      // without `use function`
$verificationHandle = Phony::verify($otherHandle); // static
```

To access the actual mock object, call the [`mock()`](#handle.mock) method of
the handle:

```php
$mock = $verificationHandle->mock();
```

Verification handles implement magic [`__call()`](#handle.__call) methods that
are equivalent to a call to [`calledWith()`](#stub.calledWith):

```php
// these two statements are equivalent
$verificationHandle->methodA('a', 'b');
$verificationHandle->methodA->calledWith('a', 'b');
```

These magic methods can be used in a fluent manner to perform multiple
verifications easily:

```php
$verificationHandle
    ->methodA('a', 'b')
    ->methodB('c', 'd')
    ->methodC('e', 'f');

// the above is equivalent to these statements:
$verificationHandle->methodA->calledWith('a', 'b');
$verificationHandle->methodB->calledWith('c', 'd');
$verificationHandle->methodC->calledWith('e', 'f');
```

Note that this does not verify the order of events. If order is important, use
[order verification].

Note that a static variant of the verification handle exists. See
[Static mocks].

#### Mock handle substitution

*Phony* will sometimes accept a mock handle as equivalent to the mock it
represents. This simplifies some common mocking scenarios, and improves test
readability.

One such scenario is returning a [mock] from another [stub] \(this includes
stubbed mock methods). Returning a handle from a stub is equivalent to returning
the mock itself:

```php
$database = mock('Database');
$result = mock('Result');

// these two statements are equivalent
$database->select->returns($result);
$database->select->returns($result->mock());
```

Another common situation is the use of a mock handle when
[matching stub arguments]. Use of a mock handle in a argument list is equivalent
to use of the mock itself:

```php
$database = mock('Database');
$query = mock('Query');
$result = mock('Result');

// these two statements are equivalent
$database->select($query)->returns($result);
$database->select($query->mock())->returns($result);
```

The same is true when [verifying that a spy was called with specific arguments]:

```php
$database = mock('Database');
$query = mock('Query');

// these two statements are equivalent
$database->select->calledWith($query);
$database->select->calledWith($query->mock());
```

There are other edge-case situations where *Phony* will exhibit this behaviour.
Refer to the API documentation for more detailed information.

### Mock builders

Mock builders provide an alternative method for defining and creating mocks,
when more fine-grained control is desired. To create a mock builder, use
[`mockBuilder()`](#facade.mockBuilder):

```php
$builder = mockBuilder();        // with `use function`
$builder = x\mockBuilder();      // without `use function`
$builder = Phony::mockBuilder(); // static
```

Types to mock can be passed directly to [`mockBuilder()`](#facade.mockBuilder)
in a similar fashion to [`mock()`](#facade.mock):

```php
$builder = mockBuilder(['ClassA', 'InterfaceA']);
```

#### Customizing the mock class

Mock builders implement a fluent interface, with many methods for customizing
the generated mock class:

```php
$builder
    ->like('ClassA', 'InterfaceA')
    ->named('CustomClassName')
    ->addMethod(
        'methodA',
        function ($argumentA, &$argumentB) {
            // ...
        }
    )
    ->addProperty('propertyA', 'a')
```

This is only a small example of what is possible. For a full list of the
available methods, see [the mock builder API].

#### Creating mocks from a builder

Once the builder is configured, there are several options for creating mock
instances. All of these will internally "finalize" the mock builder, and no
further customizations can be made.

Use [`full()`](#builder.full) to create a new full mock instance:

```php
$mock = $builder->full();
```

Use [`partial()`](#builder.partial) to create a new partial mock instance:

```php
$mock = $builder->partial();
```

Constructor arguments can also be passed to [`partial()`](#builder.partial):

```php
$mock = $builder->partial('a', 'b');
```

There is also a more advanced method, [`partialWith()`](#builder.partialWith),
that accepts arguments passed by reference:

```php
$a = null;
$b = null;

$mock = $builder->partialWith([&$a, &$b]);
```

All of the above methods for creating mock instances store the last created mock
instance on the builder. To retrieve the last created mock instance, use
[`get()`](#builder.get):

```php
$mockA = $builder->full();
$mockB = $builder->get();

echo $mockA === $mockB ? 'true' : 'false'; // outputs 'true'
```

If no mock instance already exists, [`get()`](#builder.get) will create a new
full mock instance, and return it.

Note that unlike using [`mock()`](#facade.mock), these methods do not
automatically wrap the returned mock in a [mock handle]. To obtain a stubbing
handle, use [`on()`](#facade.on):

```php
$mock = $builder->mock();
$handle = on($mock);
```

#### Generating mock classes from a builder

The mock class can be generated without actually creating a mock instance. To
generate and return a mock class, use [`build()`](#builder.build), (which
returns a [ReflectionClass]):

```php
$class = $builder->build();
```

The [`build()`](#builder.build) method will normally return the same class for
subsequent calls. To generate a new class each time, pass `true` as the first
argument:

```php
$classA = $builder->build(true);
$classB = $builder->build(true);
```

Note that this is not possible when a custom class name has been set.

If only the class *name* is required, [`className()`](#builder.className) will
generate the mock class, and return the class name as a string:

```php
$className = $builder->className();
```

All of the above methods will internally "finalize" the mock builder, and no
further customizations can be made.

#### Copying mock builders

Mock builders can be copied by using the [`clone`](#builder.__clone) operator:

```php
$builderA = mockBuilder();
$builderB = clone $builderA;
```

A copied mock builder can be modified even if the original mock builder is
"finalized":

```php
$countableBuilder = mockBuilder('Countable');
$countable = $countableBuilder->get();

$countableIteratorBuilder = clone $countableBuilder;
$countableIteratorBuilder->like('Iterator');
$countableIterator = $countableIteratorBuilder->get();

echo $countable instanceof Countable ? 'true' : 'false';         // outputs 'true'
echo $countableIterator instanceof Countable ? 'true' : 'false'; // outputs 'true'

echo $countable instanceof Iterator ? 'true' : 'false';         // outputs 'false'
echo $countableIterator instanceof Iterator ? 'true' : 'false'; // outputs 'true'
```

The copied mock builder will also ignore any mock instances generated by the
original builder:

```php
$builderA = mockBuilder();
$mockA = $builderA->get();

$builderB = clone $builderA;
$mockB = $builderB->get();

echo $mockA === $mockB ? 'true' : 'false'; // outputs 'false'
```

### Pausing mock recording

To temporarily prevent an entire mock from recording calls, use
[`stopRecording()`](#handle.stopRecording) and
[`startRecording()`](#handle.startRecording) as necessary:

```php
$handle = mock(
    [
        'methodA' => function () {},
    ]
);
$mock = $handle->mock();

$mock->methodA('a');
$handle->stopRecording();
$mock->methodA('b');
$handle->startRecording();
$mock->methodA('c');

$handle->methodA->calledWith('a'); // passes
$handle->methodA->calledWith('c'); // passes

$handle->methodA->calledWith('b'); // fails
```

### Terminology

By some popular definitions, *Phony*'s mocks are not technically mocks at all,
because they do not implement "expectations". According to these definitions,
*Phony*'s mocks would be more correctly called stubs.

However, throughout *Phony*'s API and documentation, the term "mock" is used to
refer to any test double that is an object. The term "stub" is used to refer to
a callable that can be programmed to provide canned answers to incoming calls.

## Stubs

*Stubs* are callable entities that can be configured to behave according to a
set of rules when called. In *Phony*, every stub also implements [the spy API].

### The stub API

<a name="facade.stub" />

----

> *[stub][stub-api]* [**stub**](#facade.stub)($callback = null) *(with [use function])*<br />
> *[stub][stub-api]* x\\[**stub**](#facade.stub)($callback = null) *(without [use function])*<br />
> *[stub][stub-api]* Phony::[**stub**](#facade.stub)($callback = null) *(static)*

Create a new [stub].

*See [Stubbing an existing callable], [Anonymous stubs].*

<a name="stub.invoke" />
<a name="stub.__invoke" />

----

> *mixed* $stub->[**invoke**](#stub.invoke)(...$arguments) or
> [**$stub(...$arguments)**](#stub.__invoke)
> throws [Exception], [Error]

Invoke the stub, record the call, and return or throw the result.

*This method does not support reference parameters.*

*See [Invoking spies].*

<a name="stub.invokeWith" />

----

> *mixed* $stub->[**invokeWith**](#stub.invokeWith)($arguments)
> throws [Exception], [Error]

Invoke the stub, record the call, and return or throw the result.

*This method supports reference parameters.*

*See [Invoking spies].*

<a name="stub.label" />

----

> *string|null* $stub->[**label**](#stub.label)()

Get the [label][labeling spies].

<a name="stub.setLabel" />

----

> *fluent* $stub->[**setLabel**](#stub.setLabel)($label)

Set the [label][labeling spies].

<a name="stub.self" />

----

> *mixed* $stub->[**self**](#stub.self)()

Get the [self value] of this stub.

<a name="stub.setSelf" />

----

> *fluent* $stub->[**setSelf**](#stub.setSelf)($self)

Set the [self value] of this stub.

<a name="stub.defaultAnswerCallback" />

----

> *callable* $stub->[**defaultAnswerCallback**](#stub.defaultAnswerCallback)()

Get the [default answer callback].

<a name="stub.setDefaultAnswerCallback" />

----

> *fluent* $stub->[**setDefaultAnswerCallback**](#stub.setDefaultAnswerCallback)($callback)

Set the [default answer callback] of this stub.

*This method accepts a callback that takes the stub as the first argument.*

<a name="stub.with" />

----

> *fluent* $stub->[**with**](#stub.with)(...$arguments)

Modify the current criteria to match the supplied arguments.

*This method supports [mock handle substitution].*

*See [Matching stub arguments].*

<a name="stub.calls" />

----

> *fluent* $stub->[**calls**](#stub.calls)($callback, ...$additionalCallbacks)

Add callbacks to be called as part of an answer.

*Note that all supplied callbacks will be called in the same invocation.*

*See [Invoking callables].*

<a name="stub.callsWith" />

----

> *fluent* $stub->[**callsWith**](#stub.callsWith)($callback, $arguments = [], $prefixSelf = false, $suffixArgumentsObject = false, $suffixArguments = true)

Add a callback to be called as part of an answer.

*Note that all supplied callbacks will be called in the same invocation.*

*This method supports [mock handle substitution]. Any mock handles in
`$arguments` are equivalent to the mocks themselves.*

*See [Invoking callables].*

<a name="stub.callsArgument" />

----

> *fluent* $stub->[**callsArgument**](#stub.callsArgument)($index = 0, ...$additionalIndices)

Add argument callbacks to be called as part of an answer.

*Negative indices are offset from the end of the list. That is, `-1` indicates
the last element, and `-2` indicates the second last element.*

*Note that all supplied callbacks will be called in the same invocation.*

*See [Invoking arguments].*

<a name="stub.callsArgumentWith" />

----

> *fluent* $stub->[**callsArgumentWith**](#stub.callsArgumentWith)($index = 0, $arguments = [], $prefixSelf = false, $suffixArgumentsObject = false, $suffixArguments = true)

Add an argument callback to be called as part of an answer.

*Negative indices are offset from the end of the list. That is, `-1` indicates
the last element, and `-2` indicates the second last element.*

*This method supports reference parameters.*

*Note that all supplied callbacks will be called in the same invocation.*

*This method supports [mock handle substitution]. Any mock handles in
`$arguments` are equivalent to the mocks themselves.*

*See [Invoking arguments].*

<a name="stub.setsArgument" />

----

> *fluent* $stub->[**setsArgument**](#stub.setsArgument)($indexOrValue = null, $value = null)

Set the value of an argument passed by reference as part of an answer.

*If called with no arguments, sets the first argument to `null`.*

*If called with one argument, sets the first argument to `$indexOrValue`.*

*If called with two arguments, sets the argument at `$indexOrValue` to
`$value`.*

*This method supports [mock handle substitution] of the value.*

*See [Setting passed-by-reference arguments].*

<a name="stub.does" />

----

> *fluent* $stub->[**does**](#stub.does)($callback, ...$additionalCallbacks)

Add callbacks as answers.

*See [Using a callable as an answer].*

<a name="stub.doesWith" />

----

> *fluent* $stub->[**doesWith**](#stub.doesWith)($callback, $arguments = [], $prefixSelf = false, $suffixArgumentsObject = false, $suffixArguments = true)

Add a callback as an answer.

*The supplied arguments support reference parameters.*

*This method supports [mock handle substitution]. Any mock handles in
`$arguments` are equivalent to the mocks themselves.*

*See [Using a callable as an answer].*

<a name="stub.forwards" />

----

> *fluent* $stub->[**forwards**](#stub.forwards)($arguments = [], $prefixSelf = false, $suffixArgumentsObject = false, $suffixArguments = true)

Add an answer that calls the wrapped callback.

*The supplied arguments support reference parameters.*

*This method supports [mock handle substitution]. Any mock handles in
`$arguments` are equivalent to the mocks themselves.*

*See [Forwarding to the original callable].*

<a name="stub.returns" />

----

> *fluent* $stub->[**returns**](#stub.returns)($value = null, ...$additionalValues)

Add answers that return values.

*This method supports [mock handle substitution].*

*See [Returning values].*

<a name="stub.returnsArgument" />

----

> *fluent* $stub->[**returnsArgument**](#stub.returnsArgument)($index = 0)

Add an answer that returns an argument.

*Negative indices are offset from the end of the list. That is, `-1` indicates
the last element, and `-2` indicates the second last element.*

*See [Returning arguments].*

<a name="stub.returnsSelf" />

----

> *fluent* $stub->[**returnsSelf**](#stub.returnsSelf)()

Add an answer that returns the self value.

*See [Returning the "self" value], [Stub "self" values].*

<a name="stub.throws" />

----

> *fluent* $stub->[**throws**](#stub.throws)($exception = null, ...$additionalExceptions)

Add answers that throw exceptions.

*This method supports [mock handle substitution]. Any supplied exception mock
handles are equivalent to the mocks themselves.*

*See [Throwing exceptions].*

<a name="stub.generates" />

----

> *[generator-answer][generator-answer-api]* $stub->[**generates**](#stub.generates)($values = [])

Add an answer that returns a generator, and return the answer for further
behavior customization.

*Any supplied `$values` will be yielded from the resulting generator.*

*See [Stubbing generators], [Yielding from a generator].*

<a name="stub.useGeneratorSpies" />

----

> *boolean* $stub->[**useGeneratorSpies**](#stub.useGeneratorSpies)()

Returns `true` if this stub uses [generator spies].

<a name="stub.setUseGeneratorSpies" />

----

> *fluent* $stub->[**setUseGeneratorSpies**](#stub.setUseGeneratorSpies)($useGeneratorSpies)

Turn on or off the use of [generator spies].

<a name="stub.useTraversableSpies" />

----

> *boolean* $stub->[**useTraversableSpies**](#stub.useTraversableSpies)()

Returns `true` if this stub uses [traversable spies].

<a name="stub.setUseTraversableSpies" />

----

> *fluent* $stub->[**setUseTraversableSpies**](#stub.setUseTraversableSpies)($useTraversableSpies)

Turn on or off the use of [traversable spies].

<a name="stub.stopRecording" />

----

> *fluent* $stub->[**stopRecording**](#stub.stopRecording)()

Stop recording calls.

*See [Pausing spy recording].*

<a name="stub.startRecording" />

----

> *fluent* $stub->[**startRecording**](#stub.startRecording)()

Start recording calls.

*See [Pausing spy recording].*

<a name="stub.arguments" />

----

> *[arguments][arguments-api]* $stub->[**arguments**](#stub.arguments)()
> throws [UndefinedCallException]

Get the arguments of the first call.

<a name="stub.argument" />

----

> *mixed* $stub->[**argument**](#stub.argument)($index = 0)
> throws [UndefinedCallException], [UndefinedArgumentException]

Get the argument of the first call at `$index`.

<a name="stub.hasCalls" />

----

> *boolean* $stub->[**hasCalls**](#stub.hasCalls)()

Returns `true` if any calls were recorded.

<a name="stub.callCount" />

----

> *integer* $stub->[**callCount**](#stub.callCount)()

Get the number of calls.

<a name="stub.allCalls" />

----

> *array\<[call][call-api]>* $stub->[**allCalls**](#stub.allCalls)()

Get all calls as an array.

<a name="stub.firstCall" />

----

> *[call][call-api]* $stub->[**firstCall**](#stub.firstCall)()
> throws [UndefinedCallException]

Get the first call.

<a name="stub.lastCall" />

----

> *[call][call-api]* $stub->[**lastCall**](#stub.lastCall)()
> throws [UndefinedCallException]

Get the last call.

<a name="stub.callAt" />

----

> *[call][call-api]* $stub->[**callAt**](#stub.callAt)($index = 0)
> throws [UndefinedCallException]

Get the call at `$index`.

*Negative indices are offset from the end of the list. That is, `-1` indicates
the last element, and `-2` indicates the second last element.*

<a name="stub.hasEvents" />

----

> *boolean* $stub->[**hasEvents**](#stub.hasEvents)()

Returns `true` if any events were recorded.

<a name="stub.eventCount" />

----

> *integer* $stub->[**eventCount**](#stub.eventCount)()

Get the number of events.

<a name="stub.allEvents" />

----

> *array\<[event][event-api]>* $stub->[**allEvents**](#stub.allEvents)()

Get all events as an array.

<a name="stub.firstEvent" />

----

> *[event][event-api]* $stub->[**firstEvent**](#stub.firstEvent)()
> throws [UndefinedEventException]

Get the first event.

<a name="stub.lastEvent" />

----

> *[event][event-api]* $stub->[**lastEvent**](#stub.lastEvent)()
> throws [UndefinedEventException]

Get the last event.

<a name="stub.eventAt" />

----

> *[event][event-api]* $stub->[**eventAt**](#stub.eventAt)($index = 0)
> throws [UndefinedEventException]

Get the event at `$index`.

*Negative indices are offset from the end of the list. That is, `-1` indicates
the last element, and `-2` indicates the second last element.*

<a name="stub.called" />

----

> *[verification][verification-api]* $stub->[**called**](#stub.called)()
> throws [AssertionException]

Throws an exception unless called.

*See [Verifying that a call was made].*

<a name="stub.checkCalled" />

----

> *[verification][verification-api]|null* $stub->[**checkCalled**](#stub.checkCalled)()

Checks if called.

*See [Verifying that a call was made], [Check verification].*

<a name="stub.calledWith" />

----

> *[verification][verification-api]* $stub->[**calledWith**](#stub.calledWith)(...$arguments)
> throws [AssertionException]

Throws an exception unless called with the supplied arguments.

*This method supports [mock handle substitution]. Any mock handles in
`$arguments` are equivalent to the mocks themselves.*

*See [Verifying that a spy was called with specific arguments].*

<a name="stub.checkCalledWith" />

----

> *[verification][verification-api]|null* $stub->[**checkCalledWith**](#stub.checkCalledWith)(...$arguments)

Checks if called with the supplied arguments.

*This method supports [mock handle substitution]. Any mock handles in
`$arguments` are equivalent to the mocks themselves.*

*See [Verifying that a spy was called with specific arguments],
[Check verification].*

<a name="stub.calledOn" />

----

> *[verification][verification-api]* $stub->[**calledOn**](#stub.calledOn)($value)
> throws [AssertionException]

Throws an exception unless the bound `$this` value is equal to the supplied
value.

*This method supports [mock handle substitution].*

*See [Verifying spy closure binding].*

<a name="stub.checkCalledOn" />

----

> *[verification][verification-api]|null* $stub->[**checkCalledOn**](#stub.checkCalledOn)($value)

Checks if the bound `$this` value is equal to the supplied value.

*This method supports [mock handle substitution].*

*See [Verifying spy closure binding], [Check verification].*

<a name="stub.returned" />

----

> *[verification][verification-api]* $stub->[**returned**](#stub.returned)($value = null)
> throws [AssertionException]

Throws an exception unless this stub returned the supplied value.

*When called with no arguments, this method simply checks that the stub returned
any value.*

*This method supports [mock handle substitution].*

*See [Verifying spy return values].*

<a name="stub.checkReturned" />

----

> *[verification][verification-api]|null* $stub->[**checkReturned**](#stub.checkReturned)($value = null)

Checks if this stub returned the supplied value.

*When called with no arguments, this method simply checks that the stub returned
any value.*

*This method supports [mock handle substitution].*

*See [Verifying spy return values], [Check verification].*

<a name="stub.threw" />

----

> *[verification][verification-api]* $stub->[**threw**](#stub.threw)($type = null)
> throws [AssertionException]

Throws an exception unless this stub threw an exception of the supplied type.

*When called with no arguments, this method simply checks that the stub threw
any exception.*

*When called with a string, this method checks that the stub threw an exception
that is an instance of `$type`.*

*When called with an exception instance, this method checks that the stub threw
an exception that is equal to the supplied instance.*

*When called with a [matcher], this method checks that the stub threw an
exception that matches the supplied matcher.*

*This method supports [mock handle substitution].*

*See [Verifying spy exceptions].*

<a name="stub.checkThrew" />

----

> *[verification][verification-api]|null* $stub->[**checkThrew**](#stub.checkThrew)($type = null)

Checks if an exception of the supplied type was thrown.

*When called with no arguments, this method simply checks that the stub threw
any exception.*

*When called with a string, this method checks that the stub threw an exception
that is an instance of `$type`.*

*When called with an exception instance, this method checks that the stub threw
an exception that is equal to the supplied instance.*

*When called with a [matcher], this method checks that the stub threw an
exception that matches the supplied matcher.*

*This method supports [mock handle substitution].*

*See [Verifying spy exceptions], [Check verification].*

<a name="stub.produced" />

----

> *[verification][verification-api]* $stub->[**produced**](#stub.produced)($keyOrValue = null, $value = null)
> throws [AssertionException]

Checks if this stub produced the supplied values.

*When called with no arguments, this method simply checks that the stub produced
any value.*

*With a single argument, it checks that a value matching the argument was
produced.*

*With two arguments, it checks that a key and value matching the respective
arguments were produced together.*

*This method supports [mock handle substitution] for both keys and values.*

*See [Verifying values produced by spies].*

<a name="stub.checkProduced" />

----

> *[verification][verification-api]|null* $stub->[**checkProduced**](#stub.checkProduced)($keyOrValue = null, $value = null)

Checks if this stub produced the supplied values.

*When called with no arguments, this method simply checks that the stub produced
any value.*

*With a single argument, it checks that a value matching the argument was
produced.*

*With two arguments, it checks that a key and value matching the respective
arguments were produced together.*

*This method supports [mock handle substitution] for both keys and values.*

*See [Verifying values produced by spies], [Check verification].*

<a name="stub.producedAll" />

----

> *[verification][verification-api]* $stub->[**producedAll**](#stub.producedAll)(...$pairs)
> throws [AssertionException]

Throws an exception unless this stub produced all of the supplied key-value
pairs, in the supplied order, in a single call.

*Each value in `$pairs` is equivalent to a set of arguments passed to
[`produced()`](#stub.produced).*

*This method supports [mock handle substitution] for both keys and values.*

*See [Verifying values produced by spies].*

<a name="stub.checkProducedAll" />

----

> *[verification][verification-api]|null* $stub->[**checkProducedAll**](#stub.checkProducedAll)(...$pairs)

Checks if this stub produced all of the supplied key-value pairs, in the
supplied order, in a single call.

*Each value in `$pairs` is equivalent to a set of arguments passed to
[`checkProduced()`](#stub.checkProduced).*

*This method supports [mock handle substitution] for both keys and values.*

*See [Verifying values produced by spies], [Check verification].*

<a name="stub.received" />

----

> *[verification][verification-api]* $stub->[**received**](#stub.received)($value = null)
> throws [AssertionException]

Throws an exception unless this stub received the supplied value.

*When called with no arguments, this method simply checks that the stub received
any value.*

*This method supports [mock handle substitution].*

*See [Verifying values received by spies].*

<a name="stub.checkReceived" />

----

> *[verification][verification-api]|null* $stub->[**checkReceived**](#stub.checkReceived)($value = null)

Checks if this stub received the supplied value.

*When called with no arguments, this method simply checks that the stub received
any value.*

*This method supports [mock handle substitution].*

*See [Verifying values received by spies], [Check verification].*

<a name="stub.receivedException" />

----

> *[verification][verification-api]* $stub->[**receivedException**](#stub.receivedException)($type = null)
> throws [AssertionException]

Throws an exception unless this call received an exception of the supplied type.

*When called with no arguments, this method simply checks that the stub received
any exception.*

*When called with a string, this method checks that the stub received an
exception that is an instance of `$type`.*

*When called with an exception instance, this method checks that the stub
received an exception that is equal to the supplied instance.*

*When called with a [matcher], this method checks that the stub received an
exception that matches the supplied matcher.*

*This method supports [mock handle substitution].*

*See [Verifying exceptions received by spies].*

<a name="stub.checkReceivedException" />

----

> *[verification][verification-api]|null* $stub->[**checkReceivedException**](#stub.checkReceivedException)($type = null)

Checks if this stub received an exception of the supplied type.

*When called with no arguments, this method simply checks that the stub received
any exception.*

*When called with a string, this method checks that the stub received an
exception that is an instance of `$type`.*

*When called with an exception instance, this method checks that the stub
received an exception that is equal to the supplied instance.*

*When called with a [matcher], this method checks that the stub received an
exception that matches the supplied matcher.*

*This method supports [mock handle substitution].*

*See [Verifying exceptions received by spies], [Check verification].*

<a name="stub.never" />

----

> *fluent* $stub->[**never**](#stub.never)()

Requires that the next verification never matches.

*See [Verifying that a call event happened an exact number of times].*

<a name="stub.once" />

----

> *fluent* $stub->[**once**](#stub.once)()

Requires that the next verification matches only once.

*See [Verifying that a call event happened an exact number of times].*

<a name="stub.twice" />

----

> *fluent* $stub->[**twice**](#stub.twice)()

Requires that the next verification matches exactly two times.

*See [Verifying that a call event happened an exact number of times].*

<a name="stub.thrice" />

----

> *fluent* $stub->[**thrice**](#stub.thrice)()

Requires that the next verification matches exactly three times.

*See [Verifying that a call event happened an exact number of times].*

<a name="stub.times" />

----

> *fluent* $stub->[**times**](#stub.times)($times)

Requires that the next verification matches exactly `$times` times.

*See [Verifying that a call event happened an exact number of times].*

<a name="stub.atLeast" />

----

> *fluent* $stub->[**atLeast**](#stub.atLeast)($minimum)

Requires that the next verification matches a number of times greater than or
equal to `$minimum`.

*See [Verifying that a spy event happened a bounded number of times].*

<a name="stub.atMost" />

----

> *fluent* $stub->[**atMost**](#stub.atMost)($maximum)

Requires that the next verification matches a number of times less than or equal
to `$maximum`.

*See [Verifying that a spy event happened a bounded number of times].*

<a name="stub.between" />

----

> *fluent* $stub->[**between**](#stub.between)($minimum, $maximum)

Requires that the next verification matches a number of times greater than or
equal to `$minimum`, and less than or equal to `$maximum`.

*See [Verifying that a spy event happened a bounded number of times].*

<a name="stub.always" />

----

> *fluent* $stub->[**always**](#stub.always)()

Requires that the next verification matches for all possible items.

*See [Verifying that all spy events happen the same way].*

### The generator answer API

<a name="generatorAnswer.calls" />

----

> *fluent* $generatorAnswer->[**calls**](#generatorAnswer.calls)($callback, ...$additionalCallbacks)

Add callbacks to be called as part of the answer.

*See [Invoking callables in a generator].*

<a name="generatorAnswer.callsWith" />

----

> *fluent* $generatorAnswer->[**callsWith**](#generatorAnswer.callsWith)($callback, $arguments = [], $prefixSelf = false, $suffixArgumentsObject = false, $suffixArguments = true)

Add callbacks to be called as part of the answer.

*This method supports [mock handle substitution]. Any mock handles in
`$arguments` are equivalent to the mocks themselves.*

*See [Invoking callables in a generator].*

<a name="generatorAnswer.callsArgument" />

----

> *fluent* $generatorAnswer->[**callsArgument**](#generatorAnswer.callsArgument)($index = 0, ...$additionalIndices)

Add argument callbacks to be called as part of the answer.

*Negative indices are offset from the end of the list. That is, `-1` indicates
the last element, and `-2` indicates the second last element.*

*See [Invoking arguments in a generator].*

<a name="generatorAnswer.callsArgumentWith" />

----

> *fluent* $generatorAnswer->[**callsArgumentWith**](#generatorAnswer.callsArgumentWith)($index = 0, $arguments = [], $prefixSelf = false, $suffixArgumentsObject = false, $suffixArguments = true)

Add an argument callback to be called as part of the answer.

*Negative indices are offset from the end of the list. That is, `-1` indicates
the last element, and `-2` indicates the second last element.*

*This method supports reference parameters.*

*This method supports [mock handle substitution]. Any mock handles in
`$arguments` are equivalent to the mocks themselves.*

*See [Invoking arguments in a generator].*

<a name="generatorAnswer.setsArgument" />

----

> *fluent* $generatorAnswer->[**setsArgument**](#generatorAnswer.setsArgument)($indexOrValue = null, $value = null)

Set the value of an argument passed by reference as part of the answer.

*If called with no arguments, sets the first argument to `null`.*

*If called with one argument, sets the first argument to `$indexOrValue`.*

*If called with two arguments, sets the argument at `$indexOrValue` to
`$value`.*

*This method supports [mock handle substitution] of the value.*

*See [Setting passed-by-reference arguments in a generator].*

<a name="generatorAnswer.yields" />

----

> *fluent* $generatorAnswer->[**yields**](#generatorAnswer.yields)($keyOrValue = null, $value = null)

Add a yielded value to the answer.

*If both `$keyOrValue` and `$value` are supplied, the stub will yield like
`yield $keyOrValue => $value;`.*

*If only `$keyOrValue` is supplied, the stub will yield like
`yield $keyOrValue;`.*

*If no arguments are supplied, the stub will yield like `yield;`.*

*See [Yielding from a generator].*

<a name="generatorAnswer.yieldsFrom" />

----

> *fluent* $generatorAnswer->[**yieldsFrom**](#generatorAnswer.yieldsFrom)($values)

Add a set of yielded values to the answer.

*The `$values` argument can be an array, an iterator, or even another
generator.*

*See [Yielding from a generator].*

<a name="generatorAnswer.returns" />

----

> *[stub][stub-api]* $generatorAnswer->[**returns**](#generatorAnswer.returns)($value = null)

End the generator by returning a value.

*This method supports [mock handle substitution].*

*See [Returning values from a generator].*

<a name="generatorAnswer.returnsArgument" />

----

> *[stub][stub-api]* $generatorAnswer->[**returnsArgument**](#generatorAnswer.returnsArgument)($index = 0)

End the generator by returning an argument.

*Negative indices are offset from the end of the list. That is, `-1` indicates
the last element, and `-2` indicates the second last element.*

*See [Returning arguments from a generator].*

<a name="generatorAnswer.returnsSelf" />

----

> *[stub][stub-api]* $generatorAnswer->[**returnsSelf**](#generatorAnswer.returnsSelf)()

End the generator by returning the self value.

*See [Returning the "self" value from a generator], [Stub "self" values].*

<a name="generatorAnswer.throws" />

----

> *[stub][stub-api]* $generatorAnswer->[**throws**](#generatorAnswer.throws)($exception = null)

End the generator by throwing an exception.

*This method supports [mock handle substitution]. Any supplied exception mock
handles are equivalent to the mocks themselves.*

*See [Throwing exceptions from a generator].*

### Stubbing an existing callable

Any callable can be stubbed, by passing the callable to
[`stub()`](#facade.stub):

```php
$stub = stub($callable);        // with `use function`
$stub = x\stub($callable);      // without `use function`
$stub = Phony::stub($callable); // static
```

By default, the created stub will behave exactly like the wrapped callable:

```php
$stub = stub('max');

echo $stub(2, 3, 1); // outputs '3'
```

The stub can be configured to behave differently in specific circumstances,
whilst falling back to the default behavior during regular operation:

```php
$stub = stub('max')->with(2, 3, 1)->returns(9);

echo $stub(1, 2, 3); // outputs '3'
echo $stub(2, 3, 1); // outputs '9'
```

The stub can also be configured to completely replace the default behavior:

```php
$stub = stub('max')->returns(9);

echo $stub(1, 2, 3); // outputs '9'
echo $stub(2, 3, 1); // outputs '9'
```

### Anonymous stubs

*Anonymous stubs* are stubs that do not wrap an existing callable. An anonymous
stub is created by calling [`stub()`](#facade.stub) without passing a callable:

```php
$stub = stub();        // with `use function`
$stub = x\stub();      // without `use function`
$stub = Phony::stub(); // static
```

By default, anonymous stubs will always return `null` regardless of input
arguments:

```php
$stub = stub();

echo gettype($stub('a')); // outputs 'NULL'
```

The stub can be configured to behave differently in specific circumstances,
whilst returning `null` during regular operation:

```php
$stub = stub()->with('b')->returns('x');

echo gettype($stub('a')); // outputs 'NULL'
echo $stub('b');          // outputs 'x'
```

The stub can also be configured to behave the same in all circumstances:

```php
$stub = stub()->returns('x');

echo $stub();    // outputs 'x'
echo $stub('a'); // outputs 'x'
```

### Stub "self" values

All stubs have a special "self" value that is used in multiple ways by *Phony*.
The self value defaults to the callback passed to [`stub()`](#facade.stub):

```php
$callback = function () {};
$stub = stub($callback);

echo $stub->self() === $callback ? 'true' : 'false'; // outputs 'true'
```

The self value can also be set manually by calling [`setSelf()`](#stub.setSelf):

```php
$stub = stub();
$stub->setSelf('a');

echo $stub->self(); // outputs 'a'
```

The [`setSelf()`](#stub.setSelf) method is also fluent, meaning that stub
creation and self value setting can be done in a single expression:

```php
$stub = stub()->setSelf('a');
```

When stubs are retrieved from a [mock], their "self" value is automatically set
to the mock itself:

```php
$handle = mock('ClassA');
$mock = $handle->mock();
$stub = $handle->methodA;

echo $stub->self() === $mock ? 'true' : 'false'; // outputs 'true'
```

#### Magic "self" values

A stubbed callback that has a first parameter named `$phonySelf`, regardless of
the parameter's type, will receive the stub [self value] as the first argument.

In the case of stubs created outside of a mock, this self value will be the
callback passed to [`stub()`](#facade.stub), allowing recursive calls without
creating an explicit reference to the callback:

```php
$stub = stub(
    function ($phonySelf, $n, $total = 1) {
        if ($n < 2) {
            return $total;
        }

        return $phonySelf($n - 1, $total * $n);
    }
);
$stub->forwards();

echo $stub(0); // outputs '1'
echo $stub(1); // outputs '1'
echo $stub(2); // outputs '2'
echo $stub(3); // outputs '6'
echo $stub(4); // outputs '24'
echo $stub(5); // outputs '120'
```

### Stub rules and answers

*Stub rules* define the circumstances under which a stub changes its behavior.
*Stub answers* define how the stub behaves for a given rule. Each time a stub is
called, it will chose an answer by determining which rule matches the incoming
arguments:

```php
$stub
    ->with('a')     // starts the rule
    ->returns('x'); // creates an answer for the rule

echo $stub('a'); // outputs 'x'
```

#### Multiple rules

Each time [`with()`](#stub.with) is called (not to be confused with
[`calledWith()`](#spy.calledWith), which is part of [the spy API]), a new rule
is started:

```php
$stub
    ->with('a') // starts a rule
    ->returns('x')
    ->with('b') // starts another rule
    ->returns('y');

echo $stub('a'); // outputs 'x'
echo $stub('b'); // outputs 'y'
```

#### Multiple answers

A rule can have multiple answers. Each time a stub is called, *Phony* finds a
matching rule, and uses the next answer for that rule. When the last answer is
reached, *Phony* will continue to use it for any subsequent calls:

```php
$stub
    ->with('a')
    ->returns('x')  // creates the first answer
    ->returns('y'); // creates a subsequent answer

echo $stub('a'); // outputs 'x'

while (true) {
  echo $stub('a'); // outputs 'y' forever
}
```

#### Overriding rules

Defining a rule that matches the same arguments as a previous rule will override
the previous rule:

```php
$stub
    ->with('a')->returns('x')
    ->with('a')->returns('y'); // overrides previous rule

echo $stub('a'); // outputs 'y'
```

Defining a new rule that matches any arguments will override **all** previous
rules:

```php
$stub
    ->with('a')->returns('x')
    ->with('b')->returns('y')
    ->with('*')->returns('z'); // overrides all previous rules

echo $stub('a'); // outputs 'z'
echo $stub('b'); // outputs 'z'
```

Later rules take precedence over earlier ones, so more generic rules should be
defined first, with more specific rules being defined later:

```php
$stub
    ->with('*')->returns('x')  // a generic rule
    ->with('a')->returns('y'); // a more specific rule

echo $stub('a', 'b'); // outputs 'x'
echo $stub('a');      // outputs 'y'
```

#### The default rule and answer

When a new stub is created, the first rule is started implicitly, as if
[`with('*')`][shorthand matchers] were called. For example, the two following
stubs behave the same:

```php
$stubA = stub()
    ->with('*')
    ->returns('x');

$stubB = stub()
    // implicit ->with('*')
    ->returns('x');
```

If a new rule is started before any answers are defined, the stub behaves as if
[`forwards()`](#stub.forwards) were called, causing the stub to behave the same
as the stubbed callable by default. For example, the two following stubs behave
the same:

```php
$stubA = stub($callable)
    ->with('*')->forwards()
    ->with('a')->returns('x');

$stubB = stub($callable)
    // implicit ->with('*')->forwards()
    ->with('a')->returns('x');
```

##### The default answer callback

To change the default behavior of a stub, use
[`setDefaultAnswerCallback()`](#stub.setDefaultAnswerCallback). This method
accepts a callback that takes the stub as the first argument:

```php
$stub = stub();
$stub->setDefaultAnswerCallback(
    function ($stub) {
        // custom answer logic goes here
        $stub->returns('default');
    }
);

$stub->with('a')->returns('x');

echo $stub('a'); // outputs 'x'
echo $stub('b'); // outputs 'default'
```

The [`setDefaultAnswerCallback()`](#stub.setDefaultAnswerCallback) method is
also fluent, meaning stub creation and setting of this option can be done in a
single expression:

```php
$stub = stub()->setDefaultAnswerCallback($defaultAnswerCallback);
```

### Matching stub arguments

Stub arguments can be matched using [`with()`](#stub.with) (not to be confused
with [`calledWith()`](#spy.calledWith), which is part of [the spy API]).
Arguments passed to [`with()`](#stub.with) can be literal values, or [matchers],
including [shorthand matchers]:

```php
$stub
    ->with('*')->returns('v')
    ->with('a', '*')->returns('w')
    ->with('a', '~')->returns('x')
    ->with('a', 'b')->returns('y')
    ->with()->returns('z');

echo $stub('a');           // outputs 'v'
echo $stub('a', 'b', 'c'); // outputs 'w'
echo $stub('a', 'c');      // outputs 'x'
echo $stub('a', 'b');      // outputs 'y'
echo $stub();              // outputs 'z'
```

### Returning values

To return a value from a stub, use [`returns()`](#stub.returns):

```php
$stubA = stub()->returns('x');
$stubB = stub()->returns();

echo $stubA();          // outputs 'x'
echo gettype($stubB()); // outputs 'NULL'
```

Calling [`returns()`](#stub.returns) with multiple arguments is equivalent to
calling it once with each argument. For example, the two following stubs behave
the same:

```php
$stubA = stub()->returns('x', 'y');

echo $stubA(); // outputs 'x'
echo $stubA(); // outputs 'y'

$stubB = stub()->returns('x')->returns('y');

echo $stubB(); // outputs 'x'
echo $stubB(); // outputs 'y'
```

#### Default values for return types

When using [`returns()`](#stub.returns) without passing an explicit value,
*Phony* will attempt to return a value that conforms to the stubbed callable's
[return type]:

```php
$stub = stub(
    function (): int {}
);
$stub->returns();

var_dump($stubA()); // outputs 'int(0)'
```

This table details the return types that *Phony* handles, and the values
returned for each:

Return type   | Returned value
--------------|---------------
*(none)*      | `null`
`bool`        | `false`
`int`         | `0`
`float`       | `.0`
`string`      | `''`
`array`       | `[]`
`stdClass`    | `(object) []`
`callable`    | `function () {}`
`Traversable` | `new EmptyIterator()`
`Iterator`    | `new EmptyIterator()`
`Generator`   | `(function () {return; yield;})()`

When using a [return type] that is not listed above, the return value *must* be
explicitly passed, or *Phony* will throw an exception:

```php
$stub = stub(
    function (): DateTime {}
);

$stub->returns(new DateTime()); // works fine
$stub->returns();               // throws an exception
```

### Returning arguments

To return an argument from a stub, use
[`returnsArgument()`](#stub.returnsArgument):

```php
$stubA = stub()->returnsArgument();   // returns the first argument
$stubB = stub()->returnsArgument(1);  // returns the second argument
$stubC = stub()->returnsArgument(-1); // returns the last argument

echo $stubA('x', 'y', 'z'); // outputs 'x'
echo $stubB('x', 'y', 'z'); // outputs 'y'
echo $stubC('x', 'y', 'z'); // outputs 'z'
```

### Returning the "self" value

When stubs are retrieved from a mock, their [self value] is automatically set to
the mock itself. This allows mocking of [fluent interfaces] with the
[`returnsSelf()`](#stub.returnsSelf) method:

```php
interface FluentInterface
{
    public function methodA();
    public function methodB();
}

$handle = mock('FluentInterface')
$handle->methodA->returnsSelf();
$handle->methodB->returns('x');

$fluent = $handle->mock();

echo $fluent->methodA()->methodB(); // outputs 'x'
```

### Throwing exceptions

To throw an exception from a stub, use [`throws()`](#stub.throws):

```php
$exception = new RuntimeException('You done goofed.');

$stubA = stub()->throws($exception);
$stubB = stub()->throws();

$stubA(); // throws $exception
$stubB(); // throws a generic exception
```

Calling [`throws()`](#stub.throws) with multiple arguments is equivalent to
calling it once with each argument. For example, the two following stubs behave
the same:

```php
$exceptionA = new RuntimeException('You done goofed.');
$exceptionB = new RuntimeException('Consequences will never be the same.');

$stubA = stub()->throws($exceptionA, $exceptionB);

$stubA(); // throws $exceptionA
$stubA(); // throws $exceptionB

$stubB = stub()->throws($exceptionA)->throws($exceptionB);

$stubB(); // throws $exceptionA
$stubB(); // throws $exceptionB
```

### Using a callable as an answer

To use a callable as an answer, use [`does()`](#stub.does):

```php
$stub = stub()->does('max');

echo $stub(2, 3, 1); // outputs '3'
```

Calling [`does()`](#stub.does) with multiple arguments is equivalent to calling
it once with each argument. For example, the two following stubs behave the
same:

```php
$stubA = stub()->does('min', 'max');

echo $stubA(2, 3, 1); // outputs '1'
echo $stubA(2, 3, 1); // outputs '3'

$stubB = stub()->does('min')->does('max');

echo $stubB(2, 3, 1); // outputs '1'
echo $stubB(2, 3, 1); // outputs '3'
```

There is also a more powerful version of [`does()`](#stub.does), named
[`doesWith()`](#stub.doesWith), that allows more control over which arguments
are passed to the callable, and how they are passed:

```php
$stub = stub()->doesWith(
    'implode', // callable
    [', '],    // fixed arguments
    false,     // prefix the "self" value?
    true,      // suffix the arguments object?
    false      // suffix the arguments normally?
);

echo $stub('x', 'y', 'z'); // outputs 'x, y, z'
```

The [`doesWith()`](#stub.doesWith) method also supports arguments passed by
reference:

```php
$a = null;
$b = null;
$c = null;
$d = null;

$stub = stub()->doesWith(
    function (&$a, &$b, &$c, &$d) {
        list($a, $b, $c, $d) = ['a', 'b', 'c', 'd'];
    },
    [&$a, &$b],
    false,
    false,
    true
);

$stub->invokeWith([&$c, &$d]);

echo $a; // outputs 'a'
echo $b; // outputs 'b'
echo $c; // outputs 'c'
echo $d; // outputs 'd'
```

### Forwarding to the original callable

When stubbing an existing callable, the stub can "forward" calls on to the
original callable using [`forwards()`](#stub.forwards):

```php
$stub = stub('max')
    ->returns(9)
    ->with(2, 3, 1)->forwards();

echo $stub(2, 3, 1); // outputs '3'
echo $stub(3, 4, 5); // outputs '9'
echo $stub(7, 6, 5); // outputs '9'
```

This technique can be used to return a mocked method to its default behavior in
specific circumstances:

```php
class Cat
{
    public function speak()
    {
        return 'Cower in fear, mortal.';
    }
}

$handle = mock('Cat');
$handle->speak->returns('Meow.');
$handle->speak(true)->forwards();

$cat = $handle->mock();

echo $cat->speak();     // outputs 'Meow.'
echo $cat->speak(true); // outputs 'Cower in fear, mortal.'
```

The [`forwards()`](#stub.forwards) method also supports advanced usage,
including the ability to add to, and/or remove from, the arguments passed on to
the original callable:

```php
$stub = stub('implode')->forwards(
    [', '],    // fixed arguments
    false,     // prefix the "self" value?
    true,      // suffix the arguments object?
    false      // suffix the arguments normally?
);

echo $stub('x', 'y', 'z'); // outputs 'x, y, z'
```

Arguments passed by reference are also supported:

```php
$a = null;
$b = null;
$c = null;
$d = null;

$stub = stub(
    function (&$a, &$b, &$c, &$d) {
        list($a, $b, $c, $d) = ['a', 'b', 'c', 'd'];
    }
);

$stub->forwards([&$a, &$b], false, false, true);

$stub->invokeWith([&$c, &$d]);

echo $a; // outputs 'a'
echo $b; // outputs 'b'
echo $c; // outputs 'c'
echo $d; // outputs 'd'
```

### Answers that perform multiple actions

Stubs can perform multiple actions as part of a single answer. This allows
callables that have side effects other than return values or exceptions to be
emulated.

The most familiar of these side effects is probably the modification of
passed-by-reference arguments, and the invocation of other callables, such as in
an event emitter implementation.

#### Setting passed-by-reference arguments

To set a reference argument as part of an answer, use
[`setsArgument()`](#stub.setsArgument):

```php
$stub = stub(function (&$a, &$b, &$c) {})
    ->setsArgument(0, 'x')  // sets the first argument to 'x'
    ->setsArgument(1, 'y')  // sets the second argument to 'y'
    ->setsArgument(-1, 'z') // sets the last argument to 'z'
    ->returns();

$a = null;
$b = null;
$c = null;
$stub->invokeWith([&$a, &$b, &$c]);

echo $a; // outputs 'x'
echo $b; // outputs 'y'
echo $c; // outputs 'z'
```

If only one argument is passed to [`setsArgument()`](#stub.setsArgument), it
sets the first argument:

```php
$stub = stub(function (&$a) {})
    ->setsArgument('x') // sets the first argument to 'x'
    ->returns();

$a = null;
$stub->invokeWith([&$a]);

echo $a; // outputs 'x'
```

If [`setsArgument()`](#stub.setsArgument) is called without any arguments, it
sets the first argument to `null`:

```php
$stub = stub(function (&$a) {})
    ->setsArgument() // sets the first argument to `null`
    ->returns();

$a = 'x';
$stub->invokeWith([&$a]);

echo gettype($a); // outputs 'NULL'
```

#### Invoking arguments

To invoke an argument as part of an answer, use
[`callsArgument()`](#stub.callsArgument):

```php
$stub = stub()
    ->callsArgument()   // calls the first argument
    ->callsArgument(1)  // calls the second argument
    ->callsArgument(-1) // calls the last argument
    ->returns();

$x = function () { echo 'x'; };
$y = function () { echo 'y'; };
$z = function () { echo 'z'; };

$stub($x, $y, $z); // outputs 'xyz'
```

There is also a more powerful version of
[`callsArgument()`](#stub.callsArgument), named
[`callsArgumentWith()`](#stub.callsArgumentWith), that allows more control over
which arguments are passed to the callable, and how they are passed:

```php
$stub = stub()
    ->callsArgumentWith(
        1,              // argument to invoke
        ['%s, %s, %s'], // fixed arguments
        false,          // prefix the "self" value?
        true,           // suffix the arguments object?
        false           // suffix the arguments normally?
    )
    ->returns();

$stub('x', 'printf', 'y'); // outputs 'x, printf, y'
```

The [`callsArgumentWith()`](#stub.callsArgumentWith) method also supports
arguments passed by reference:

```php
$a = null;
$b = null;
$c = null;
$d = null;

$stub = stub()->callsArgumentWith(
    -1,
    [&$a, &$b],
    false,
    false,
    true
);

$callback = function (&$a, &$b, &$c, &$d) {
    list($a, $b, $c, $d) = ['a', 'b', 'c', 'd'];
};

$stub->invokeWith([&$c, &$d, $callback]);

echo $a; // outputs 'a'
echo $b; // outputs 'b'
echo $c; // outputs 'c'
echo $d; // outputs 'd'
```

#### Invoking callables

To invoke a callable as part of an answer, use [`calls()`](#stub.calls):

```php
$stub = stub()->calls('printf')->returns();

$stub('%s, %s', 'a', 'b'); // outputs 'a, b'
```

Calling [`calls()`](#stub.calls) with multiple arguments is equivalent to
calling it once with each argument. For example, the two following stubs behave
the same:

```php
$x = function () { echo 'x'; };
$y = function () { echo 'y'; };

$stubA = stub()->calls($x, $y)->returns();

$stubA(); // outputs 'xy'

$stubB = stub()->calls($x)->calls($y)->returns();

$stubB(); // outputs 'xy'
```

There is also a more powerful version of [`calls()`](#stub.calls), named
[`callsWith()`](#stub.callsWith), that allows more control over which arguments
are passed to the callable, and how they are passed:

```php
$stub = stub()
    ->callsWith(
        'printf',   // argument to invoke
        ['%s, %s'], // fixed arguments
        false,      // prefix the "self" value?
        false,      // suffix the arguments object?
        true        // suffix the arguments normally?
    )
    ->returns();

$stub('x', 'y'); // outputs 'x, y'
```

The [`callsWith()`](#stub.callsWith) method also supports arguments passed by
reference:

```php
$a = null;
$b = null;
$c = null;
$d = null;

$stub = stub()->callsWith(
    function (&$a, &$b, &$c, &$d) {
        list($a, $b, $c, $d) = ['a', 'b', 'c', 'd'];
    },
    [&$a, &$b],
    false,
    false,
    true
);

$stub->invokeWith([&$c, &$d]);

echo $a; // outputs 'a'
echo $b; // outputs 'b'
echo $c; // outputs 'c'
echo $d; // outputs 'd'
```

### Stubbing generators

To return a [generator] from a stub, use [`generates()`](#stub.generates):

```php
$stub = stub();
$stub->generates();

$generator = $stub();
$values = iterator_to_array($generator); // consume the generator

echo $generator instanceof Generator ? 'true' : 'false'; // outputs 'true'
echo json_encode($values);                               // outputs '[]'
```

The result of [`generates()`](#stub.generates) is a [generator answer]. This
object can be used to further customize the behavior of the generator to be
returned. See the subsequent headings for details of these customizations.

When a method is called on the generator answer that "ends" the answer (by
returning or throwing), the original stub is returned, allowing continued
stubbing in a fluent manner:

```php
$stub = stub()
    ->generates()     // returns a generator
        ->yields('a')
        ->yields('b')
        ->returns()   // ends the generator
    ->returns('c')    // returns a normal value
    ->generates()     // returns another generator
        ->yields('d')
        ->yields('e')
        ->throws();   // ends the generator by throwing

$resultA = $stub();
$resultB = $stub();
$resultC = $stub();

echo $resultA instanceof Generator ? 'true' : 'false'; // outputs 'true'
echo $resultB instanceof Generator ? 'true' : 'false'; // outputs 'false'
echo $resultC instanceof Generator ? 'true' : 'false'; // outputs 'true'
```

#### Yielding from a generator

Keys and values to be yielded can be passed directly to
[`generates()`](#stub.generates) as an array:

```php
$stub = stub()
    ->generates(['a', 'b', 'c', 'd'])->returns()
    ->generates(['e' => 'f', 'g' => 'h'])->returns();

$generatorA = $stub();
$generatorB = $stub();

$valuesA = iterator_to_array($generatorA); // consume the generator
$valuesB = iterator_to_array($generatorB); // consume the generator

echo json_encode($valuesA); // outputs '["a","b","c","d"]'
echo json_encode($valuesB); // outputs '{"e":"f","g":"h"}'
```

Alternatively, [`yields()`](#generatorAnswer.yields) can be used when yields
need to be interleaved with other actions:

```php
$count = 0;
$callback = function () use (&$count) {
    printf("Called %d time(s)\n", ++$count);
};

$stub = stub(function (&$argument) {});
$stub->generates()
    ->calls($callback)
    ->yields('a')
    ->calls($callback)
    ->yields('b');

foreach ($stub() as $value) {
    printf("Value: %s\n", $value);
}
```

The above example outputs:

```
Called 1 time(s)
Value: a
Called 2 time(s)
Value: b
```

If [`yields()`](#generatorAnswer.yields) is called with 2 arguments, they are
treated as key and value respectively. When called with 1 argument, the argument
is treated as the value to yield. When called with no arguments, the generator
will yield with no value:

```php
$stub = stub()->generates()
    ->yields('a', 'b')
    ->yields('c')
    ->yields()
    ->returns();

$values = iterator_to_array($stub()); // consume the generator

echo json_encode($values); // outputs '{"a":"b","0":"c","1":null}'
```

To yield a set of values from an array, an iterator, or another generator, use
[`yieldsFrom()`](#generatorAnswer.yieldsFrom):

```php
$stub = stub()->generates()
    ->yieldsFrom(['a' => 'b', 'c' => 'd'])
    ->yieldsFrom(['e' => 'f'])
    ->returns();

$values = iterator_to_array($stub()); // consume the generator

echo json_encode($values); // outputs '{"a":"b","c":"d","e":"f"}'
```

Calling [`generates()`](#stub.generates) with multiple sets of values creates
multiple answers that yield the supplied values on subsequent invocations. The
returned [generator answer] will allow customization of the final answer only.
For example, the two following stubs behave the same:

```php
$stubA = stub()
    ->generates(['a', 'b'], ['c', 'd'])
    ->yields('e')
    ->returns();

echo json_encode(iterator_to_array($stubA())); // outputs '["a","b"]'
echo json_encode(iterator_to_array($stubA())); // outputs '["c","d","e"]'

$stubB = stub()
    ->generates(['a', 'b'])
    ->returns()
    ->generates(['c', 'd'])
    ->yields('e')
    ->returns();

echo json_encode(iterator_to_array($stubB())); // outputs '["a","b"]'
echo json_encode(iterator_to_array($stubB())); // outputs '["c","d","e"]'
```

#### Returning values from a generator

To return a value from a generator, use [`returns()`](#generatorAnswer.returns)
on any [generator answer]:

```php
$stub = stub()->generates()->returns('a');

$generator = $stub();
iterator_to_array($generator); // consume the generator

echo $generator->getReturn(); // outputs 'a'
```

Note that attempting to return anything other than `null` will result in an
exception unless the current runtime supports generator return expressions. For
older runtimes, it is perfectly valid to call
[`returns()`](#generatorAnswer.returns) with no arguments in order to end the
generator:

```php
$stub = stub()->generates()->returns();
```

#### Returning arguments from a generator

To return an argument from a generator, use
[`returnsArgument()`](#generatorAnswer.returnsArgument) on any
[generator answer]:

```php
$stubA = stub()->generates()->returnsArgument();   // returns the first argument
$stubB = stub()->generates()->returnsArgument(1);  // returns the second argument
$stubC = stub()->generates()->returnsArgument(-1); // returns the last argument

$generatorA = $stubA('x', 'y', 'z');
$generatorB = $stubB('x', 'y', 'z');
$generatorC = $stubC('x', 'y', 'z');

iterator_to_array($generatorA); // consume the generator
iterator_to_array($generatorB); // consume the generator
iterator_to_array($generatorC); // consume the generator

echo $generatorA->getReturn(); // outputs 'x'
echo $generatorB->getReturn(); // outputs 'y'
echo $generatorC->getReturn(); // outputs 'z'
```

#### Returning the "self" value from a generator

The stub [self value] can be return from a generator by using
[`returnsSelf()`](#generatorAnswer.returnsSelf) on any [generator answer]:

```php
$handle = mock();
$handle->methodA->generates()->returnsSelf();

$mock = $handle->mock();
$generator = $mock->methodA();

iterator_to_array($generator); // consume the generator

echo $generator->getReturn() === $mock ? 'true' : 'false'; // outputs 'true'
```

#### Throwing exceptions from a generator

To throw an exception from a generator, use
[`throws()`](#generatorAnswer.throws) on any [generator answer]:

```php
$exception = new RuntimeException('You done goofed.');

$stubA = stub()->generates()->throws($exception);
$stubB = stub()->generates()->throws();

$generatorA = $stubA();
$generatorB = $stubB();

iterator_to_array($generatorA); // throws $exception
iterator_to_array($generatorB); // throws a generic exception
```

#### Generator iterations that perform multiple actions

Stubbed generators can perform multiple actions as part of a single iteration.
This allows side effects other than yielded values to be emulated.

The most familiar of these side effects is probably the modification of
passed-by-reference arguments, and the invocation of other callables.

##### Setting passed-by-reference arguments in a generator

To set a reference argument as part of a generator, use
[`setsArgument()`](#generatorAnswer.setsArgument) on any [generator answer]:

```php
$stub = stub(function (&$a, &$b, &$c) {})->generates()
    ->setsArgument(0, 'x')  // sets the first argument to 'x'
    ->setsArgument(1, 'y')  // sets the second argument to 'y'
    ->setsArgument(-1, 'z') // sets the last argument to 'z'
    ->returns();

$a = null;
$b = null;
$c = null;
$generator = $stub->invokeWith([&$a, &$b, &$c]);
iterator_to_array($generator); // consume the generator

echo $a; // outputs 'x'
echo $b; // outputs 'y'
echo $c; // outputs 'z'
```

If only one argument is passed to
[`setsArgument()`](#generatorAnswer.setsArgument), it sets the first argument:

```php
$stub = stub(function (&$a) {})->generates()
    ->setsArgument('x') // sets the first argument to 'x'
    ->returns();

$a = null;
$generator = $stub->invokeWith([&$a]);
iterator_to_array($generator); // consume the generator

echo $a; // outputs 'x'
```

If [`setsArgument()`](#generatorAnswer.setsArgument) is called without any
arguments, it sets the first argument to `null`:

```php
$stub = stub(function (&$a) {})->generates()
    ->setsArgument() // sets the first argument to `null`
    ->returns();

$a = 'x';
$generator = $stub->invokeWith([&$a]);
iterator_to_array($generator); // consume the generator

echo gettype($a); // outputs 'NULL'
```

Setting of arguments can be configured to occur in between yields:

```php
$stub = stub(function (&$a) {})->generates()
    ->setsArgument('x') // first iteration starts
    ->yields('a')       // first iteration ends
    ->setsArgument('y') // second iteration starts
    ->yields('b')       // second iteration ends
    ->returns();

$a = null;

foreach ($stub->invokeWith([&$a]) as $value) {
    printf("%s, %s\n", $value, $a);
}
```

The above example outputs:

```
a, x
b, y
```

##### Invoking arguments in a generator

To invoke an argument as part of a generator, use
[`callsArgument()`](#generatorAnswer.callsArgument) on any [generator answer]:

```php
$stub = stub()->generates()
    ->callsArgument()   // calls the first argument
    ->callsArgument(1)  // calls the second argument
    ->callsArgument(-1) // calls the last argument
    ->returns();

$x = function () { echo 'x'; };
$y = function () { echo 'y'; };
$z = function () { echo 'z'; };

$generator = $stub($x, $y, $z);
iterator_to_array($generator); // outputs 'xyz'
```

There is also a more powerful version of
[`callsArgument()`](#generatorAnswer.callsArgument), named
[`callsArgumentWith()`](#generatorAnswer.callsArgumentWith), that allows more
control over which arguments are passed to the callable, and how they are
passed:

```php
$stub = stub()->generates()
    ->callsArgumentWith(
        1,              // argument to invoke
        ['%s, %s, %s'], // fixed arguments
        false,          // prefix the "self" value?
        true,           // suffix the arguments object?
        false           // suffix the arguments normally?
    )
    ->returns();

$generator = $stub('x', 'printf', 'y');
iterator_to_array($generator); // outputs 'x, printf, y'
```

The [`callsArgumentWith()`](#generatorAnswer.callsArgumentWith) method also
supports arguments passed by reference:

```php
$a = null;
$b = null;
$c = null;
$d = null;

$stub = stub()->generates()
    ->callsArgumentWith(
        -1,
        [&$a, &$b],
        false,
        false,
        true
    )
    ->returns();

$callback = function (&$a, &$b, &$c, &$d) {
    list($a, $b, $c, $d) = ['a', 'b', 'c', 'd'];
};

$generator = $stub->invokeWith([&$c, &$d, $callback]);
iterator_to_array($generator); // consume the generator

echo $a; // outputs 'a'
echo $b; // outputs 'b'
echo $c; // outputs 'c'
echo $d; // outputs 'd'
```

Calling of arguments can be configured to occur in between yields:

```php
$count = 0;
$callback = function () use (&$count) {
    printf("Called %d time(s)\n", ++$count);
};

$stub = stub(function (&$a) {})->generates()
    ->callsArgument() // first iteration starts
    ->yields('a')     // first iteration ends
    ->callsArgument() // second iteration starts
    ->yields('b')     // second iteration ends
    ->returns();

foreach ($stub($callback) as $value) {
    printf("Value: %s\n", $value);
}
```

The above example outputs:

```
Called 1 time(s)
Value: a
Called 2 time(s)
Value: b
```

##### Invoking callables in a generator

To invoke a callable as part of a generator, use
[`calls()`](#generatorAnswer.calls) on any [generator answer]:

```php
$stub = stub()->generates()->calls('printf')->returns();

$generator = $stub('%s, %s', 'a', 'b');
iterator_to_array($generator); // outputs 'a, b'
```

Calling [`calls()`](#generatorAnswer.calls) with multiple arguments is
equivalent to calling it once with each argument. For example, the two following
stubs behave the same:

```php
$x = function () { echo 'x'; };
$y = function () { echo 'y'; };

$stubA = stub()->generates()->calls($x, $y)->returns();

$generator = $stubA();
iterator_to_array($generator); // outputs 'xy'

$stubB = stub()->generates()->calls($x)->calls($y)->returns();

$generator = $stubB();
iterator_to_array($generator); // outputs 'xy'
```

There is also a more powerful version of [`calls()`](#generatorAnswer.calls),
named [`callsWith()`](#generatorAnswer.callsWith), that allows more control over
which arguments are passed to the callable, and how they are passed:

```php
$stub = stub()->generates()
    ->callsWith(
        'printf',   // argument to invoke
        ['%s, %s'], // fixed arguments
        false,      // prefix the "self" value?
        false,      // suffix the arguments object?
        true        // suffix the arguments normally?
    )
    ->returns();

$generator = $stub('x', 'y');
iterator_to_array($generator); // outputs 'x, y'
```

The [`callsWith()`](#generatorAnswer.callsWith) method also supports arguments
passed by reference:

```php
$a = null;
$b = null;
$c = null;
$d = null;

$stub = stub()->generates()
    ->callsWith(
        function (&$a, &$b, &$c, &$d) {
            list($a, $b, $c, $d) = ['a', 'b', 'c', 'd'];
        },
        [&$a, &$b],
        false,
        false,
        true
    )
    ->returns();

$generator = $stub->invokeWith([&$c, &$d])
iterator_to_array($generator); // consume the generator

echo $a; // outputs 'a'
echo $b; // outputs 'b'
echo $c; // outputs 'c'
echo $d; // outputs 'd'
```

Calling of callables can be configured to occur in between yields:

```php
$count = 0;
$callback = function () use (&$count) {
    printf("Called %d time(s)\n", ++$count);
};

$stub = stub(function (&$a) {})->generates()
    ->calls($callback) // first iteration starts
    ->yields('a')      // first iteration ends
    ->calls($callback) // second iteration starts
    ->yields('b')      // second iteration ends
    ->returns();

foreach ($stub($callback) as $value) {
    printf("Value: %s\n", $value);
}
```

The above example outputs:

```
Called 1 time(s)
Value: a
Called 2 time(s)
Value: b
```

## Spies

*Spies* record interactions with callable entities, such as functions, methods,
closures, and objects with an [`__invoke()`] method. They can be used to verify
both the *input*, and *output* of function calls.

Most of the methods in the spy API are mirrored in [the call API].

### The spy API

<a name="facade.spy" />

----

> *[spy][spy-api]* [**spy**](#facade.spy)($callback = null) *(with [use function])*<br />
> *[spy][spy-api]* x\\[**spy**](#facade.spy)($callback = null) *(without [use function])*<br />
> *[spy][spy-api]* Phony::[**spy**](#facade.spy)($callback = null) *(static)*

Create a new [spy].

*See [Spying on an existing callable], [Anonymous spies].*

<a name="spy.invoke" />
<a name="spy.__invoke" />

----

> *mixed* $spy->[**invoke**](#spy.invoke)(...$arguments) or
> [**$spy(...$arguments)**](#spy.__invoke)
> throws [Exception], [Error]

Invoke the spy, record the call, and return or throw the result.

*This method does not support reference parameters.*

*See [Invoking spies].*

<a name="spy.invokeWith" />

----

> *mixed* $spy->[**invokeWith**](#spy.invokeWith)($arguments)
> throws [Exception], [Error]

Invoke the spy, record the call, and return or throw the result.

*This method supports reference parameters.*

*See [Invoking spies].*

<a name="spy.label" />

----

> *string|null* $spy->[**label**](#spy.label)()

Get the [label][labeling spies].

<a name="spy.setLabel" />

----

> *fluent* $spy->[**setLabel**](#spy.setLabel)($label)

Set the [label][labeling spies].

<a name="spy.useGeneratorSpies" />

----

> *boolean* $spy->[**useGeneratorSpies**](#spy.useGeneratorSpies)()

Returns `true` if this spy uses [generator spies].

<a name="spy.setUseGeneratorSpies" />

----

> *fluent* $spy->[**setUseGeneratorSpies**](#spy.setUseGeneratorSpies)($useGeneratorSpies)

Turn on or off the use of [generator spies].

<a name="spy.useTraversableSpies" />

----

> *boolean* $spy->[**useTraversableSpies**](#spy.useTraversableSpies)()

Returns `true` if this spy uses [traversable spies].

<a name="spy.setUseTraversableSpies" />

----

> *fluent* $spy->[**setUseTraversableSpies**](#spy.setUseTraversableSpies)($useTraversableSpies)

Turn on or off the use of [traversable spies].

<a name="spy.stopRecording" />

----

> *fluent* $spy->[**stopRecording**](#spy.stopRecording)()

Stop recording calls.

*See [Pausing spy recording].*

<a name="spy.startRecording" />

----

> *fluent* $spy->[**startRecording**](#spy.startRecording)()

Start recording calls.

*See [Pausing spy recording].*

<a name="spy.arguments" />

----

> *[arguments][arguments-api]* $spy->[**arguments**](#spy.arguments)()
> throws [UndefinedCallException]

Get the arguments of the first call.

<a name="spy.argument" />

----

> *mixed* $spy->[**argument**](#spy.argument)($index = 0)
> throws [UndefinedCallException], [UndefinedArgumentException]

Get the argument of the first call at `$index`.

<a name="spy.hasCalls" />

----

> *boolean* $spy->[**hasCalls**](#spy.hasCalls)()

Returns `true` if any calls were recorded.

<a name="spy.callCount" />

----

> *integer* $spy->[**callCount**](#spy.callCount)()

Get the number of calls.

<a name="spy.allCalls" />

----

> *array\<[call][call-api]>* $spy->[**allCalls**](#spy.allCalls)()

Get all calls as an array.

<a name="spy.firstCall" />

----

> *[call][call-api]* $spy->[**firstCall**](#spy.firstCall)()
> throws [UndefinedCallException]

Get the first call.

<a name="spy.lastCall" />

----

> *[call][call-api]* $spy->[**lastCall**](#spy.lastCall)()
> throws [UndefinedCallException]

Get the last call.

<a name="spy.callAt" />

----

> *[call][call-api]* $spy->[**callAt**](#spy.callAt)($index = 0)
> throws [UndefinedCallException]

Get the call at `$index`.

*Negative indices are offset from the end of the list. That is, `-1` indicates
the last element, and `-2` indicates the second last element.*

<a name="spy.hasEvents" />

----

> *boolean* $spy->[**hasEvents**](#spy.hasEvents)()

Returns `true` if any events were recorded.

<a name="spy.eventCount" />

----

> *integer* $spy->[**eventCount**](#spy.eventCount)()

Get the number of events.

<a name="spy.allEvents" />

----

> *array\<[event][event-api]>* $spy->[**allEvents**](#spy.allEvents)()

Get all events as an array.

<a name="spy.firstEvent" />

----

> *[event][event-api]* $spy->[**firstEvent**](#spy.firstEvent)()
> throws [UndefinedEventException]

Get the first event.

<a name="spy.lastEvent" />

----

> *[event][event-api]* $spy->[**lastEvent**](#spy.lastEvent)()
> throws [UndefinedEventException]

Get the last event.

<a name="spy.eventAt" />

----

> *[event][event-api]* $spy->[**eventAt**](#spy.eventAt)($index = 0)
> throws [UndefinedEventException]

Get the event at `$index`.

*Negative indices are offset from the end of the list. That is, `-1` indicates
the last element, and `-2` indicates the second last element.*

<a name="spy.called" />

----

> *[verification][verification-api]* $spy->[**called**](#spy.called)()
> throws [AssertionException]

Throws an exception unless called.

*See [Verifying that a call was made].*

<a name="spy.checkCalled" />

----

> *[verification][verification-api]|null* $spy->[**checkCalled**](#spy.checkCalled)()

Checks if called.

*See [Verifying that a call was made], [Check verification].*

<a name="spy.calledWith" />

----

> *[verification][verification-api]* $spy->[**calledWith**](#spy.calledWith)(...$arguments)
> throws [AssertionException]

Throws an exception unless called with the supplied arguments.

*This method supports [mock handle substitution]. Any mock handles in
`$arguments` are equivalent to the mocks themselves.*

*See [Verifying that a spy was called with specific arguments].*

<a name="spy.checkCalledWith" />

----

> *[verification][verification-api]|null* $spy->[**checkCalledWith**](#spy.checkCalledWith)(...$arguments)

Checks if called with the supplied arguments.

*This method supports [mock handle substitution]. Any mock handles in
`$arguments` are equivalent to the mocks themselves.*

*See [Verifying that a spy was called with specific arguments],
[Check verification].*

<a name="spy.calledOn" />

----

> *[verification][verification-api]* $spy->[**calledOn**](#spy.calledOn)($value)
> throws [AssertionException]

Throws an exception unless the bound `$this` value is equal to the supplied
value.

*This method supports [mock handle substitution].*

*See [Verifying spy closure binding].*

<a name="spy.checkCalledOn" />

----

> *[verification][verification-api]|null* $spy->[**checkCalledOn**](#spy.checkCalledOn)($value)

Checks if the bound `$this` value is equal to the supplied value.

*This method supports [mock handle substitution].*

*See [Verifying spy closure binding], [Check verification].*

<a name="spy.returned" />

----

> *[verification][verification-api]* $spy->[**returned**](#spy.returned)($value = null)
> throws [AssertionException]

Throws an exception unless this spy returned the supplied value.

*When called with no arguments, this method simply checks that the spy returned
any value.*

*This method supports [mock handle substitution].*

*See [Verifying spy return values].*

<a name="spy.checkReturned" />

----

> *[verification][verification-api]|null* $spy->[**checkReturned**](#spy.checkReturned)($value = null)

Checks if this spy returned the supplied value.

*When called with no arguments, this method simply checks that the spy returned
any value.*

*This method supports [mock handle substitution].*

*See [Verifying spy return values], [Check verification].*

<a name="spy.threw" />

----

> *[verification][verification-api]* $spy->[**threw**](#spy.threw)($type = null)
> throws [AssertionException]

Throws an exception unless this spy threw an exception of the supplied type.

*When called with no arguments, this method simply checks that the spy threw any
exception.*

*When called with a string, this method checks that the spy threw an exception
that is an instance of `$type`.*

*When called with an exception instance, this method checks that the spy threw
an exception that is equal to the supplied instance.*

*When called with a [matcher], this method checks that the spy threw an
exception that matches the supplied matcher.*

*This method supports [mock handle substitution].*

*See [Verifying spy exceptions].*

<a name="spy.checkThrew" />

----

> *[verification][verification-api]|null* $spy->[**checkThrew**](#spy.checkThrew)($type = null)

Checks if an exception of the supplied type was thrown.

*When called with no arguments, this method simply checks that the spy threw any
exception.*

*When called with a string, this method checks that the spy threw an exception
that is an instance of `$type`.*

*When called with an exception instance, this method checks that the spy threw
an exception that is equal to the supplied instance.*

*When called with a [matcher], this method checks that the spy threw an
exception that matches the supplied matcher.*

*This method supports [mock handle substitution].*

*See [Verifying spy exceptions], [Check verification].*

<a name="spy.produced" />

----

> *[verification][verification-api]* $spy->[**produced**](#spy.produced)($keyOrValue = null, $value = null)
> throws [AssertionException]

Checks if this spy produced the supplied values.

*When called with no arguments, this method simply checks that the spy produced
any value.*

*With a single argument, it checks that a value matching the argument was
produced.*

*With two arguments, it checks that a key and value matching the respective
arguments were produced together.*

*This method supports [mock handle substitution] for both keys and values.*

*See [Verifying values produced by spies].*

<a name="spy.checkProduced" />

----

> *[verification][verification-api]|null* $spy->[**checkProduced**](#spy.checkProduced)($keyOrValue = null, $value = null)

Checks if this spy produced the supplied values.

*When called with no arguments, this method simply checks that the spy produced
any value.*

*With a single argument, it checks that a value matching the argument was
produced.*

*With two arguments, it checks that a key and value matching the respective
arguments were produced together.*

*This method supports [mock handle substitution] for both keys and values.*

*See [Verifying values produced by spies], [Check verification].*

<a name="spy.producedAll" />

----

> *[verification][verification-api]* $spy->[**producedAll**](#spy.producedAll)(...$pairs)
> throws [AssertionException]

Throws an exception unless this spy produced all of the supplied key-value
pairs, in the supplied order, in a single call.

*Each value in `$pairs` is equivalent to a set of arguments passed to
[`produced()`](#spy.produced).*

*This method supports [mock handle substitution] for both keys and values.*

*See [Verifying values produced by spies].*

<a name="spy.checkProducedAll" />

----

> *[verification][verification-api]|null* $spy->[**checkProducedAll**](#spy.checkProducedAll)(...$pairs)

Checks if this spy produced all of the supplied key-value pairs, in the supplied
order, in a single call.

*Each value in `$pairs` is equivalent to a set of arguments passed to
[`checkProduced()`](#spy.checkProduced).*

*This method supports [mock handle substitution] for both keys and values.*

*See [Verifying values produced by spies], [Check verification].*

<a name="spy.received" />

----

> *[verification][verification-api]* $spy->[**received**](#spy.received)($value = null)
> throws [AssertionException]

Throws an exception unless this spy received the supplied value.

*When called with no arguments, this method simply checks that the spy received
any value.*

*This method supports [mock handle substitution].*

*See [Verifying values received by spies].*

<a name="spy.checkReceived" />

----

> *[verification][verification-api]|null* $spy->[**checkReceived**](#spy.checkReceived)($value = null)

Checks if this spy received the supplied value.

*When called with no arguments, this method simply checks that the spy received
any value.*

*This method supports [mock handle substitution].*

*See [Verifying values received by spies], [Check verification].*

<a name="spy.receivedException" />

----

> *[verification][verification-api]* $spy->[**receivedException**](#spy.receivedException)($type = null)
> throws [AssertionException]

Throws an exception unless this call received an exception of the supplied type.

*When called with no arguments, this method simply checks that the spy received
any exception.*

*When called with a string, this method checks that the spy received an
exception that is an instance of `$type`.*

*When called with an exception instance, this method checks that the spy
received an exception that is equal to the supplied instance.*

*When called with a [matcher], this method checks that the spy received an
exception that matches the supplied matcher.*

*This method supports [mock handle substitution].*

*See [Verifying exceptions received by spies].*

<a name="spy.checkReceivedException" />

----

> *[verification][verification-api]|null* $spy->[**checkReceivedException**](#spy.checkReceivedException)($type = null)

Checks if this spy received an exception of the supplied type.

*When called with no arguments, this method simply checks that the spy received
any exception.*

*When called with a string, this method checks that the spy received an
exception that is an instance of `$type`.*

*When called with an exception instance, this method checks that the spy
received an exception that is equal to the supplied instance.*

*When called with a [matcher], this method checks that the spy received an
exception that matches the supplied matcher.*

*This method supports [mock handle substitution].*

*See [Verifying exceptions received by spies], [Check verification].*

<a name="spy.never" />

----

> *fluent* $spy->[**never**](#spy.never)()

Requires that the next verification never matches.

*See [Verifying that a call event happened an exact number of times].*

<a name="spy.once" />

----

> *fluent* $spy->[**once**](#spy.once)()

Requires that the next verification matches only once.

*See [Verifying that a call event happened an exact number of times].*

<a name="spy.twice" />

----

> *fluent* $spy->[**twice**](#spy.twice)()

Requires that the next verification matches exactly two times.

*See [Verifying that a call event happened an exact number of times].*

<a name="spy.thrice" />

----

> *fluent* $spy->[**thrice**](#spy.thrice)()

Requires that the next verification matches exactly three times.

*See [Verifying that a call event happened an exact number of times].*

<a name="spy.times" />

----

> *fluent* $spy->[**times**](#spy.times)($times)

Requires that the next verification matches exactly `$times` times.

*See [Verifying that a call event happened an exact number of times].*

<a name="spy.atLeast" />

----

> *fluent* $spy->[**atLeast**](#spy.atLeast)($minimum)

Requires that the next verification matches a number of times greater than or
equal to `$minimum`.

*See [Verifying that a spy event happened a bounded number of times].*

<a name="spy.atMost" />

----

> *fluent* $spy->[**atMost**](#spy.atMost)($maximum)

Requires that the next verification matches a number of times less than or equal
to `$maximum`.

*See [Verifying that a spy event happened a bounded number of times].*

<a name="spy.between" />

----

> *fluent* $spy->[**between**](#spy.between)($minimum, $maximum)

Requires that the next verification matches a number of times greater than or
equal to `$minimum`, and less than or equal to `$maximum`.

*See [Verifying that a spy event happened a bounded number of times].*

<a name="spy.always" />

----

> *fluent* $spy->[**always**](#spy.always)()

Requires that the next verification matches for all possible items.

*See [Verifying that all spy events happen the same way].*

### Spying on an existing callable

Any callable can be wrapped in a spy, by passing the callable to
[`spy()`](#facade.spy):

```php
$spy = spy($callable);        // with `use function`
$spy = x\spy($callable);      // without `use function`
$spy = Phony::spy($callable); // static
```

The created spy will behave exactly like the wrapped callable:

```php
$spy = spy('max');

echo $spy(2, 3, 1); // outputs '3'
```

### Anonymous spies

*Anonymous spies* are spies that do not wrap an existing callable. Their only
purpose is to record input arguments. An anonymous spy is created by calling
[`spy()`](#facade.spy) without passing a callable:

```php
$spy = spy();        // with `use function`
$spy = x\spy();      // without `use function`
$spy = Phony::spy(); // static
```

Regardless of input arguments, anonymous spies will always return `null`:

```php
$spy = spy();

echo gettype($spy('a')); // outputs 'NULL'
```

### Call verification

*Phony* provides the ability to make verifications on individual recorded calls.
The API for verifying calls mirrors the methods available for spy verification.

See [the call API] for more information.

#### Call count

The number of calls recorded by a spy can be retrieved using
[`callCount()`](#spy.callCount):

```php
$spy->callCount();
```

#### Individual calls

To get the first call, use [`firstCall()`](#spy.firstCall):

```php
$spy->firstCall();
```

To get the last call, use [`lastCall()`](#spy.lastCall):

```php
$spy->lastCall();
```

To get a specific call by index, use [`callAt()`](#spy.callAt):

```php
$spy->callAt(0); // returns the first call
$spy->callAt(9); // returns the tenth call
```

These methods will throw an exception if no call is found.

### Verifying spy input

#### Verifying that a call was made

To verify that a spy was called, use [`called()`](#spy.called):

```php
$spy->called();
```

#### Verifying that a spy was called with specific arguments

To verify input arguments, use [`calledWith()`](#spy.calledWith). Arguments
passed to [`calledWith()`](#spy.calledWith) can be literal values, or
[matchers], including [shorthand matchers]:

```php
$spy->calledWith();         // called with no arguments
$spy->calledWith('a', 'b'); // called with 'a' followed by 'b'
$spy->calledWith('a', '*'); // called with 'a' followed by 0-n arguments
$spy->calledWith('a', '~'); // called with 'a' followed by exactly 1 argument
```

Arguments can be retrieved by calling [`arguments()`](#spy.arguments) or
[`argument()`](#spy.argument) on a spy:

```php
$spy->arguments(); // all arguments as an array
$spy->argument();  // first argument
$spy->argument(1); // second argument
```

Note that this will return the arguments for the first call made on the spy.

Arguments can also be retrieved by calling
[`arguments()`](#verification.arguments) or
[`argument()`](#verification.argument) on any [verification result]:

```php
$spy->called()->arguments(); // all arguments as an array
$spy->called()->argument();  // first argument
$spy->called()->argument(1); // second argument
```

Note that this will return the arguments for the first call that matches the
verification in use.

#### Verifying spy closure binding

Where [closure binding] is supported, the bound object can be verified using
[`calledOn()`](#spy.calledOn):

```php
$spy->calledOn($object);
```

### Verifying spy output

#### Verifying spy return values

To verify a spy's return value, use [`returned()`](#spy.returned):

```php
$spy->returned();    // returned anything
$spy->returned('a'); // returned 'a'
```

#### Verifying spy exceptions

To verify that a spy threw an exception, use [`threw()`](#spy.threw):

```php
$spy->threw();                                         // threw any exception
$spy->threw('RuntimeException');                       // threw a runtime exception
$spy->threw(new RuntimeException('You done goofed.')); // threw a runtime exception with a specific message
```

### Verifying spies with generators or traversables

*Phony* records values and exceptions that are passed in and out of
[generators]:

- When a generator uses the [yield] keyword, this is recorded as a *produced
  value*.
- When calling code uses [Generator::send()], this is recorded as a *sent
  value*.
- When calling code uses [Generator::throw()], this is recorded as a *sent
  exception*.
- When a generator ends, either implicitly, or with an explicit `return`
  statement, this is recorded as a *returned value* of `null` (as generators
  cannot return values).
- When a generator ends because of an exception, this is recorded as a *thrown
  exception*.

This behavior is enabled by default for generators, and can optionally be
enabled for other traversables by calling
[`setUseTraversableSpies()`](#spy.setUseTraversableSpies) on a spy:

```php
$spy->setUseTraversableSpies(true);
```

For other traversables, such as arrays and iterators, the values are recorded as
*produced values*.

To turn off generator spies, use
[`setUseGeneratorSpies()`](#spy.setUseGeneratorSpies) on a spy:

```php
$spy->setUseGeneratorSpies(false);
```

Both [`setUseGeneratorSpies()`](#spy.setUseGeneratorSpies) and
[`setUseTraversableSpies()`](#spy.setUseTraversableSpies) are fluent, meaning
spy creation and setting of these options can be done in a single expression:

```php
$spy = spy()
    ->setUseTraversableSpies(true)
    ->setUseGeneratorSpies(false);
```

#### Verifying values produced by spies

To verify that a value was produced by a spy, use [`produced()`](#spy.produced):

```php
$spy->produced();         // produced anything
$spy->produced('a');      // produced 'a' with any key
$spy->produced('a', 'b'); // produced 'b' with key 'a'
```

To verify that a set of values were produced by a spy in a specific order, in a
single call, use [`producedAll()`](#spy.producedAll):

```php
$spy->producedAll();                // produced nothing (an empty traversable)
$spy->producedAll('a', 'b');        // produced 'a', then 'b', with any keys
$spy->producedAll('a', ['b', 'c']); // produced 'a' with any key, then 'c' with key 'b'
```

#### Verifying values received by spies

To verify that a value was received by a spy, use [`received()`](#spy.received):

```php
$spy->received();    // received anything
$spy->received('a'); // received 'a'
```

#### Verifying exceptions received by spies

To verify that an exception was received by a spy, use
[`receivedException()`](#spy.receivedException):

```php
$spy->receivedException();                                         // received any exception
$spy->receivedException('RuntimeException');                       // received a runtime exception
$spy->receivedException(new RuntimeException('You done goofed.')); // received a runtime exception with a specific message
```

### Verifying cardinality with spies

Cardinality modifiers change the amount of times a call, or other event, must
meet the requirements of a subsequent verification.

Cardinality must be specified **before** verification, and can be applied to
any verification call:

```php
$spy->once()->called();        // called exactly 1 time
$spy->once()->calledWith('a'); // called exactly 1 time with 'a'
$spy->once()->returned('b');   // returned 'b' exactly 1 time
```

The default cardinality is `atLeast(1)`, meaning verifications will pass if at
least one matching event was recorded.

#### Verifying that a spy event happened an exact number of times

To verify that an event happened an exact number of times, use one of
[`never()`](#spy.never), [`once()`](#spy.once), [`twice()`](#spy.twice),
[`thrice()`](#spy.thrice), or [`times()`](#spy.times):

```php
$spy->never()->called();   // never called
$spy->once()->called();    // called exactly 1 time
$spy->twice()->called();   // called exactly 2 times
$spy->thrice()->called();  // called exactly 3 times
$spy->times(10)->called(); // called exactly 10 times

$spy->never()->returned('a');   // never returned 'a'
$spy->once()->returned('a');    // returned 'a' exactly 1 time
$spy->twice()->returned('a');   // returned 'a' exactly 2 times
$spy->thrice()->returned('a');  // returned 'a' exactly 3 times
$spy->times(10)->returned('a'); // returned 'a' exactly 10 times
```

#### Verifying that a spy event happened a bounded number of times

To verify that an event happened a bounded number of times, use one of
[`atLeast()`](#spy.atLeast), [`atMost()`](#spy.atMost), or
[`between()`](#spy.between):

```php
$spy->atLeast(2)->called();    // called 2 or more times
$spy->atMost(3)->called();     // called no more than 3 times
$spy->between(2, 4)->called(); // called 2, 3, or 4 times

$spy->atLeast(2)->returned('a');    // returned 'a' 2 or more times
$spy->atMost(3)->returned('a');     // returned 'a' no more than 3 times
$spy->between(2, 4)->returned('a'); // returned 'a' 2, 3, or 4 times
```

#### Verifying that all spy events happen the same way

To verify that all events happen the same way, use [`always()`](#spy.always):

```php
$spy->always()->calledWith('a'); // always called with 'a'
$spy->always()->returned('b');   // always returned 'b'
```

Note that [`always()`](#spy.always) does not interfere with other cardinality
modifiers, and can be combined to produce powerful verifications:

```php
$spy->twice()->always()->calledWith('a'); // called exactly 2 times, and always with 'a'
```

### Labeling spies

Every spy has a label, which is a free-form string used to help identify the spy
in verification failure messages. By default, each spy is assigned a unique
sequential integer label upon creation.

The label can be changed at any time by using [`setLabel()`](#spy.setLabel):

```php
$spy = spy();
$spy->setLabel('a');

echo $spy->label(); // outputs 'a'
```

The [`setLabel()`](#spy.setLabel) method is also fluent, meaning that spy
creation and label setting can be done in a single expression:

```php
$spy = spy()->setLabel('a');
```

When a verification fails for a labeled spy, the output is similar to the
following:

    Expected call on {spy}[label] with arguments like:
        "x", "y"
    Calls:
        - "x", "z"

### Invoking spies

Spies can be invoked directly like any other dynamic callable:

```php
$spy('a', 'b');

$spy->calledWith('a', 'b'); // passes
```

They can also be invoked more explicitly using [`invoke()`](#spy.invoke):

```php
$spy->invoke('a', 'b');

$spy->calledWith('a', 'b'); // passes
```

There is also a more advanced method, [`invokeWith()`](#spy.invokeWith), that
supports arguments passed by reference:

```php
$spy = spy(
    function (&$a, &$b) {
        list($a, $b) = ['x', 'y'];
    }
);

$a = 'a';
$b = 'b';

$spy->invokeWith([&$a, &$b]);

$spy->calledWith('a', 'b'); // passes

echo $a; // outputs 'x'
echo $b; // outputs 'y'
```

### Pausing spy recording

To temporarily prevent spies from recording calls, use
[`stopRecording()`](#spy.stopRecording) and
[`startRecording()`](#spy.startRecording) as necessary:

```php
$spy = spy();
$spy('a');
$spy->stopRecording();
$spy('b');
$spy->startRecording();
$spy('c');

$spy->calledWith('a'); // passes
$spy->calledWith('c'); // passes

$spy->calledWith('b'); // fails
```

## Calls

*Phony* provides the ability to make verifications on individual recorded calls.
The call API mirrors the methods available on [the spy API].

### The call API

<a name="call.arguments" />

----

> *[arguments][arguments-api]* $call->[**arguments**](#call.arguments)()

Get the arguments.

<a name="call.argument" />

----

> *mixed* $call->[**argument**](#call.argument)($index = 0)
> throws [UndefinedArgumentException]

Get an argument by index.

*Negative indices are offset from the end of the list. That is, `-1` indicates
the last element, and `-2` indicates the second last element.*

<a name="call.argumentCount" />

----

> *integer* $call->[**argumentCount**](#call.argumentCount)()

Get the number of arguments.

<a name="call.returnValue" />

----

> *mixed* $call->[**returnValue**](#call.returnValue)()
> throws [UndefinedResponseException]

Get the return value.

*An [UndefinedResponseException] will be thrown if the call has not yet
responded, or threw an exception.*

<a name="call.exception" />

----

> *[Throwable]* $call->[**exception**](#call.exception)()
> throws [UndefinedResponseException]

Get the thrown exception.

*An [UndefinedResponseException] will be thrown if the call has not yet
responded, or did not throw an exception.*

<a name="call.response" />

----

> *tuple\<[Throwable]|null,mixed>* $call->[**response**](#call.response)()
> throws [UndefinedResponseException]

Get the response.

*This method returns a 2-tuple. The first element is the thrown exception, or
`null` if no exception was thrown. The second element is the returned value,
or `null` if an exception was thrown.*

*An [UndefinedResponseException] will be thrown if the call has not yet
responded.*

<a name="call.hasResponded" />

----

> *boolean* $call->[**hasResponded**](#call.hasResponded)()

Returns true if this call has responded.

*A call that has "responded" has returned a value, or thrown an exception.*

<a name="call.isTraversable" />

----

> *boolean* $call->[**isTraversable**](#call.isTraversable)()

Returns true if this call has responded with a traversable.

*A call that has "responded" has returned a value, or thrown an exception.*

<a name="call.isGenerator" />

----

> *boolean* $call->[**isGenerator**](#call.isGenerator)()

Returns true if this call has responded with a generator.

*A call that has "responded" has returned a value, or thrown an exception.*

<a name="call.hasCompleted" />

----

> *boolean* $call->[**hasCompleted**](#call.hasCompleted)()

Returns true if this call has completed.

*When [generator spies] are in use, a call that returns a generator will not be
considered "complete" until the generator has been completely consumed via
iteration.*

*Similarly, when [traversable spies] are in use, a call that returns a
traversable will not be considered "complete" until the traversable has been
completely consumed via iteration.*

<a name="call.time" />

----

> *float* $call->[**time**](#call.time)()

Get the time at which the call occurred, in seconds since the Unix epoch.

<a name="call.responseTime" />

----

> *float|null* $call->[**responseTime**](#call.responseTime)()

Get the time at which the call responded, in seconds since the Unix epoch.

*If the call has not yet responded, `null` will be returned.*

*A call that has "responded" has returned a value, or thrown an exception.*

<a name="call.endTime" />

----

> *float|null* $call->[**endTime**](#call.endTime)()

Get the time at which the call completed, in seconds since the Unix epoch.

*If the call has not yet completed, `null` will be returned.*

*When [generator spies] are in use, a call that returns a generator will not be
considered "complete" until the generator has been completely consumed via
iteration.*

*Similarly, when [traversable spies] are in use, a call that returns a
traversable will not be considered "complete" until the traversable has been
completely consumed via iteration.*

<a name="call.responseDuration" />

----

> *float|null* $call->[**responseDuration**](#call.responseDuration)()

Get the call response duration, in seconds.

*If the call has not yet responded, `null` will be returned.*

*A call that has "responded" has returned a value, or thrown an exception.*

<a name="call.duration" />

----

> *float|null* $call->[**duration**](#call.duration)()

Get the call response duration, in seconds.

*If the call has not yet completed, `null` will be returned.*

*When [generator spies] are in use, a call that returns a generator will not be
considered "complete" until the generator has been completely consumed via
iteration.*

*Similarly, when [traversable spies] are in use, a call that returns a
traversable will not be considered "complete" until the traversable has been
completely consumed via iteration.*

<a name="call.sequenceNumber" />

----

> *integer* $call->[**sequenceNumber**](#call.sequenceNumber)()

Get the sequence number.

*The sequence number is a unique number assigned to every event that Phony
records. The numbers are assigned sequentially, meaning that sequence numbers
can be used to determine event order.*

<a name="call.calledWith" />

----

> *[verification][verification-api]* $call->[**calledWith**](#call.calledWith)(...$arguments)
> throws [AssertionException]

Throws an exception unless called with the supplied arguments.

*This method supports [mock handle substitution]. Any mock handles in
`$arguments` are equivalent to the mocks themselves.*

*See [Verifying that a call was made with specific arguments].*

<a name="call.checkCalledWith" />

----

> *[verification][verification-api]|null* $call->[**checkCalledWith**](#call.checkCalledWith)(...$arguments)

Checks if called with the supplied arguments.

*This method supports [mock handle substitution]. Any mock handles in
`$arguments` are equivalent to the mocks themselves.*

*See [Verifying that a call was made with specific arguments],
[Check verification].*

<a name="call.calledOn" />

----

> *[verification][verification-api]* $call->[**calledOn**](#call.calledOn)($value)
> throws [AssertionException]

Throws an exception unless the bound `$this` value is equal to the supplied
value.

*This method supports [mock handle substitution].*

*See [Verifying call closure binding].*

<a name="call.checkCalledOn" />

----

> *[verification][verification-api]|null* $call->[**checkCalledOn**](#call.checkCalledOn)($value)

Checks if the bound `$this` value is equal to the supplied value.

*This method supports [mock handle substitution].*

*See [Verifying call closure binding], [Check verification].*

<a name="call.returned" />

----

> *[verification][verification-api]* $call->[**returned**](#call.returned)($value = null)
> throws [AssertionException]

Throws an exception unless this call returned the supplied value.

*When called with no arguments, this method simply checks that the call returned
any value.*

*This method supports [mock handle substitution].*

*See [Verifying call return values].*

<a name="call.checkReturned" />

----

> *[verification][verification-api]|null* $call->[**checkReturned**](#call.checkReturned)($value = null)

Checks if this call returned the supplied value.

*When called with no arguments, this method simply checks that the call returned
any value.*

*This method supports [mock handle substitution].*

*See [Verifying call return values], [Check verification].*

<a name="call.threw" />

----

> *[verification][verification-api]* $call->[**threw**](#call.threw)($type = null)
> throws [AssertionException]

Throws an exception unless this call threw an exception of the supplied type.

*When called with no arguments, this method simply checks that the call threw
any exception.*

*When called with a string, this method checks that the call threw an exception
that is an instance of `$type`.*

*When called with an exception instance, this method checks that the call threw
an exception that is equal to the supplied instance.*

*When called with a [matcher], this method checks that the call threw an
exception that matches the supplied matcher.*

*This method supports [mock handle substitution].*

*See [Verifying call exceptions].*

<a name="call.checkThrew" />

----

> *[verification][verification-api]|null* $call->[**checkThrew**](#call.checkThrew)($type = null)

Checks if an exception of the supplied type was thrown.

*When called with no arguments, this method simply checks that the call threw
any exception.*

*When called with a string, this method checks that the call threw an exception
that is an instance of `$type`.*

*When called with an exception instance, this method checks that the call threw
an exception that is equal to the supplied instance.*

*When called with a [matcher], this method checks that the call threw an
exception that matches the supplied matcher.*

*This method supports [mock handle substitution].*

*See [Verifying call exceptions], [Check verification].*

<a name="call.produced" />

----

> *[verification][verification-api]* $call->[**produced**](#call.produced)($keyOrValue = null, $value = null)
> throws [AssertionException]

Checks if this call produced the supplied values.

*When called with no arguments, this method simply checks that the call produced
any value.*

*With a single argument, it checks that a value matching the argument was
produced.*

*With two arguments, it checks that a key and value matching the respective
arguments were produced together.*

*This method supports [mock handle substitution] for both keys and values.*

*See [Verifying values produced by calls].*

<a name="call.checkProduced" />

----

> *[verification][verification-api]|null* $call->[**checkProduced**](#call.checkProduced)($keyOrValue = null, $value = null)

Checks if this call produced the supplied values.

*When called with no arguments, this method simply checks that the call produced
any value.*

*With a single argument, it checks that a value matching the argument was
produced.*

*With two arguments, it checks that a key and value matching the respective
arguments were produced together.*

*This method supports [mock handle substitution] for both keys and values.*

*See [Verifying values produced by calls], [Check verification].*

<a name="call.producedAll" />

----

> *[verification][verification-api]* $call->[**producedAll**](#call.producedAll)(...$pairs)
> throws [AssertionException]

Throws an exception unless this call produced all of the supplied key-value
pairs, in the supplied order.

*Each value in `$pairs` is equivalent to a set of arguments passed to
[`produced()`](#call.produced).*

*This method supports [mock handle substitution] for both keys and values.*

*See [Verifying values produced by calls].*

<a name="call.checkProducedAll" />

----

> *[verification][verification-api]|null* $call->[**checkProducedAll**](#call.checkProducedAll)(...$pairs)

Checks if this call produced all of the supplied key-value pairs, in the
supplied order.

*Each value in `$pairs` is equivalent to a set of arguments passed to
[`checkProduced()`](#call.checkProduced).*

*This method supports [mock handle substitution] for both keys and values.*

*See [Verifying values produced by calls], [Check verification].*

<a name="call.received" />

----

> *[verification][verification-api]* $call->[**received**](#call.received)($value = null)
> throws [AssertionException]

Throws an exception unless this call received the supplied value.

*When called with no arguments, this method simply checks that the call received
any value.*

*This method supports [mock handle substitution].*

*See [Verifying values received by calls].*

<a name="call.checkReceived" />

----

> *[verification][verification-api]|null* $call->[**checkReceived**](#call.checkReceived)($value = null)

Checks if this call received the supplied value.

*When called with no arguments, this method simply checks that the call received
any value.*

*This method supports [mock handle substitution].*

*See [Verifying values received by calls], [Check verification].*

<a name="call.receivedException" />

----

> *[verification][verification-api]* $call->[**receivedException**](#call.receivedException)($type = null)
> throws [AssertionException]

Throws an exception unless this call received an exception of the supplied type.

*When called with no arguments, this method simply checks that the call received
any exception.*

*When called with a string, this method checks that the call received an
exception that is an instance of `$type`.*

*When called with an exception instance, this method checks that the call
received an exception that is equal to the supplied instance.*

*When called with a [matcher], this method checks that the call received an
exception that matches the supplied matcher.*

*This method supports [mock handle substitution].*

*See [Verifying exceptions received by calls].*

<a name="call.checkReceivedException" />

----

> *[verification][verification-api]|null* $call->[**checkReceivedException**](#call.checkReceivedException)($type = null)

Checks if this call received an exception of the supplied type.

*When called with no arguments, this method simply checks that the call received
any exception.*

*When called with a string, this method checks that the call received an
exception that is an instance of `$type`.*

*When called with an exception instance, this method checks that the call
received an exception that is equal to the supplied instance.*

*When called with a [matcher], this method checks that the call received an
exception that matches the supplied matcher.*

*This method supports [mock handle substitution].*

*See [Verifying exceptions received by calls], [Check verification].*

<a name="call.never" />

----

> *fluent* $call->[**never**](#call.never)()

Requires that the next verification never matches.

*See [Verifying that a call event happened an exact number of times].*

<a name="call.once" />

----

> *fluent* $call->[**once**](#call.once)()

Requires that the next verification matches only once.

*See [Verifying that a call event happened an exact number of times].*

<a name="call.twice" />

----

> *fluent* $call->[**twice**](#call.twice)()

Requires that the next verification matches exactly two times.

*See [Verifying that a call event happened an exact number of times].*

<a name="call.thrice" />

----

> *fluent* $call->[**thrice**](#call.thrice)()

Requires that the next verification matches exactly three times.

*See [Verifying that a call event happened an exact number of times].*

<a name="call.times" />

----

> *fluent* $call->[**times**](#call.times)($times)

Requires that the next verification matches exactly `$times` times.

*See [Verifying that a call event happened an exact number of times].*

<a name="call.atLeast" />

----

> *fluent* $call->[**atLeast**](#call.atLeast)($minimum)

Requires that the next verification matches a number of times greater than or
equal to `$minimum`.

*See [Verifying that a call event happened a bounded number of times].*

<a name="call.atMost" />

----

> *fluent* $call->[**atMost**](#call.atMost)($maximum)

Requires that the next verification matches a number of times less than or equal
to `$maximum`.

*See [Verifying that a call event happened a bounded number of times].*

<a name="call.between" />

----

> *fluent* $call->[**between**](#call.between)($minimum, $maximum)

Requires that the next verification matches a number of times greater than or
equal to `$minimum`, and less than or equal to `$maximum`.

*See [Verifying that a call event happened a bounded number of times].*

<a name="call.always" />

----

> *fluent* $call->[**always**](#call.always)()

Requires that the next verification matches for all possible items.

*See [Verifying that all call events happen the same way].*

### The arguments API

<a name="arguments.all" />

----

> *array\<mixed>* $arguments->[**all**](#arguments.all)()

Get the arguments as an array.

*Arguments passed by reference will be references in the returned array.*

<a name="arguments.has" />

----

> *boolean* $arguments->[**has**](#arguments.has)($index = 0)

Returns `true` if an argument exists at `$index`.

*Negative indices are offset from the end of the list. That is, `-1` indicates
the last element, and `-2` indicates the second last element.*

<a name="arguments.get" />

----

> *mixed* $arguments->[**get**](#arguments.get)($index = 0)
> throws [UndefinedArgumentException]

Get the argument at `$index`.

*Negative indices are offset from the end of the list. That is, `-1` indicates
the last element, and `-2` indicates the second last element.*

<a name="arguments.set" />

----

> *fluent* $arguments->[**set**](#arguments.set)($indexOrValue = null, $value = null)
> throws [UndefinedArgumentException]

Set an argument by index.

*If called with no arguments, sets the first argument to `null`.*

*If called with one argument, sets the first argument to `$indexOrValue`.*

*If called with two arguments, sets the argument at `$indexOrValue` to
`$value`.*

<a name="arguments.copy" />

----

> *[arguments][arguments-api]* $arguments->[**copy**](#arguments.copy)()

Copy these arguments, breaking any references.

### Retrieving calls from a spy

To get the first call, use [`firstCall()`](#spy.firstCall):

```php
$spy->firstCall();
```

To get the last call, use [`lastCall()`](#spy.lastCall):

```php
$spy->lastCall();
```

To get a specific call by index, use [`callAt()`](#spy.callAt):

```php
$spy->callAt(0); // returns the first call
$spy->callAt(9); // returns the tenth call
```

These methods will throw an exception if no call is found.

### Verifying call input

#### Verifying that a call was made with specific arguments

To verify input arguments, use [`calledWith()`](#call.calledWith). Arguments
passed to [`calledWith()`](#call.calledWith) can be literal values, or
[matchers], including [shorthand matchers]:

```php
$call->calledWith();         // called with no arguments
$call->calledWith('a', 'b'); // called with 'a' followed by 'b'
$call->calledWith('a', '*'); // called with 'a' followed by 0-n arguments
$call->calledWith('a', '~'); // called with 'a' followed by exactly 1 argument
```

Arguments can also be retrieved with [`arguments()`](#call.arguments) or
[`argument()`](#call.argument):

```php
$call->arguments(); // all arguments as an array
$call->argument();  // first argument
$call->argument(1); // second argument
```

#### Verifying call closure binding

Where [closure binding] is supported, the bound object can be verified using
[`calledOn()`](#call.calledOn):

```php
$call->calledOn($object);
```

### Verifying call output

#### Verifying call return values

To verify a call's return value, use [`returned()`](#call.returned):

```php
$call->returned();    // returned anything
$call->returned('a'); // returned 'a'
```

#### Verifying call exceptions

To verify that a call threw an exception, use [`threw()`](#call.threw):

```php
$call->threw();                                         // threw any exception
$call->threw('RuntimeException');                       // threw a runtime exception
$call->threw(new RuntimeException('You done goofed.')); // threw a runtime exception with a specific message
```

### Verifying calls with generators or traversables

*Phony* records values and exceptions that are passed in and out of
[generators]:

- When a generator uses the [yield] keyword, this is recorded as a *produced
  value*.
- When calling code uses [Generator::send()], this is recorded as a *sent
  value*.
- When calling code uses [Generator::throw()], this is recorded as a *sent
  exception*.
- When a generator ends, either implicitly, or with an explicit `return`
  statement, this is recorded as a *returned value* of `null` (as generators
  cannot return values).
- When a generator ends because of an exception, this is recorded as a *thrown
  exception*.

This behavior is enabled by default for generators, and can optionally be
enabled for other traversables by calling
[`setUseTraversableSpies()`](#spy.setUseTraversableSpies) on a spy:

```php
$spy->setUseTraversableSpies(true);
```

For other traversables, such as arrays and iterators, the values are recorded as
*produced values*.

#### Verifying values produced by calls

To verify that a value was produced by a call, use
[`produced()`](#call.produced):

```php
$call->produced();         // produced anything
$call->produced('a');      // produced 'a' with any key
$call->produced('a', 'b'); // produced 'b' with key 'a'
```

To verify that a set of values were produced by a call in a specific order, use
[`producedAll()`](#call.producedAll):

```php
$call->producedAll();                // produced nothing (an empty traversable)
$call->producedAll('a', 'b');        // produced 'a', then 'b', with any keys
$call->producedAll('a', ['b', 'c']); // produced 'a' with any key, then 'c' with key 'b'
```

#### Verifying values received by calls

To verify that a value was received by a call, use
[`received()`](#call.received):

```php
$call->received();    // received anything
$call->received('a'); // received 'a'
```

#### Verifying exceptions received by calls

To verify that an exception was received by a call, use
[`receivedException()`](#call.receivedException):

```php
$call->receivedException();                                         // received any exception
$call->receivedException('RuntimeException');                       // received a runtime exception
$call->receivedException(new RuntimeException('You done goofed.')); // received a runtime exception with a specific message
```

### Verifying cardinality with calls

Cardinality modifiers change the amount of times a call, or other event, must
meet the requirements of a subsequent verification.

Cardinality must be specified **before** verification, and can be applied to
any verification call:

```php
$call->never()->calledWith('a'); // not called with 'a'
$call->never()->returned('b');   // did not return 'b'
$call->never()->threw();         // did not throw an exception
```

The default cardinality is `atLeast(1)`, meaning verifications will pass if at
least one matching event was recorded.

#### Verifying that a call event happened an exact number of times

To verify that an event happened an exact number of times, use one of
[`never()`](#call.never), [`once()`](#call.once), [`twice()`](#call.twice),
[`thrice()`](#call.thrice), or [`times()`](#call.times):

```php
$call->never()->produced('a');   // never produced 'a'
$call->once()->produced('a');    // produced 'a' exactly 1 time
$call->twice()->produced('a');   // produced 'a' exactly 2 times
$call->thrice()->produced('a');  // produced 'a' exactly 3 times
$call->times(10)->produced('a'); // produced 'a' exactly 10 times

$call->never()->received('a');   // never received 'a'
$call->once()->received('a');    // received 'a' exactly 1 time
$call->twice()->received('a');   // received 'a' exactly 2 times
$call->thrice()->received('a');  // received 'a' exactly 3 times
$call->times(10)->received('a'); // received 'a' exactly 10 times
```

#### Verifying that a call event happened a bounded number of times

To verify that an event happened a bounded number of times, use one of
[`atLeast()`](#call.atLeast), [`atMost()`](#call.atMost), or
[`between()`](#call.between):

```php
$call->atLeast(2)->produced('a');    // produced 'a' 2 or more times
$call->atMost(3)->produced('a');     // produced 'a' no more than 3 times
$call->between(2, 4)->produced('a'); // produced 'a' 2, 3, or 4 times

$call->atLeast(2)->received('a');    // received 'a' 2 or more times
$call->atMost(3)->received('a');     // received 'a' no more than 3 times
$call->between(2, 4)->received('a'); // received 'a' 2, 3, or 4 times
```

#### Verifying that all call events happen the same way

To verify that all events happen the same way, use [`always()`](#call.always):

```php
$call->always()->produced('a'); // always produced 'a'
$call->always()->received('b'); // always received 'b'
```

Note that [`always()`](#call.always) does not interfere with other cardinality
modifiers, and can be combined to produce powerful verifications:

```php
$call->twice()->always()->produced('a'); // produced exactly 2 values, both of which are 'a'
```

## Verification

"Verification" is a general term, used to refer to any *Phony* method or
function that asserts that something happened. *Phony* implements many
verification methods and functions across its APIs, but they all behave in a
similar manner.

Each verification method or function has two variants. For most use-cases, the
[standard verification] style will be the best fit. When verification fails,
this variant will record an assertion failure with the [testing framework] in
use. This typically (but not always) involves an exception being thrown.

For situations where this is not desirable, the [check verification] style can
be used. When verification fails, this variant will simply return `null`, and no
assertion failure will be recorded with the [testing framework] in use.

For information on specific verification methods, see these sections:

- [Spies]
    - [Call verification]
        - [Call count]
        - [Individual calls]
    - [Verifying spy input]
        - [Verifying that a call was made]
        - [Verifying that a spy was called with specific arguments]
        - [Verifying spy closure binding]
    - [Verifying spy output]
        - [Verifying spy return values]
        - [Verifying spy exceptions]
    - [Verifying spies with generators or traversables]
        - [Verifying values produced by spies]
        - [Verifying values received by spies]
        - [Verifying exceptions received by spies]
    - [Verifying cardinality with spies]
        - [Verifying that a spy event happened an exact number of times]
        - [Verifying that a spy event happened a bounded number of times]
        - [Verifying that all spy events happen the same way]
- [Calls]
    - [Verifying call input]
        - [Verifying that a call was made with specific arguments]
        - [Verifying call closure binding]
    - [Verifying call output]
        - [Verifying call return values]
        - [Verifying call exceptions]
    - [Verifying calls with generators or traversables]
        - [Verifying values produced by calls]
        - [Verifying values received by calls]
        - [Verifying exceptions received by calls]
    - [Verifying cardinality with calls]
        - [Verifying that a call event happened an exact number of times]
        - [Verifying that a call event happened a bounded number of times]
        - [Verifying that all call events happen the same way]
- [Verification]
    - [The verification result API]
    - [The event API]
    - [The order verification API]
    - [Standard verification]
    - [Check verification]
    - [Order verification]
        - [Dynamic order verification]
        - [Order verification caveats]
            - [Intermediate events in order verification]
            - [Similar events in order verification]
    - [Verifying that there was no interaction with a mock]

### The verification result API

<a name="verification.arguments" />

----

> *[arguments][arguments-api]* $verification->[**arguments**](#verification.arguments)()
> throws [UndefinedCallException]

Get the arguments of the first call.

<a name="verification.hasCalls" />

----

> *boolean* $verification->[**hasCalls**](#verification.hasCalls)()

Returns `true` if this verification matched any calls.

<a name="verification.callCount" />

----

> *integer* $verification->[**callCount**](#verification.callCount)()

Get the number of calls.

<a name="verification.allCalls" />

----

> *array\<[call][call-api]>* $verification->[**allCalls**](#verification.allCalls)()

Get all calls as an array.

<a name="verification.firstCall" />

----

> *[call][call-api]* $verification->[**firstCall**](#verification.firstCall)()
> throws [UndefinedCallException]

Get the first call.

<a name="verification.lastCall" />

----

> *[call][call-api]* $verification->[**lastCall**](#verification.lastCall)()
> throws [UndefinedCallException]

Get the last call.

<a name="verification.callAt" />

----

> *[call][call-api]* $verification->[**callAt**](#verification.callAt)($index = 0)
> throws [UndefinedCallException]

Get the call at `$index`.

*Negative indices are offset from the end of the list. That is, `-1` indicates
the last element, and `-2` indicates the second last element.*

<a name="verification.hasEvents" />

----

> *boolean* $verification->[**hasEvents**](#verification.hasEvents)()

Returns `true` if this verification matched any events.

<a name="verification.eventCount" />

----

> *integer* $verification->[**eventCount**](#verification.eventCount)()

Get the number of events.

<a name="verification.allEvents" />

----

> *array\<[event][event-api]>* $verification->[**allEvents**](#verification.allEvents)()

Get all events as an array.

<a name="verification.firstEvent" />

----

> *[event][event-api]* $verification->[**firstEvent**](#verification.firstEvent)()
> throws [UndefinedEventException]

Get the first event.

<a name="verification.lastEvent" />

----

> *[event][event-api]* $verification->[**lastEvent**](#verification.lastEvent)()
> throws [UndefinedEventException]

Get the last event.

<a name="verification.eventAt" />

----

> *[event][event-api]* $verification->[**eventAt**](#verification.eventAt)($index = 0)
> throws [UndefinedEventException]

Get the event at `$index`.

*Negative indices are offset from the end of the list. That is, `-1` indicates
the last element, and `-2` indicates the second last element.*

### The event API

<a name="event.time" />

----

> *float* $event->[**time**](#event.time)()

Get the time at which the event occurred, in seconds since the Unix epoch.

<a name="event.sequenceNumber" />

----

> *integer* $event->[**sequenceNumber**](#event.sequenceNumber)()

Get the sequence number.

*The sequence number is a unique number assigned to every event that Phony
records. The numbers are assigned sequentially, meaning that sequence numbers
can be used to determine event order.*

### The order verification API

<a name="facade.checkInOrder" />

----

> *[verification][verification-api]|null* [**checkInOrder**](#facade.checkInOrder)(...$events) *(with [use function])*<br />
> *[verification][verification-api]|null* x\\[**checkInOrder**](#facade.checkInOrder)(...$events) *(without [use function])*<br />
> *[verification][verification-api]|null* Phony::[**checkInOrder**](#facade.checkInOrder)(...$events) *(static)*

Checks if the supplied events happened in chronological order.

*Each value in `$events` should be an event, or a [verification result].*

*See [Order verification], [Check verification].*

<a name="facade.inOrder" />

----

> *[verification][verification-api]* [**inOrder**](#facade.inOrder)(...$events) *(with [use function])*
> throws [AssertionException]<br />
> *[verification][verification-api]* x\\[**inOrder**](#facade.inOrder)(...$events) *(without [use function])*
> throws [AssertionException]<br />
> *[verification][verification-api]* Phony::[**inOrder**](#facade.inOrder)(...$events) *(static)*
> throws [AssertionException]

Throws an exception unless the supplied events happened in chronological order.

*Each value in `$events` should be an event, or a [verification result].*

*See [Order verification].*

<a name="facade.checkInOrderSequence" />

----

> *[verification][verification-api]|null* [**checkInOrderSequence**](#facade.checkInOrderSequence)($events) *(with [use function])*<br />
> *[verification][verification-api]|null* x\\[**checkInOrderSequence**](#facade.checkInOrderSequence)($events) *(without [use function])*<br />
> *[verification][verification-api]|null* Phony::[**checkInOrderSequence**](#facade.checkInOrderSequence)($events) *(static)*

Checks if the supplied event sequence happened in chronological order.

*Each value in `$events` should be an event, or a [verification result].*

*See [Dynamic order verification], [Check verification].*

<a name="facade.inOrderSequence" />

----

> *[verification][verification-api]* [**inOrderSequence**](#facade.inOrderSequence)($events) *(with [use function])*
> throws [AssertionException]<br />
> *[verification][verification-api]* x\\[**inOrderSequence**](#facade.inOrderSequence)($events) *(without [use function])*
> throws [AssertionException]<br />
> *[verification][verification-api]* Phony::[**inOrderSequence**](#facade.inOrderSequence)($events) *(static)*
> throws [AssertionException]

Throws an exception unless the supplied event sequence happened in chronological
order.

*Each value in `$events` should be an event, or a [verification result].*

*See [Dynamic order verification].*

<a name="facade.checkAnyOrder" />

----

> *[verification][verification-api]|null* [**checkAnyOrder**](#facade.checkAnyOrder)(...$events) *(with [use function])*<br />
> *[verification][verification-api]|null* x\\[**checkAnyOrder**](#facade.checkAnyOrder)(...$events) *(without [use function])*<br />
> *[verification][verification-api]|null* Phony::[**checkAnyOrder**](#facade.checkAnyOrder)(...$events) *(static)*

Checks that at least one event is supplied.

*Each value in `$events` should be an event, or a [verification result].*

*See [Order verification], [Check verification].*

<a name="facade.anyOrder" />

----

> *[verification][verification-api]* [**anyOrder**](#facade.anyOrder)(...$events) *(with [use function])*
> throws [AssertionException]<br />
> *[verification][verification-api]* x\\[**anyOrder**](#facade.anyOrder)(...$events) *(without [use function])*
> throws [AssertionException]<br />
> *[verification][verification-api]* Phony::[**anyOrder**](#facade.anyOrder)(...$events) *(static)*
> throws [AssertionException]

Throws an exception unless at least one event is supplied.

*Each value in `$events` should be an event, or a [verification result].*

*See [Order verification].*

<a name="facade.checkAnyOrderSequence" />

----

> *[verification][verification-api]|null* [**checkAnyOrderSequence**](#facade.checkAnyOrderSequence)($events) *(with [use function])*<br />
> *[verification][verification-api]|null* x\\[**checkAnyOrderSequence**](#facade.checkAnyOrderSequence)($events) *(without [use function])*<br />
> *[verification][verification-api]|null* Phony::[**checkAnyOrderSequence**](#facade.checkAnyOrderSequence)($events) *(static)*

Checks if the supplied event sequence contains at least one event.

*Each value in `$events` should be an event, or a [verification result].*

*See [Dynamic order verification], [Check verification].*

<a name="facade.anyOrderSequence" />

----

> *[verification][verification-api]* [**anyOrderSequence**](#facade.anyOrderSequence)($events) *(with [use function])*
> throws [AssertionException]<br />
> *[verification][verification-api]* x\\[**anyOrderSequence**](#facade.anyOrderSequence)($events) *(without [use function])*
> throws [AssertionException]<br />
> *[verification][verification-api]* Phony::[**anyOrderSequence**](#facade.anyOrderSequence)($events) *(static)*
> throws [AssertionException]

Throws an exception unless the supplied event sequence contains at least one
event.

*Each value in `$events` should be an event, or a [verification result].*

*See [Dynamic order verification].*

### Standard verification

"Standard" verification involves "recording" failures with the
[testing framework] in use. For most testing frameworks, this involves throwing
a specific type of exception used by the framework.

In some cases, the framework will not use a specific exception type, and will
treat *any* exception as a failure. In this case, and in the case that no
[testing framework] is being used, *Phony* will simply throw its own
[AssertionException] on failure.

In rare cases, such as use with [SimpleTest], failures do not result in any
exception being thrown. The failure is recorded with the [testing framework]
via whatever method the framework uses instead. This may mean that execution
continues past the failed verification call.

As with [check verification], verification successes will return a
[verification result], which can be used for further verifications, including
[order verification].

### Check verification

In contrast to [standard verification], "check" verification does not involve
integration with the [testing framework] in use.

In the case of verification failure, `null` will be returned. As with
[standard verification], verification successes will return a
[verification result], which can be used for further verifications, including
[order verification].

Check verification may be used to integrate with [testing frameworks] that do
not yet have first-class support in *Phony*:

```php
expect($spy->checkCalled())->to->be->ok;
```

### Order verification

To verify that events happened in a particular order, use
[`inOrder()`](#facade.inOrder):

```php
// with `use function`
inOrder(
    $spyA->calledWith('a'),
    $spyA->returned('x'),
    $spyB->calledWith('b'),
    $spyB->returned('y')
);

// without `use function`
x\inOrder(
    $spyA->calledWith('a'),
    $spyA->returned('x'),
    $spyB->calledWith('b'),
    $spyB->returned('y')
);

// static
Phony::inOrder(
    $spyA->calledWith('a'),
    $spyA->returned('x'),
    $spyB->calledWith('b'),
    $spyB->returned('y')
);
```

To relax order verification within a call to [`inOrder()`](#facade.inOrder), use
[`anyOrder()`](#facade.anyOrder):

```php
// with `use function`
inOrder(
    anyOrder(
        $spyA->calledWith('a'),
        $spyB->calledWith('b')
    ),
    anyOrder(
        $spyA->returned('x'),
        $spyB->returned('y')
    )
);

// without `use function`
x\inOrder(
    x\anyOrder(
        $spyA->calledWith('a'),
        $spyB->calledWith('b')
    ),
    x\anyOrder(
        $spyA->returned('x'),
        $spyB->returned('y')
    )
);

// static
Phony::inOrder(
    Phony::anyOrder(
        $spyA->calledWith('a'),
        $spyB->calledWith('b')
    ),
    Phony::anyOrder(
        $spyA->returned('x'),
        $spyB->returned('y')
    )
);
```

Event order can be verified across all defined mocks, stubs, and spies, using
any [verification] implemented by *Phony*. Both [`inOrder()`](#facade.inOrder)
and [`anyOrder()`](#facade.anyOrder) will accept any number of
[verification results] to verify.

#### Dynamic order verification

In addition to [`inOrder()`](#facade.inOrder) and
[`anyOrder()`](#facade.anyOrder), there are also
[`inOrderSequence()`](#facade.inOrderSequence) and
[`anyOrderSequence()`](#facade.anyOrderSequence) variants that accept any array
or traversable containing [verification results]. These variants can be used
when the number of events to verify is dynamic:

```php
$calledEvents = [];
$returnedEvents = [];

foreach ($spies as $spy) {
    $calledEvents[] = $spy->called();
    $returnedEvents[] = $spy->returned();
}

// with `use function`
inOrderSequence(
    [
        anyOrderSequence($calledEvents),
        anyOrderSequence($returnedEvents),
    ]
);

// without `use function`
x\inOrderSequence(
    [
        x\anyOrderSequence($calledEvents),
        x\anyOrderSequence($returnedEvents),
    ]
);

// static
Phony::inOrderSequence(
    [
        Phony::anyOrderSequence($calledEvents),
        Phony::anyOrderSequence($returnedEvents),
    ]
);
```

Note that [`inOrder()`](#facade.inOrder), [`anyOrder()`](#facade.anyOrder),
[`inOrderSequence()`](#facade.inOrderSequence), and
[`anyOrderSequence()`](#facade.anyOrderSequence) can be used together in
whatever fashion is most appropriate for the situation:

```php
inOrder(
    anyOrderSequence($calledEvents),
    anyOrderSequence($returnedEvents),
);
```

#### Order verification caveats

##### Intermediate events in order verification

Order verification **does not** verify that the specified events were the *only*
events that occurred:

```php
$spy('a');
$spy('b');
$spy('c');

$withA = $spy->calledWith('a');
$withB = $spy->calledWith('b');
$withC = $spy->calledWith('c');

inOrder($withA, $withB); // passes
inOrder($withA, $withC); // passes
inOrder($withB, $withC); // passes
```

To verify that specific events did not occur, use [`$spy->never()`](#spy.never)
or [`$call->never()`](#call.never).

##### Similar events in order verification

Order verification **does not** verify that the specified events happened in
*only* one order:

```php
$spy('a');
$spy('b');
$spy('a');

$withA = $spy->calledWith('a');
$withB = $spy->calledWith('b');

inOrder($withA, $withB);         // passes
inOrder($withB, $withA);         // passes
inOrder($withA, $withB, $withA); // passes
```

However, it *is* possible to verify event order more explicitly using
[individual calls]:

```php
inOrder($withA->firstCall(), $withB); // passes
inOrder($withA->lastCall(), $withB);  // fails
```

Of course, extracting [individual calls] is only helpful when verifying the
order of calls. Similar methods exist to aid in explicitly verifying the order
of other types of events:

```php
$spy = spy(
    function ($x) {
        return $x;
    }
);

$spy('a');
$spy('b');
$spy('a');

$returnedA = $spy->returned('a');
$returnedB = $spy->returned('b');

inOrder($returnedA->firstEvent(), $returnedB); // passes
inOrder($returnedA->lastEvent(), $returnedB);  // fails
```

### Verifying that there was no interaction with a mock

To verify that there was no interaction with a mock, use
[`noInteraction()`](#handle.noInteraction) on any [mock handle]:

```php
$handle = mock('ClassA');
$mock = $handle->mock();

$handle->noInteraction(); // passes

$mock->methodA();

$handle->noInteraction(); // fails
```

This verification will fail if any of the mock's methods have been called.

## Matchers

*Matchers* are used to determine whether the arguments of a call match certain
criteria. Utilized correctly, they can make verifications less brittle, and
improve the quality of a test suite.

*Phony* implements only a few matchers itself, and provides first-class support
for numerous third-party matcher libraries.

### The matcher API

<a name="facade.any" />

----

> *[matcher][matcher-api]* [**any**](#facade.any)() *(with [use function])*<br />
> *[matcher][matcher-api]* x\\[**any**](#facade.any)() *(without [use function])*<br />
> *[matcher][matcher-api]* Phony::[**any**](#facade.any)() *(static)*

Create a new ["any" matcher].

<a name="facade.equalTo" />

----

> *[matcher][matcher-api]* [**equalTo**](#facade.equalTo)($value) *(with [use function])*<br />
> *[matcher][matcher-api]* x\\[**equalTo**](#facade.equalTo)($value) *(without [use function])*<br />
> *[matcher][matcher-api]* Phony::[**equalTo**](#facade.equalTo)($value) *(static)*

Create a new ["equal to" matcher].

<a name="matcher.matches" />

----

> *boolean* $matcher->[**matches**](#matcher.matches)($value)

Returns `true` if `$value` matches this matcher's criteria.

<a name="matcher.describe" />
<a name="matcher.__toString" />

----

> *string* $matcher->[**describe**](#matcher.describe)() or
> [**"$matcher"**](#matcher.__toString)

Describe this matcher.

### The wildcard matcher API

<a name="facade.wildcard" />

----

> *[wildcard][wildcard-api]* [**wildcard**](#facade.wildcard)($value = null, $minimumArguments = 0, $maximumArguments = null) *(with [use function])*<br />
> *[wildcard][wildcard-api]* x\\[**wildcard**](#facade.wildcard)($value = null, $minimumArguments = 0, $maximumArguments = null) *(without [use function])*<br />
> *[wildcard][wildcard-api]* Phony::[**wildcard**](#facade.wildcard)($value = null, $minimumArguments = 0, $maximumArguments = null) *(static)*

Create a new ["wildcard" matcher].

*The `$value` parameter accepts a value, or a [matcher], to check against each
argument that the wildcard matches.*

<a name="wildcard.matcher" />

----

> *[matcher][matcher-api]* $wildcard->[**matcher**](#wildcard.matcher)()

Get the matcher to use for each argument.

<a name="wildcard.minimumArguments" />

----

> *integer* $wildcard->[**minimumArguments**](#wildcard.minimumArguments)()

Get the minimum number of arguments to match.

<a name="wildcard.maximumArguments" />

----

> *integer|null* $wildcard->[**maximumArguments**](#wildcard.maximumArguments)()

Get the maximum number of arguments to match.

<a name="wildcard.describe" />
<a name="wildcard.__toString" />

----

> *string* $wildcard->[**describe**](#wildcard.describe)() or
> [**"$wildcard"**](#wildcard.__toString)

Describe this matcher.

### Matcher integrations

#### [Counterpart] matchers

[Counterpart] is a stand-alone matcher library. Its matchers can be used in any
*Phony* verification:

```php
$spy->calledWith(Counterpart\Matchers::isEqual('a'));
```

#### [Hamcrest] matchers

[Hamcrest] is a popular stand-alone matcher library, that originated in Java,
and has been ported to many languages, including an "official" port for PHP. Its
matchers can be used in any *Phony* verification:

```php
$spy->calledWith(equalTo('a'));
```

#### [Mockery] matchers

[Mockery] is a mocking library, similar to *Phony*. [Mockery matchers] can be
used in any *Phony* verification:

```php
$spy->calledWith(Mockery::mustBe('a'));
```

#### [Phake] matchers

[Phake] is a mocking library, similar to *Phony*. [Phake matchers] can be used
in any *Phony* verification:

```php
$spy->calledWith(Phake::equalTo('a'));
```

#### [PHPUnit] constraints

[PHPUnit] is a popular unit testing framework. [PHPUnit matchers] \(referred to
as "constraints") can be used in any *Phony* verification:

```php
// where $this is a PHPUnit test case
$spy->calledWith($this->equalTo('a'));
```

#### [Prophecy] argument tokens

[Prophecy] is a mocking library, similar to *Phony*. [Prophecy matchers]
\(referred to as "argument tokens") can be used in any *Phony* verification:

```php
$spy->calledWith(Prophecy\Argument::exact('a'));
```

#### [SimpleTest] expectations

[SimpleTest] is a legacy unit testing framework. [SimpleTest matchers]
\(referred to as "expectations") can be used in any *Phony* verification:

```php
$spy->calledWith(new EqualExpectation('a'));
```

### Shorthand matchers

*Phony* provides two "shorthand matchers"; `'~'`, and `'*'`. These strings are
automatically transformed into specific matchers:

- `'~'` is equivalent to an ["any" matcher]
- `'*'` is equivalent to a ["wildcard" matcher]

For typical *Phony* usage, this results in much simpler verifications. For
example, the following verifications are exactly equivalent:

```php
$spy->calledWith('a', any(), 'b', wildcard());
$spy->calledWith('a', '~', 'b', '*');
```

In the case that a verification expects a literal `'~'`, or `'*'` value, the
["equal to" matcher] can be used to prevent *Phony* interpreting shorthand
matchers:

```php
$spy->calledWith(equalTo('~'), equalTo('*'));
```

### The "any" matcher

This matcher matches a single argument of any value:

```php
$matcher = any($value);        // with `use function`
$matcher = x\any($value);      // without `use function`
$matcher = Phony::any($value); // static

$spy->calledWith(any()); // typical usage
```

### The "equal to" matcher

This is the default matcher used by *Phony*. It takes a single argument, and
matches values that are equal to that argument:

```php
$matcher = equalTo($value);        // with `use function`
$matcher = x\equalTo($value);      // without `use function`
$matcher = Phony::equalTo($value); // static

$spy->calledWith(equalTo('a')); // typical usage
```

This matcher is equivalent to strict comparison (`===`), except that it does not
require objects to be the same *instance*:

```php
$matcher = equalTo((object) ['a' => 0]);

var_dump($matcher->matches((object) ['a' => 0]));    // outputs 'bool(true)'
var_dump($matcher->matches((object) ['a' => null])); // outputs 'bool(false)'
```

#### When to use the "equal to" matcher

In most cases, it's not necessary to create an "equal to" matcher manually,
because *Phony* implicitly wraps anything that's not already a matcher. For
example, this verification:

```php
$spy->calledWith('a');
```

is exactly equivalent to this one:

```php
$spy->calledWith(equalTo('a'));
```

In fact, there are only two circumstances in which this matcher should be used.
The first is when a verification expects a literal `*` or `~` string (which
*Phony* would otherwise interpret as special [shorthand matchers]):

```php
$spy->calledWith(equalTo('~'), equalTo('*'));
```

The second circumstance is when a verification expects an actual matcher as an
argument:

```php
$spy->calledWith(equalTo(new EqualToMatcher('a')));
```

#### Special cases for the "equal to" matcher

For certain types of values, the "equal to" matcher will exhibit special
behavior, in order to improve the usefulness of its comparisons, or to improve
performance in common use cases.

##### Comparing exceptions

When an exception is compared, some internal PHP details are stripped from the
output, including file path, line number, and stack trace.

In the following example, note that differing line numbers are ignored, but
differing codes are not:

```php
$matcher = equalTo(new Exception('x'));

$a = new Exception('x');
$b = new Exception('x', 1);

echo $matcher->matches($a) ? 'true' : 'false'; // outputs 'true'
echo $matcher->matches($b) ? 'true' : 'false'; // outputs 'false'
```

##### Comparing mocks

When a mock is compared, some internal *Phony* details are ignored. In addition,
if a [label][labeling mocks] has been set on the mock, it will be included in
the comparison.

In the following example, note that differing mock behaviors are ignored, but
differing labels are not:

```php
$matcher = equalTo(mock('ClassX')->setLabel('x')->mock());

$a = mock('ClassX')->setLabel('x');
$a->methodX->returns('x');

echo $matcher->matches($a->mock()) ? 'true' : 'false'; // outputs 'true'

$b = mock('ClassX')->setLabel('y');
$c = mock('ClassX');

echo $matcher->matches($b->mock()) ? 'true' : 'false'; // outputs 'false'
echo $matcher->matches($c->mock()) ? 'true' : 'false'; // outputs 'false'
```

Since mocks are labeled with a unique integer by default, they can normally be
used to differentiate calls without requiring the use of an 'identical to'
matcher:

```php
$a = mock('ClassX')->mock();
$b = mock('ClassX')->mock();

$stub = stub();
$stub->with($a)->returns('a');
$stub->with($b)->returns('b');

echo $stub($a); // outputs 'a'
echo $stub($b); // outputs 'b'
```

### The "wildcard" matcher

The "wildcard" matcher is a special matcher that can match multiple arguments:

```php
$spy('a', 'b', 'c');

$spy->calledWith('a', wildcard()); // verification passes
$spy->calledWith('a', any());      // verification fails (not enough arguments)
```

It is usually much simpler to take advantage of [shorthand matchers] when
dealing with wildcards:

```php
$spy('a', 'b', 'c');

$spy->calledWith('a', '*'); // verification passes
$spy->calledWith('a', '~'); // verification fails (not enough arguments)
```

By default, wildcard matchers will match any argument value. This behavior can
be modified by wrapping any non-wildcard matcher (including literal values) in a
wildcard. This is accomplished by passing the matcher to the wildcard as the
first argument:

```php
$spy('a', 'a');

$spy->calledWith(wildcard(equalTo('a'))); // verification passes
$spy->calledWith(wildcard('a'));          // verification passes

$spy->calledWith(wildcard(equalTo('b'))); // verification fails
$spy->calledWith(wildcard('b'));          // verification fails
```

Wildcards can also have minimum and/or maximum options, which limit how many
arguments they match. The second and third parameters, respectively, are for
specifying the minimum, and maximum argument counts:

```php
$spy('a', 'b', 'c');

$spy->calledWith(wildcard('~', 2, 3)); // verification passes
$spy->calledWith(wildcard('~', 4));    // verification fails (too few arguments)
$spy->calledWith(wildcard('~', 0, 2)); // verification fails (too many arguments)
```

The behavior of wildcard matchers is only well defined when they appear at the
end of a matcher list. If a wildcard appears before any other matcher, any
behavior exhibited by *Phony* is not guaranteed, and may change in future
versions:

```php
$spy->calledWith('*');           // this is supported
$spy->calledWith('a', 'b', '*'); // this is supported
$spy->calledWith('a', '*', 'c'); // this is not supported
```

#### Third-party wildcard matcher integrations

*Phony* also supports the use of "wildcard" style matchers from third-party
matcher systems:

##### Phake wildcard matcher integration

[Phake wildcard matchers] \(`Phake::anyParameters()`) can be used in any *Phony*
verification:

```php
$spy('a', 'b');

$spy->calledWith(Phake::anyParameters()); // verification passes
```

##### Prophecy wildcard matcher integration

[Prophecy wildcard matchers] \(`Argument::cetera()`) can be used in any *Phony*
verification:

```php
$spy('a', 'b');

$spy->calledWith(Prophecy\Argument::cetera()); // verification passes
```

## The exporter

When a *Phony* verification fails, the failure message will often contain string
representations of the actual, or expected PHP values involved. These string
representations are generated by *Phony*'s internal exporter.

### The exporter API

<a name="facade.setExportDepth" />

----

> *integer* [**setExportDepth**](#facade.setExportDepth)($depth) *(with [use function])*<br />
> *integer* x\\[**setExportDepth**](#facade.setExportDepth)($depth) *(without [use function])*<br />
> *integer* Phony::[**setExportDepth**](#facade.setExportDepth)($depth) *(static)*

Set the default export depth, and return the previous depth.

*Negative depths are treated as infinite depth.*

### The export format

The exporter generates a concise, unambiguous, human-readable representation of
any PHP value, including recursive objects and arrays:

Input value                     | Exporter output
--------------------------------|-----------------
`null`                          | `'null'`
`true`                          | `'true'`
`false`                         | `'false'`
`111`                           | `'111'`
`1.11`                          | `'1.110000e+0'`
`'1.11'`                        | `'"1.11"'`
`"a\nb"`                        | `'"a\nb"'`
`STDIN`                         | `'resource#1'`
`[1, 2]`                        | `'#0[1, 2]'`
`['a' => 1, 'b' => 2]`          | `'#0[a: 1, b: 2]'`
`(object) ['a' => 1, 'b' => 2]` | `'#0{a: 1, b: 2}'`
`new ClassA()`                  | `'ClassA#0{}'`

#### Export identifiers and references

Exported arrays and objects include a numeric identifier that can be used to
identify re-use of the same value in nested structures. When a value appears
multiple times, its internal structure will only be described the first time.
Subsequent appearances will be indicated by a reference to the value's
identifier:

```php
$inner = [1, 2];
$value = [$inner, $inner];
// $value is exported as '#0[#1[1, 2], #1[]]'

$inner = (object) ['a' => 1];
$value = (object) ['b' => $inner, 'c' => $inner];
// $value is exported as '#0{b: #1{a: 1}, c: #1{}}'
```

Array references appear followed by brackets (e.g. `#0[]`), and object
references appear followed by braces (e.g. `#0{}`). This is because the
identifier sequences used for arrays and objects are independent, meaning that
identical array identifiers and object identifiers can co-exist in the same
exporter output:

```php
$value = [
    (object) [],
    [
        (object) [],
    ],
];
// $value is exported as '#0[#0{}, #1[#1{}]]'
```

Object references also exclude the class name for brevity:

```php
$inner = new ClassA();
$value = (object) ['a' => $inner, 'b' => $inner];
// $value is exported as '#0{a: ClassA#1{}, b: #1{}}'
```

Identifiers for objects are persistent across invocations of the exporter:

```php
$a = (object) [];
$b = (object) [];

$value = [$a, $b, $a];
// $value is exported as '#0[#0{}, #1{}, #0{}]'

$value = [$b, $a, $b];
// $value is exported as '#0[#1{}, #0{}, #1{}]'
```

But due to PHP's limitations, array identifiers are only persistent within a
single exporter invocation:

```php
$a = [];
$b = [];

$valueA = [$a, $b, $a];
$valueB = [$b, $a, $b];
// both $valueA and $valueB are exported as '#0[#1[], #2[], #1[]]'
```

#### Exporting recursive values

If a recursive value is exported, the points of recursion are exported as
[references], in the same way that multiple instances of the same value are
handled:

```php
$value = [];
$value[] = &$value;
// $value is exported as '#0[#0[]]'

$value = (object) [];
$value->a = $value;
// $value is exported as '#0{"a": #0{}}'
```

#### Exporter special cases

For certain types of values, the exporter will exhibit special behavior, in
order to improve the usefulness of its output, or to improve performance in
common use cases.

##### Exporting exceptions

When an exception is exported, some internal PHP details are stripped from the
output, including file path, line number, and stack trace:

```php
$value = new Exception('a', 1, new Exception());
// $value is exported as 'Exception#0{message: "a", code: 1, previous: Exception#1{}}'
```

Additionally, when the message is `''`, the code is `0`, and/or the previous
exception is `null`, these values are excluded for brevity:

```php
$value = new RuntimeException();
// $value is exported as 'RuntimeException#0{}'
```

##### Exporting mocks

When a mock is exported, some internal *Phony* details are stripped from the
output. In addition, if a [label][labeling mocks] has been set on the mock, this
will be included as a special property `phony.label`:

```php
$handle = mock('ClassA');
$handle->setLabel('a');

$value = $handle->mock();
// $value is exported as 'Phony_ClassA_0#0{phony.label: "a"}'
```

### Export depth

For complicated nested structures, exporting the entire value right down to its
innermost values is not always desirable. *Phony* sets a limit on how deep into
a nested structure the exporter will traverse.

When a value is beyond the export depth, and has sub-values, its contents will
be replaced with a special notation that simply indicates how many sub-values
exist within that value:

```php
$value = [[], ['a', 'b', 'c']];
// $value is exported as '#0[#1[], #2[:3]]'

$value = [(object) [], (object) ['a', 'b', 'c']];
// $value is exported as '#0[#1{}, #2{:3}]'
```

#### Setting the export depth

To set the export depth, use [`setExportDepth()`](#facade.setExportDepth):

```php
setExportDepth($depth);        // with `use function`
x\setExportDepth($depth);      // without `use function`
Phony::setExportDepth($depth); // static
```

Where `$depth` is an integer indicating the desired export depth.

Negative values are treated as infinite depth, and will cause *Phony* to export
values in their entirety. Note that this can produce immense amounts of output
for large nested structures.

## Thrown exceptions

### AssertionException

Thrown when a verification fails. The exact exception class and implementation
depends on the [testing framework] in use. See [Standard verification].

Other than the standard PHP [Exception] methods, assertion exceptions have no
public API methods.

### UndefinedArgumentException

Thrown when an argument that was requested does not exist.

Namespace: `Eloquent\Phony\Call\Argument\Exception`

<a name="undefinedargumentexception.index" />

----

> *integer* $exception->[**index**](#undefinedargumentexception.index)()

Get the index.

### UndefinedCallException

Thrown when a call that was requested does not exist.

Namespace: `Eloquent\Phony\Call\Exception`

<a name="undefinedcallexception.index" />

----

> *integer* $exception->[**index**](#undefinedcallexception.index)()

Get the index.

### UndefinedEventException

Thrown when an event that was requested does not exist.

Namespace: `Eloquent\Phony\Event\Exception`

<a name="undefinedeventexception.index" />

----

> *integer* $exception->[**index**](#undefinedeventexception.index)()

Get the index.

### UndefinedResponseException

Thrown when the call has not yet produced a response of the requested type.

This can occur when an individual call is queried for its response details
before the call has returned a value, or thrown an exception.

Other than the standard PHP [Exception] methods, undefined response exceptions
have no public API methods.

## License

For the full copyright and license information, please view the [LICENSE file].

<!-- Heading references -->

[ad hoc definition magic "self" values]: #ad-hoc-definition-magic-self-values
[ad hoc definition values]: #ad-hoc-definition-values
[ad hoc mocks]: #ad-hoc-mocks
[anonymous spies]: #anonymous-spies
[anonymous stubs]: #anonymous-stubs
[answers that perform multiple actions]: #answers-that-perform-multiple-actions
[assertionexception]: #assertionexception
[call count]: #call-count
[call verification]: #call-verification
[calling a constructor manually]: #calling-a-constructor-manually
[calls]: #calls
[check verification]: #check-verification
[comparing exceptions]: #comparing-exceptions
[comparing mocks]: #comparing-mocks
[copying mock builders]: #copying-mock-builders
[counterpart matchers]: #counterpart-matchers
[creating mocks from a builder]: #creating-mocks-from-a-builder
[customizing the mock class]: #customizing-the-mock-class
[default values for return types]: #default-values-for-return-types
[dynamic order verification]: #dynamic-order-verification
[example test suites]: #example-test-suites
[export depth]: #export-depth
[export identifiers and references]: #export-identifiers-and-references
[exporter special cases]: #exporter-special-cases
[exporting exceptions]: #exporting-exceptions
[exporting mocks]: #exporting-mocks
[exporting recursive values]: #exporting-recursive-values
[forwarding to the original callable]: #forwarding-to-the-original-callable
[generating mock classes from a builder]: #generating-mock-classes-from-a-builder
[generator iterations that perform multiple actions]: #generator-iterations-that-perform-multiple-actions
[hamcrest matchers]: #hamcrest-matchers
[help]: #help
[importing a static facade]: #importing-a-static-facade
[importing with use function]: #importing-with-use-function
[importing without use function]: #importing-without-use-function
[importing]: #importing
[individual calls]: #individual-calls
[installation]: #installation
[integration with test frameworks]: #integration-with-test-frameworks
[intermediate events in order verification]: #intermediate-events-in-order-verification
[invoking arguments in a generator]: #invoking-arguments-in-a-generator
[invoking arguments]: #invoking-arguments
[invoking callables in a generator]: #invoking-callables-in-a-generator
[invoking callables]: #invoking-callables
[invoking spies]: #invoking-spies
[labeling mocks]: #labeling-mocks
[labeling spies]: #labeling-spies
[license]: #license
[magic "self" values]: #magic-self-values
[matcher integrations]: #matcher-integrations
[matchers]: #matchers
[matching stub arguments]: #matching-stub-arguments
[mock builders]: #mock-builders
[mock handle substitution]: #mock-handle-substitution
[mock handles]: #mock-handles
[mockery matchers]: #mockery-matchers
[mocking basics]: #mocking-basics
[mocking multiple types]: #mocking-multiple-types
[mocks]: #mocks
[multiple answers]: #multiple-answers
[multiple rules]: #multiple-rules
[order verification caveats]: #order-verification-caveats
[order verification]: #order-verification
[overriding rules]: #overriding-rules
[partial mocks]: #partial-mocks
[pausing mock recording]: #pausing-mock-recording
[pausing spy recording]: #pausing-spy-recording
[peridot usage]: #peridot-usage
[phake matchers]: #phake-matchers
[phake wildcard matcher integration]: #phake-wildcard-matcher-integration
[pho usage]: #pho-usage
[phpunit constraints]: #phpunit-constraints
[phpunit usage]: #phpunit-usage
[prophecy argument tokens]: #prophecy-argument-tokens
[prophecy wildcard matcher integration]: #prophecy-wildcard-matcher-integration
[proxy mocks]: #proxy-mocks
[retrieving calls from a spy]: #retrieving-calls-from-a-spy
[returning arguments from a generator]: #returning-arguments-from-a-generator
[returning arguments]: #returning-arguments
[returning the "self" value from a generator]: #returning-the-self-value-from-a-generator
[returning the "self" value]: #returning-the-self-value
[returning values from a generator]: #returning-values-from-a-generator
[returning values]: #returning-values
[setting passed-by-reference arguments in a generator]: #setting-passed-by-reference-arguments-in-a-generator
[setting passed-by-reference arguments]: #setting-passed-by-reference-arguments
[setting the export depth]: #setting-the-export-depth
[shorthand matchers]: #shorthand-matchers
[similar events in order verification]: #similar-events-in-order-verification
[simpletest expectations]: #simpletest-expectations
[simpletest usage]: #simpletest-usage
[special cases for the "equal to" matcher]: #special-cases-for-the-equal-to-matcher
[spies]: #spies
[spying on an existing callable]: #spying-on-an-existing-callable
[standalone usage]: #standalone-usage
[standard verification]: #standard-verification
[static mocks]: #static-mocks
[stub "self" values]: #stub-self-values
[stub rules and answers]: #stub-rules-and-answers
[stubbing an existing callable]: #stubbing-an-existing-callable
[stubbing generators]: #stubbing-generators
[stubbing handles]: #stubbing-handles
[stubs]: #stubs
[terminology]: #terminology
[the "any" matcher]: #the-any-matcher
[the "equal to" matcher]: #the-equal-to-matcher
[the "wildcard" matcher]: #the-wildcard-matcher
[the arguments api]: #the-arguments-api
[the call api]: #the-call-api
[the default answer callback]: #the-default-answer-callback
[the default rule and answer]: #the-default-rule-and-answer
[the event api]: #the-event-api
[the export format]: #the-export-format
[the exporter api]: #the-exporter-api
[the exporter]: #the-exporter
[the generator answer api]: #the-generator-answer-api
[the matcher api]: #the-matcher-api
[the mock api]: #the-mock-api
[the mock builder api]: #the-mock-builder-api
[the order verification api]: #the-order-verification-api
[the spy api]: #the-spy-api
[the stub api]: #the-stub-api
[the verification result api]: #the-verification-result-api
[the wildcard matcher api]: #the-wildcard-matcher-api
[third-party wildcard matcher integrations]: #third-party-wildcard-matcher-integrations
[throwing exceptions from a generator]: #throwing-exceptions-from-a-generator
[throwing exceptions]: #throwing-exceptions
[thrown exceptions]: #thrown-exceptions
[undefinedargumentexception]: #undefinedargumentexception
[undefinedcallexception]: #undefinedcallexception
[undefinedeventexception]: #undefinedeventexception
[undefinedresponseexception]: #undefinedresponseexception
[usage]: #usage
[using a callable as an answer]: #using-a-callable-as-an-answer
[verification handles]: #verification-handles
[verification]: #verification
[verifying call closure binding]: #verifying-call-closure-binding
[verifying call exceptions]: #verifying-call-exceptions
[verifying call input]: #verifying-call-input
[verifying call output]: #verifying-call-output
[verifying call return values]: #verifying-call-return-values
[verifying calls with generators or traversables]: #verifying-calls-with-generators-or-traversables
[verifying cardinality with calls]: #verifying-cardinality-with-calls
[verifying cardinality with spies]: #verifying-cardinality-with-spies
[verifying exceptions received by calls]: #verifying-exceptions-received-by-calls
[verifying exceptions received by spies]: #verifying-exceptions-received-by-spies
[verifying spies with generators or traversables]: #verifying-spies-with-generators-or-traversables
[verifying spy closure binding]: #verifying-spy-closure-binding
[verifying spy exceptions]: #verifying-spy-exceptions
[verifying spy input]: #verifying-spy-input
[verifying spy output]: #verifying-spy-output
[verifying spy return values]: #verifying-spy-return-values
[verifying that a call event happened a bounded number of times]: #verifying-that-a-call-event-happened-a-bounded-number-of-times
[verifying that a call event happened an exact number of times]: #verifying-that-a-call-event-happened-an-exact-number-of-times
[verifying that a call was made with specific arguments]: #verifying-that-a-call-was-made-with-specific-arguments
[verifying that a call was made]: #verifying-that-a-call-was-made
[verifying that a spy event happened a bounded number of times]: #verifying-that-a-spy-event-happened-a-bounded-number-of-times
[verifying that a spy event happened an exact number of times]: #verifying-that-a-spy-event-happened-an-exact-number-of-times
[verifying that a spy was called with specific arguments]: #verifying-that-a-spy-was-called-with-specific-arguments
[verifying that all call events happen the same way]: #verifying-that-all-call-events-happen-the-same-way
[verifying that all spy events happen the same way]: #verifying-that-all-spy-events-happen-the-same-way
[verifying that there was no interaction with a mock]: #verifying-that-there-was-no-interaction-with-a-mock
[verifying values produced by calls]: #verifying-values-produced-by-calls
[verifying values produced by spies]: #verifying-values-produced-by-spies
[verifying values received by calls]: #verifying-values-received-by-calls
[verifying values received by spies]: #verifying-values-received-by-spies
[when to use the "equal to" matcher]: #when-to-use-the-equal-to-matcher
[yielding from a generator]: #yielding-from-a-generator

<!-- Shortcut references -->

["any" matcher]: #the-any-matcher
["equal to" matcher]: #the-equal-to-matcher
["wildcard" matcher]: #the-wildcard-matcher
[ad hoc mock]: #ad-hoc-mocks
[default answer callback]: #the-default-answer-callback
[full mock]: #mocking-basics
[generator answer]: #the-generator-answer-api
[generator spies]: #verifying-spies-with-generators-or-traversables
[matcher]: #matchers
[mock builder]: #mock-builders
[mock handle]: #mock-handles
[mock]: #mocks
[partial mock]: #partial-mocks
[references]: #export-identifiers-and-references
[self value]: #stub-self-values
[spy]: #spies
[static mock handles]: #static-mocks
[stub]: #stubs
[stubbing handle]: #stubbing-handles
[testing framework]: #integration-with-test-frameworks
[testing frameworks]: #integration-with-test-frameworks
[traversable spies]: #verifying-spies-with-generators-or-traversables
[verification handle]: #verification-handles
[verification result]: #the-verification-result-api
[verification results]: #the-verification-result-api

<!-- API references -->

[arguments-api]: #the-arguments-api
[call-api]: #the-call-api
[event-api]: #the-event-api
[generator-answer-api]: #the-generator-answer-api
[matcher-api]: #the-matcher-api
[mock-api]: #the-mock-api
[mock-builder-api]: #the-mock-builder-api
[spy-api]: #the-spy-api
[stub-api]: #the-stub-api
[verification-api]: #the-verification-result-api
[wildcard-api]: #the-wildcard-matcher-api

<!-- External references -->

[@ezzatron]: https://github.com/ezzatron
[`__invoke()`]: http://php.net/language.oop5.magic#object.invoke
[closure binding]: http://php.net/closure.bind
[composer]: http://getcomposer.org/
[counterpart]: http://docs.counterpartphp.org/
[eloquent/phony]: https://packagist.org/packages/eloquent/phony
[error]: http://php.net/class.error
[example]: https://github.com/eloquent/phony/tree/HEAD/doc/example
[exception]: http://php.net/class.exception
[fluent interfaces]: http://en.wikipedia.org/wiki/Fluent_interface
[generator::send()]: http://php.net/generator.send
[generator::throw()]: http://php.net/generator.throw
[generator]: http://php.net/language.generators.overview
[generators]: http://php.net/language.generators.overview
[github issue]: https://github.com/eloquent/phony/issues
[hamcrest]: https://github.com/hamcrest/hamcrest-php
[license file]: https://github.com/eloquent/phony/blob/HEAD/LICENSE
[mockery matchers]: http://docs.mockery.io/en/latest/reference/argument_validation.html
[mockery]: http://docs.mockery.io/
[peridot]: http://peridot-php.github.io/
[phake matchers]: http://phake.readthedocs.org/en/latest/method-parameter-matchers.html
[phake wildcard matchers]: http://phake.readthedocs.org/en/latest/method-stubbing.html?highlight=anyparameters#stubbing-consecutive-calls
[phake]: http://phake.readthedocs.org/
[pho]: https://github.com/danielstjules/pho
[phpunit matchers]: https://phpunit.de/manual/current/en/appendixes.assertions.html#appendixes.assertions.assertThat
[phpunit]: https://phpunit.de/
[prophecy matchers]: https://github.com/phpspec/prophecy#arguments-wildcarding
[prophecy wildcard matchers]: https://github.com/phpspec/prophecy#arguments-wildcarding
[prophecy]: https://github.com/phpspec/prophecy
[reflectionclass]: http://php.net/reflectionclass
[return type]: http://php.net/functions.returning-values#functions.returning-values.type-declaration
[simpletest matchers]: http://www.simpletest.org/en/expectation_documentation.html
[simpletest]: https://github.com/simpletest/simpletest
[throwable]: http://php.net/class.throwable
[twitter]: https://twitter.com/ezzatron
[use function]: http://php.net/language.namespaces.importing
[yield]: http://php.net/language.generators.syntax#control-structures.yield
