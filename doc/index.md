# Phony

- [Installation](#installation)
- [Usage](#usage)
    - [Standalone usage](#standalone-usage)
    - [Peridot usage](#peridot-usage)
    - [Pho usage](#pho-usage)
    - [PHPUnit usage](#phpunit-usage)
    - [SimpleTest usage](#simpletest-usage)
    - [Integration with test frameworks](#integration-with-test-frameworks)
    - [Importing](#importing)
- [Mocks](#mocks)
    - [Mocking basics](#mocking-basics)
    - [Partial mocks](#partial-mocks)
    - [Mocking multiple types](#mocking-multiple-types)
    - [Ad hoc mocks](#ad-hoc-mocks)
    - [Static mocks](#static-mocks)
    - [Custom class names](#custom-class-names)
    - [Calling a constructor manually](#calling-a-constructor-manually)
    - [Terminology](#terminology)
- [Stubs](#stubs)
    - [Stubbing an existing callable](#stubbing-an-existing-callable)
    - [Anonymous stubs](#anonymous-stubs)
    - [Stub rules and answers](#stub-rules-and-answers)
        - [Multiple rules](#multiple-rules)
        - [Multiple answers](#multiple-answers)
        - [Overriding rules](#overriding-rules)
        - [The default rule and answer](#the-default-rule-and-answer)
    - [Matching stub arguments](#matching-stub-arguments)
    - [Returning values](#returning-values)
    - [Returning arguments](#returning-arguments)
    - [Returning the 'self' value](#returning-the-self-value)
    - [Throwing exceptions](#throwing-exceptions)
    - [Using a callable as an answer](#using-a-callable-as-an-answer)
    - [Forwarding to the original callable](#forwarding-to-the-original-callable)
    - [Answers that perform multiple actions](#answers-that-perform-multiple-actions)
        - [Setting passed-by-reference arguments](#setting-passed-by-reference-arguments)
        - [Invoking arguments](#invoking-arguments)
        - [Invoking callables](#invoking-callables)
- [Spies](#spies)
    - [Spying on an existing callable](#spying-on-an-existing-callable)
    - [Anonymous spies](#anonymous-spies)
    - [Call verification](#call-verification)
        - [Call count](#call-count)
        - [Individual calls](#individual-calls)
    - [Verifying input](#verifying-input)
        - [Verifying that a call was made](#verifying-that-a-call-was-made)
        - [Verifying that a call was made with specific arguments](#verifying-that-a-call-was-made-with-specific-arguments)
        - [Verifying closure binding](#verifying-closure-binding)
    - [Verifying output](#verifying-output)
        - [Verifying return values](#verifying-return-values)
        - [Verifying exceptions](#verifying-exceptions)
    - [Verifying generators and traversables](#verifying-generators-and-traversables)
        - [Verifying produced values](#verifying-produced-values)
        - [Verifying received values](#verifying-received-values)
        - [Verifying received exceptions](#verifying-received-exceptions)
- [Calls](#calls)
    - [Retrieving calls from a spy](#retrieving-calls-from-a-spy)
    - [Verifying input](#verifying-input-1)
        - [Verifying that a call was made](#verifying-that-a-call-was-made-1)
        - [Verifying that a call was made with specific arguments](#verifying-that-a-call-was-made-with-specific-arguments-1)
        - [Verifying closure binding](#verifying-closure-binding-1)
    - [Verifying output](#verifying-output-1)
        - [Verifying return values](#verifying-return-values-1)
        - [Verifying exceptions](#verifying-exceptions-1)
    - [Verifying generators and traversables](#verifying-generators-and-traversables-1)
        - [Verifying produced values](#verifying-produced-values-1)
        - [Verifying received values](#verifying-received-values-1)
        - [Verifying received exceptions](#verifying-received-exceptions-1)
- [Matchers](#matchers)
    - [Matcher integrations](#matcher-integrations)
        - [Counterpart matchers](#counterpart-matchers)
        - [Hamcrest matchers](#hamcrest-matchers)
        - [Mockery matchers](#mockery-matchers)
        - [Phake matchers](#phake-matchers)
        - [PHPUnit constraints](#phpunit-constraints)
        - [Prophecy argument tokens](#prophecy-argument-tokens)
        - [SimpleTest expectations](#simpletest-expectations)
    - [Shorthand matchers](#shorthand-matchers)
    - [The 'any' matcher](#the-any-matcher)
    - [The 'equal to' matcher](#the-equal-to-matcher)
        - [When to use the 'equal to' matcher](#when-to-use-the-equal-to-matcher)
    - [The 'wildcard' matcher](#the-wildcard-matcher)
        - [Third-party wildcard matcher integrations](#third-party-wildcard-matcher-integrations)
            - [Phake wildcard matchers](#phake-wildcard-matchers)
            - [Prophecy wildcard matchers](#prophecy-wildcard-matchers)

## Installation

- Available as [Composer] package [eloquent/phony].

[composer]: http://getcomposer.org/
[eloquent/phony]: https://packagist.org/packages/eloquent/phony

## Usage

### Standalone usage

```php
use function Eloquent\Phony\mock;

$handle = mock('ClassA');
$handle->methodA('argument')->returns('value');

$mock = $handle->mock();

assert($mock->methodA('argument') === 'value');
$handle->methodA->calledWith('argument');
```

### Peridot usage

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

### Pho usage

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

### PHPUnit usage

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

### SimpleTest usage

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

[peridot]: http://peridot-php.github.io/
[pho]: https://github.com/danielstjules/pho

### Importing

There are three ways to import *Phony*'s API. The most appropriate choice will
depend on the test framework in use, and the user's preferred coding style.

If the version of PHP in use supports [use function], the top-level functions
can be imported from the appropriate namespace and used directly:

```php
use function Eloquent\Phony\mock;

$handle = mock('ClassA');
```

When [use function] is unavailable, the namespace itself can be imported, and an
alias can be used to make short references to the same functions:

```php
use Eloquent\Phony as x;

$handle = x\mock('ClassA');
```

A static facade implementation is also provided for those who prefer a more
traditional approach:

```php
use Eloquent\Phony\Phony;

$handle = Phony::mock('ClassA');
```

[use function]: http://php.net/language.namespaces.importing

## Mocks

*Mocks* are objects that can be used as a substitute for another object. This
can be useful when a "real" object becomes difficult to use in a test.

### Mocking basics

Any class, interface, or trait can be mocked. To create a mock, use `mock()`:

```php
$handle = mock('ClassA');        // with `use function`
$handle = x\mock('ClassA');      // without `use function`
$handle = Phony::mock('ClassA'); // static
```

The object returned by `mock()` is **not** the mock object itself, but a handle
object. This handle provides a [stub][stubs] for each method of the type being
mocked. Each stub is exposed as a property of the same name as the stubbed
method, and implements both [the stub API][stubs], and [the spy API][spies]:

```php
// stubbing
$handle->methodA->returns('1337 h4x');
$handle->methodB('bad', 'input')->throws('You done goofed.');

// verification
$handle->methodA->calledWith('swiggity', 'swooty');
$handle->methodB->called();
```

To access the actual mock object, call the `mock()` method of the handle:

```php
$mock = $handle->mock();
```

To get a handle from a mock, use `on()`:

```php
$handle = on($mock);        // with `use function`
$handle = x\on($mock);      // without `use function`
$handle = Phony::on($mock); // static
```

### Partial mocks

*Phony* supports "partial mocks", or mocks that do not override methods by
default. To create a partial mock, use `partialMock()`:

```php
$handle = partialMock('ClassA');        // with `use function`
$handle = x\partialMock('ClassA');      // without `use function`
$handle = Phony::partialMock('ClassA'); // static
```

Constructor arguments can be passed to `partialMock()` as the second parameter:

```php
$handle = partialMock('ClassA', ['argumentA', 'argumentB']);
```

### Mocking multiple types

Multiple interfaces and/or traits can be mocked simultaneously by passing an
array of types as the first argument to `mock()` or `partialMock()`:

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
using a special "definition" value, which can be passed as the second argument
to `mock()` (or the third argument to `partialMock()`):

```php
$handle = mock(
    null,
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

Definition values support methods, properties, and constants. By default,
callback functions will be converted to methods, and everything else will become
a property:

```php
$handle = mock(
    null,
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
$handle = mock(
    null,
    [
        'const A' => 'A is for apple.',
        'static b' => function () {
            return 'B is for banana.';
        },
        'static c' => 'C is for cat.',
        'var d' => function () {
            'D is for dog.';
        },
        'static var e' => function () {
            'E is for egg.';
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

### Static mocks

*Phony* can be used to stub the behavior of static methods of generated mock
classes. To modify the behavior of a static method, use `onStatic()` to obtain a
static stubbing handle from either an existing handle, or a mock instance:

```php
$handle = mock('DateTime');

$static = onStatic($handle);        // with `use function`
$static = x\onStatic($handle);      // without `use function`
$static = Phony::onStatic($handle); // static
```

This static handle is just like a normal stubbing handle, except that it refers
to static methods instead of instance methods:

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

### Custom class names

To use a specific class name for a generated mock class, pass the class name as
the third argument to `mock()` (or the fourth argument to `partialMock()`):

```php
$handle = mock('ClassA', null, 'CustomClassName');
$mock = $handle->mock();

echo get_class($handle); // outputs 'CustomClassName'
```

### Calling a constructor manually

In order to stub methods called in the constructor of a partial mock, it is
necessary to defer construction of the mock object. To accomplish this using
*Phony*, a normal mock is created, then converted to a partial mock using
`partial()`. This diverts the calling of the mock constructor:

```php
$handle = mock('ClassA')->partial();
```

Behavior can then be defined before the constructor is called:

```php
$handle->methodA->returns(true);
```

Finally, the constructor can be manually called using `construct()`, or
`constructWith()`:

```php
$handle->construct('argumentA', 'argumentB');       // variable arguments
$handle->constructWith(['argumentA', 'argumentB']); // array arguments
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
set of rules when called. In *Phony*, every stub also implements
[the spy API][spies].

### Stubbing an existing callable

Any callable can be stubbed:

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
stub is created as follows:

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

Each time `with()` is called (not to be confused with `calledWith()`, which is
part of [the spy API][spies]), a new rule is started:

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
`with('*')` were called. For example, the two following stubs behave the same:

```php
$stubA = stub()
    ->with('*')
    ->returns('x');

$stubB = stub()
    // implicit ->with('*')
    ->returns('x');
```

If a new rule is started before any answers are defined, the stub behaves as if
`returns()` were called, causing the stub to return `null` by default. For
example, the two following stubs behave the same:

```php
$stubA = stub()
    ->with('*')->returns()
    ->with('a')->returns('x');

$stubB = stub()
    // implicit ->with('*')->returns()
    ->with('a')->returns('x');
```

### Matching stub arguments

Stub arguments can be matched using `with()` (not to be confused with
`calledWith()`, which is part of [the spy API][spies]). Arguments passed to
`with()` can be literal values, or [matchers], including [shorthand matchers]:

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

To return a value from a stub, use `returns()`:

```php
$stubA = stub()->returns('x');
$stubB = stub()->returns();

echo $stubA();          // outputs 'x'
echo gettype($stubB()); // outputs 'NULL'
```

Calling `returns()` with multiple arguments is equivalent to calling it once
with each argument. For example, the two following stubs behave the same:

```php
$stubA = stub()->returns('x', 'y');

echo $stubA(); // outputs 'x'
echo $stubA(); // outputs 'y'

$stubB = stub()->returns('x')->returns('y');

echo $stubB(); // outputs 'x'
echo $stubB(); // outputs 'y'
```

### Returning arguments

To return an argument from a stub, use `returnsArgument()`:

```php
$stubA = stub()->returnsArgument();   // returns the first argument
$stubB = stub()->returnsArgument(1);  // returns the second argument
$stubC = stub()->returnsArgument(-1); // returns the last argument

echo $stubA('x', 'y', 'z'); // outputs 'x'
echo $stubB('x', 'y', 'z'); // outputs 'y'
echo $stubC('x', 'y', 'z'); // outputs 'z'
```

### Returning the 'self' value

When stubs are retrieved from a mock, their 'self' value is automatically set to
the mock itself. This allows mocking of [fluent interfaces] with the
`returnsSelf()` method:

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

The self value can also be set manually by calling `setSelf()` on any stub:

```php
$stub = stub()->returnsSelf();
$stub->setSelf('x');

echo $stub(); // outputs 'x'
```

[fluent interfaces]: http://en.wikipedia.org/wiki/Fluent_interface

### Throwing exceptions

To throw an exception from a stub, use `throws()`:

```php
$exception = new RuntimeException('You done goofed.');

$stubA = stub()->throws($exception);
$stubB = stub()->throws();

$stubA(); // throws $exception
$stubB(); // throws a generic exception
```

Calling `throws()` with multiple arguments is equivalent to calling it once
with each argument. For example, the two following stubs behave the same:

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

To use a callable as an answer, use `does()`:

```php
$stub = stub()->does('max');

echo $stub(2, 3, 1); // outputs '3'
```

Calling `does()` with multiple arguments is equivalent to calling it once with
each argument. For example, the two following stubs behave the same:

```php
$stubA = stub()->does('min', 'max');

echo $stubA(2, 3, 1); // outputs '1'
echo $stubA(2, 3, 1); // outputs '3'

$stubB = stub()->does('min')->does('max');

echo $stubB(2, 3, 1); // outputs '1'
echo $stubB(2, 3, 1); // outputs '3'
```

There is also a more powerful version of `does()`, named `doesWith()`, that
allows more control over which arguments are passed to the callable, and how
they are passed:

```php
$stub = stub()->doesWith(
    'implode', // callable
    [', '],    // fixed arguments
    false,     // prefix the 'self' value?
    true,      // suffix the arguments as an array?
    false      // suffix the arguments normally?
);

echo $stub('x', 'y', 'z'); // outputs 'x, y, z'
```

For more details, see `Eloquent\Phony\Stub\StubInterface`.

### Forwarding to the original callable

When stubbing an existing callable, the stub can 'forward' calls on to the
original callable using `forwards()`:

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

### Answers that perform multiple actions

Stubs can perform mulitple actions as part of a single answer. This allows
callables that have side effects other than return values or exceptions to be
emulated.

The most familiar of these side effects is probably the modification of
passed-by-reference arguments, and the invocation of other callables, such as in
an event emitter implementation.

#### Setting passed-by-reference arguments

To set a reference argument as part of an answer, use `setsArgument()`:

```php
$stub = stub(function (&$a, &$b, &$c) {})
    ->setsArgument(0, 'x')  // sets the first argument to 'x'
    ->setsArgument(1, 'y')  // sets the second argument to 'y'
    ->setsArgument(-1, 'z') // sets the last argument to 'z'
    ->returns();

$stub($a, $b, $c);

echo $a; // outputs 'x'
echo $b; // outputs 'y'
echo $c; // outputs 'z'
```

If only one argument is passed to `setsArgument()`, it sets the first argument:

```php
$stub = stub(function (&$a) {})
    ->setsArgument('x')  // sets the first argument to 'x'
    ->returns();

$a = 'a';
$stub($a);

echo $a; // outputs 'x'
```

If `setsArgument()` is called without any arguments, it sets the first argument
to `null`:

```php
$stub = stub(function (&$a) {})
    ->setsArgument()  // sets the first argument to null
    ->returns();

$stub($a);

echo gettype($a); // outputs 'NULL'
```

#### Invoking arguments

To invoke an argument as part of an answer, use `callsArgument()`:

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

There is also a more powerful version of `callsArgument()`, named
`callsArgumentWith()`, that allows more control over which arguments are passed
to the callable, and how they are passed:

```php
$stub = stub()
    ->callsArgumentWith(
        1,              // argument to invoke
        ['%s, %s, %s'], // fixed arguments
        false,          // prefix the 'self' value?
        true,           // suffix the arguments as an array?
        false           // suffix the arguments normally?
    )
    ->returns();

$stub('x', 'printf', 'y'); // outputs 'x, printf, y'
```

For more details, see `Eloquent\Phony\Stub\StubInterface`.

#### Invoking callables

To invoke a callable as part of an answer, use `calls()`:

```php
$stub = stub()->calls('printf')->returns();

$stub('%s, %s', 'a', 'b'); // outputs 'a, b'
```

Calling `calls()` with multiple arguments is equivalent to calling it once with
each argument. For example, the two following stubs behave the same:

```php
$x = function () { echo 'x'; };
$y = function () { echo 'y'; };

$stubA = stub()->calls($x, $y)->returns();

echo $stubA(); // outputs 'xy'

$stubB = stub()->calls($x)->calls($y)->returns();

echo $stubB(); // outputs 'xy'
```

There is also a more powerful version of `calls()`, named `callsWith()`, that
allows more control over which arguments are passed to the callable, and how
they are passed:

```php
$stub = stub()
    ->callsWith(
        'printf',   // argument to invoke
        ['%s, %s'], // fixed arguments
        false,      // prefix the 'self' value?
        false,      // suffix the arguments as an array?
        true        // suffix the arguments normally?
    )
    ->returns();

$stub('x', 'y'); // outputs 'x, y'
```

For more details, see `Eloquent\Phony\Stub\StubInterface`.

## Spies

*Spies* record interactions with callable entities, such as functions, methods,
closures, and objects with an [__invoke()] method. They can be used to verify
both the *input*, and *output* of function calls.

Most of the methods in the spy API are mirrored in [the call API][calls].

[__invoke()]: http://php.net/language.oop5.magic#object.invoke

### Spying on an existing callable

Any callable can be wrapped in a spy:

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
purpose is to record input arguments. An anonymous spy is created as follows:

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

See [the call API][calls] for more information.

#### Call count

The number of calls recorded by a spy can be retrieved using `callCount()`:

```php
$spy->callCount();
```

#### Individual calls

To get the first call, use `firstCall()`:

```php
$spy->firstCall();
```

To get the last call, use `lastCall()`:

```php
$spy->lastCall();
```

To get a specific call by index, use `callAt()`:

```php
$spy->callAt(0); // returns the first call
$spy->callAt(9); // returns the tenth call
```

These methods will throw an exception if no call is found.

### Verifying input

#### Verifying that a call was made

To verify that a spy was called, use `called()`:

```php
$spy->called();
```

#### Verifying that a call was made with specific arguments

To verify input arguments, use `calledWith()`. Arguments passed to
`calledWith()` can be literal values, or [matchers], including
[shorthand matchers]:

```php
$spy->calledWith();         // called with no arguments
$spy->calledWith('a', 'b'); // called with 'a' followed by 'b'
$spy->calledWith('a', '*'); // called with 'a' followed by 0-n arguments
$spy->calledWith('a', '~'); // called with 'a' followed by exactly 1 argument
```

Arguments can also be retrieved by calling `arguments()` or `argument()` on any
verification result:

```php
$spy->called()->arguments(); // all arguments as an array
$spy->called()->argument();  // first argument
$spy->called()->argument(1); // second argument
```

Note that this will return the arguments for the first call that matches the
verification in use.

#### Verifying closure binding

Where [closure binding] is supported, the bound object can be verified using
`calledOn()`:

```php
$spy->calledOn($object);
```

### Verifying output

#### Verifying return values

To verify a spy's return value, use `returned()`:

```php
$spy->returned();    // returned anything
$spy->returned('a'); // returned 'a'
```

#### Verifying exceptions

To verify that a spy threw an exception, use `threw()`:

```php
$spy->threw();                                         // threw any exception
$spy->threw('RuntimeException');                       // threw a runtime exception
$spy->threw(new RuntimeException('You done goofed.')); // threw a runtime exception with a specific message
```

### Verifying generators and traversables

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
enabled for other traversables by calling `setUseTraversableSpies()` on a spy:

```php
$spy->setUseTraversableSpies(true);
```

For other traversables, such as arrays and iterators, the values are recorded as
*produced values*.

#### Verifying produced values

To verify that a value was produced by a spy, use `produced()`:

```php
$spy->produced();         // produced anything
$spy->produced('a');      // produced 'a' with any key
$spy->produced('a', 'b'); // produced 'b' with key 'a'
```

To verify that a set of values were produced by a spy in a specific order, use
`producedAll()`:

```php
$spy->producedAll();                // produced nothing (an empty traversable)
$spy->producedAll('a', 'b');        // produced 'a', then 'b', with any keys
$spy->producedAll('a', ['b', 'c']); // produced 'a' with any key, then 'c' with key 'b'
```

#### Verifying received values

To verify that a value was received by a spy, use `received()`:

```php
$spy->received();    // received anything
$spy->received('a'); // received 'a'
```

#### Verifying received exceptions

To verify that an exception was received by a spy, use `receivedException()`:

```php
$spy->receivedException();                                         // received any exception
$spy->receivedException('RuntimeException');                       // received a runtime exception
$spy->receivedException(new RuntimeException('You done goofed.')); // received a runtime exception with a specific message
```

## Calls

*Phony* provides the ability to make verifications on individual recorded calls.
The call API mirrors the methods available on [the spy API][spies].

### Retrieving calls from a spy

To get the first call, use `firstCall()`:

```php
$spy->firstCall();
```

To get the last call, use `lastCall()`:

```php
$spy->lastCall();
```

To get a specific call by index, use `callAt()`:

```php
$spy->callAt(0); // returns the first call
$spy->callAt(9); // returns the tenth call
```

These methods will throw an exception if no call is found.

### Verifying input

#### Verifying that a call was made with specific arguments

To verify input arguments, use `calledWith()`. Arguments passed to
`calledWith()` can be literal values, or [matchers], including
[shorthand matchers]:

```php
$call->calledWith();         // called with no arguments
$call->calledWith('a', 'b'); // called with 'a' followed by 'b'
$call->calledWith('a', '*'); // called with 'a' followed by 0-n arguments
$call->calledWith('a', '~'); // called with 'a' followed by exactly 1 argument
```

Arguments can also be retrieved with `arguments()` or `argument()`:

```php
$call->arguments(); // all arguments as an array
$call->argument();  // first argument
$call->argument(1); // second argument
```

#### Verifying closure binding

Where [closure binding] is supported, the bound object can be verified using
`calledOn()`:

```php
$call->calledOn($object);
```

### Verifying output

#### Verifying return values

To verify a call's return value, use `returned()`:

```php
$call->returned();    // returned anything
$call->returned('a'); // returned 'a'
```

#### Verifying exceptions

To verify that a call threw an exception, use `threw()`:

```php
$call->threw();                                         // threw any exception
$call->threw('RuntimeException');                       // threw a runtime exception
$call->threw(new RuntimeException('You done goofed.')); // threw a runtime exception with a specific message
```

### Verifying generators and traversables

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
enabled for other traversables by calling `setUseTraversableSpies()` on a spy:

```php
$spy->setUseTraversableSpies(true);
```

For other traversables, such as arrays and iterators, the values are recorded as
*produced values*.

#### Verifying produced values

To verify that a value was produced by a call, use `produced()`:

```php
$call->produced();         // produced anything
$call->produced('a');      // produced 'a' with any key
$call->produced('a', 'b'); // produced 'b' with key 'a'
```

To verify that a set of values were produced by a call in a specific order, use
`producedAll()`:

```php
$call->producedAll();                // produced nothing (an empty traversable)
$call->producedAll('a', 'b');        // produced 'a', then 'b', with any keys
$call->producedAll('a', ['b', 'c']); // produced 'a' with any key, then 'c' with key 'b'
```

#### Verifying received values

To verify that a value was received by a call, use `received()`:

```php
$call->received();    // received anything
$call->received('a'); // received 'a'
```

#### Verifying received exceptions

To verify that an exception was received by a call, use `receivedException()`:

```php
$call->receivedException();                                         // received any exception
$call->receivedException('RuntimeException');                       // received a runtime exception
$call->receivedException(new RuntimeException('You done goofed.')); // received a runtime exception with a specific message
```

## Matchers

*Matchers* are used to determine whether the arguments of a call match certain
criteria. Utilized correctly, they can make verifications less brittle, and
improve the quality of a test suite.

*Phony* implements only a few matchers itself, and provides first-class support
for numerous third-party matcher libraries.

### Matcher integrations

#### Counterpart matchers

[Counterpart] is a stand-alone matcher library. Its matchers can be used in any
*Phony* validation:

```php
$spy->calledWith(Counterpart\Matchers::isEqual('a'));
```

[counterpart]: http://docs.counterpartphp.org/

#### Hamcrest matchers

[Hamcrest] is a popular stand-alone matcher library, that originated in Java,
and has been ported to many languages, including an "official" port for PHP. Its
matchers can be used in any *Phony* validation:

```php
$spy->calledWith(equalTo('a'));
```

[hamcrest]: https://github.com/hamcrest/hamcrest-php

#### Mockery matchers

[Mockery] is a mocking library, similar to *Phony*. [Mockery matchers] can be
used in any *Phony* validation:

```php
$spy->calledWith(Mockery::mustBe('a'));
```

[mockery]: http://docs.mockery.io/
[mockery matchers]: http://docs.mockery.io/en/latest/reference/argument_validation.html

#### Phake matchers

[Phake] is a mocking library, similar to *Phony*. [Phake matchers] can be used
in any *Phony* validation:

```php
$spy->calledWith(Phake::equalTo('a'));
```

[phake]: http://phake.readthedocs.org/
[phake matchers]: http://phake.readthedocs.org/en/latest/method-parameter-matchers.html

#### PHPUnit constraints

[PHPUnit] is a popular unit testing framework. [PHPUnit matchers] \(referred to
as "constraints") can be used in any *Phony* validation:

```php
// where $this is a PHPUnit test case
$spy->calledWith($this->equalTo('a'));
```
[phpunit matchers]: https://phpunit.de/manual/current/en/appendixes.assertions.html#appendixes.assertions.assertThat

#### Prophecy argument tokens

[Prophecy] is a mocking library, similar to *Phony*. [Prophecy matchers]
\(referred to as "argument tokens") can be used in any *Phony* validation:

```php
$spy->calledWith(Prophecy\Argument::exact('a'));
```

[prophecy]: https://github.com/phpspec/prophecy
[prophecy matchers]: https://github.com/phpspec/prophecy#arguments-wildcarding

#### SimpleTest expectations

[SimpleTest] is a legacy unit testing framework. [SimpleTest matchers]
\(referred to as "expectations") can be used in any *Phony* validation:

```php
$spy->calledWith(new EqualExpectation('a'));
```

[simpletest matchers]: http://www.simpletest.org/en/expectation_documentation.html

### Shorthand matchers

*Phony* provides two 'shorthand matchers'; `'~'`, and `'*'`. These strings are
automatically transformed into specific matchers:

- `'~'` is equivalent to an ['any' matcher]
- `'*'` is equivalent to a ['wildcard' matcher]

For typical *Phony* usage, this results in much simpler verifications. For
example, the following verifications are exactly equivalent:

```php
$spy->calledWith('a', Phony::any(), 'b', Phony::wildcard());
$spy->calledWith('a', '~', 'b', '*');
```

In the case that a verification expects a literal `'~'`, or `'*'` value, the
['equal to' matcher] can be used to prevent *Phony* interpreting shorthand
matchers:

```php
$spy->calledWith(equalTo('~'), equalTo('*'));
```

['any' matcher]: #the-any-matcher
['equal to' matcher]: #the-equal-to-matcher
['wildcard' matcher]: #the-wildcard-matcher

### The 'any' matcher

This matcher matches a single argument of any value:

```php
$matcher = any($value);        // with `use function`
$matcher = x\any($value);      // without `use function`
$matcher = Phony::any($value); // static

$spy->calledWith(any()); // typical usage
```

### The 'equal to' matcher

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

#### When to use the 'equal to' matcher

In most cases, it's not necessary to create an 'equal to' matcher manually,
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

### The 'wildcard' matcher

The 'wildcard' matcher is a special matcher that can match multiple arguments:

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

*Phony* also supports the use of 'wildcard' style matchers from third-party
matcher systems:

##### Phake wildcard matchers

[Phake wildcard matchers] \(`Phake::anyParameters()`) can be used in any *Phony*
validation:

```php
$spy('a', 'b');

$spy->calledWith(Phake::anyParameters()); // verification passes
```

[phake wildcard matchers]: http://phake.readthedocs.org/en/latest/method-stubbing.html?highlight=anyparameters#stubbing-consecutive-calls

##### Prophecy wildcard matchers

[Prophecy wildcard matchers] \(`Argument::cetera()`) can be used in any *Phony*
validation:

```php
$spy('a', 'b');

$spy->calledWith(Prophecy\Argument::cetera()); // verification passes
```

[prophecy wildcard matchers]: https://github.com/phpspec/prophecy#arguments-wildcarding

[calls]: #calls
[matchers]: #matchers
[shorthand matchers]: #shorthand-matchers
[spies]: #spies
[stubs]: #stubs

[closure binding]: http://php.net/closure.bind
[generator::send()]: http://php.net/generator.send
[generator::throw()]: http://php.net/generator.throw
[generators]: http://php.net/language.generators.overview
[phpunit]: https://phpunit.de/
[simpletest]: https://github.com/simpletest/simpletest
[yield]: http://php.net/language.generators.syntax#control-structures.yield
