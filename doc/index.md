# Phony

- [Installation]
- [Help]
- [Usage]
    - [Using this documentation]
    - [Example test suites]
    - [Standalone usage]
    - [Kahlan usage]
    - [Peridot usage]
    - [Pho usage]
    - [PHPUnit usage]
    - [SimpleTest usage]
    - [Integration with test frameworks]
    - [Importing]
        - [Importing with use function]
        - [Importing without use function]
- [Mocks]
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
        - [Mock handle substitution]
    - [Mock builders]
        - [Customizing the mock class]
        - [Creating mocks from a builder]
        - [Generating mock classes from a builder]
        - [Copying mock builders]
    - [Pausing mock recording]
    - [Mocking and non-public methods]
        - [Accessing non-public methods and properties]
    - [Mocking problematic classes]
    - [Terminology]
- [Stubs]
    - [Stubbing an existing callable]
        - [Stubbing global functions]
            - [Restoring global functions after stubbing]
            - [Alternatives for stubbing global functions]
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
            - [Yielding individual values from a generator]
            - [Yielding multiple values from a generator]
        - [Returning values from a generator]
        - [Returning arguments from a generator]
        - [Returning the "self" value from a generator]
        - [Throwing exceptions from a generator]
        - [Generator iterations that perform multiple actions]
            - [Setting passed-by-reference arguments in a generator]
            - [Invoking arguments in a generator]
            - [Invoking callables in a generator]
- [Spies]
    - [Spying on an existing callable]
        - [Spying on global functions]
            - [Restoring global functions after spying]
            - [Alternatives for spying on global functions]
    - [Anonymous spies]
    - [Call verification]
        - [Call count]
        - [Individual calls]
    - [Verifying spy input]
        - [Verifying that a call was made]
        - [Verifying that a spy was called with specific arguments]
    - [Verifying spy output]
        - [Verifying spy return values]
            - [Verifying generators returned by spies]
            - [Verifying iterables returned by spies]
        - [Verifying spy exceptions]
    - [Verifying spy progress]
    - [Verifying cardinality with spies]
        - [Verifying that a spy event happened an exact number of times]
        - [Verifying that a spy event happened a bounded number of times]
        - [Verifying that all spy events happen the same way]
    - [Labeling spies]
    - [Invoking spies]
    - [Pausing spy recording]
- [Calls]
    - [Retrieving calls from a spy]
    - [Verifying call input]
        - [Verifying that a call was made with specific arguments]
    - [Verifying call output]
        - [Verifying call return values]
            - [Verifying generators returned by calls]
            - [Verifying iterables returned by calls]
        - [Verifying call exceptions]
    - [Verifying call progress]
    - [Verifying cardinality with calls]
        - [Verifying that a call event happened an exact number of times]
        - [Verifying that a call event happened a bounded number of times]
        - [Verifying that all call events happen the same way]
- [Verification]
    - [Standard verification]
    - [Check verification]
    - [Understanding verification output]
        - [Expected behavior output]
        - [Cardinality output]
        - [Actual behavior output]
    - [Generator and iterable verification]
        - [Verifying iteration]
        - [Verifying produced values]
        - [Verifying values received by generators]
        - [Verifying exceptions received by generators]
        - [Verifying generator return values]
        - [Verifying generator exceptions]
        - [Verifying cardinality with generators and iterables]
        - [Iterable spy substitution]
        - [Iterable verification caveats]
            - [Using iterable spies changes the return value]
            - [Repeated iteration of iterable spies]
            - [Spying on iterables that implement array-like interfaces]
            - [Nested iterable spies]
    - [Order verification]
        - [Dynamic order verification]
        - [Order verification caveats]
            - [Intermediate events in order verification]
            - [Similar events in order verification]
    - [Verifying that there was no interaction with a mock]
    - [Using colored verification output]
- [Matchers]
    - [Matcher integrations]
        - [Hamcrest matchers]
        - [PHPUnit constraints]
        - [SimpleTest expectations]
    - [Shorthand matchers]
    - [The "any" matcher]
    - [The "equal to" matcher]
        - [When to use the "equal to" matcher]
        - [Special cases for the "equal to" matcher]
            - [Comparing exceptions]
            - [Comparing mocks]
    - [The "instance of" matcher]
    - [The "wildcard" matcher]
- [The exporter]
    - [The export format]
        - [Export identifiers and references]
            - [Export reference types]
            - [Export reference exclusions]
            - [Export identifier persistence]
        - [Exporting recursive values]
        - [Exporter special cases]
            - [Exporting closures]
            - [Exporting exceptions]
            - [Exporting mocks]
            - [Exporting stubs]
            - [Exporting spies]
    - [Export depth]
        - [Setting the export depth]
- [The API]
    - [The top-level API]
    - [The mock handle API]
    - [The mock builder API]
    - [The stub API]
    - [The generator answer API]
    - [The spy API]
    - [The call API]
    - [The arguments API]
    - [The verification result API]
    - [The iterable verification result API]
    - [The generator verification result API]
    - [The event API]
    - [The matcher API]
    - [The wildcard matcher API]
    - [Thrown exceptions]
        - [AssertionException]
        - [UndefinedArgumentException]
        - [UndefinedCallException]
        - [UndefinedEventException]
        - [UndefinedResponseException]
- [License]

## Installation

Available as various [Composer] packages, depending on the test framework in
use:

- For [Kahlan], use [eloquent/phony-kahlan] and import
  `Eloquent\Phony\Kahlan`.
- For [PHPUnit], use [eloquent/phony-phpunit] and import
  `Eloquent\Phony\Phpunit`.
- For [Peridot], use [eloquent/phony-peridot] and import `Eloquent\Phony`.
- For [Pho], use [eloquent/phony-pho] and import `Eloquent\Phony\Pho`.
- For [SimpleTest], use [eloquent/phony-simpletest] and import
  `Eloquent\Phony\Simpletest`.
- For other frameworks, or standalone usage, use [eloquent/phony] and import
  `Eloquent\Phony`.

See [Integration with test frameworks].

## Help

For help with a difficult testing scenario, questions regarding how to use
*Phony*, or to report issues with *Phony* itself, please open a [GitHub issue]
so that others may benefit from the outcome.

Alternatively, [@ezzatron] may be contacted directly via [Twitter].

## Usage

### Using this documentation

- The documentation can be searched with the standard search shortcut:
    - <kbd>⌘</kbd> + <kbd>F</kbd> for macOS and OS X
    - <kbd>Ctrl</kbd> + <kbd>F</kbd> for other platforms
- The menu automatically opens to the correct section as the documentation is
  scrolled.
- An expanded table of contents can be viewed by clicking the "+" symbol in the
  menu.
- Documentation for other versions can be selected in the bottom-right corner.

### Example test suites

See the [phony-examples] repository.

### Standalone usage

Install the [eloquent/phony] package, then:

```php
use function Eloquent\Phony\mock;

$handle = mock(ClassA::class);
$handle->methodA->with('argument')->returns('value');

$mock = $handle->get();

assert($mock->methodA('argument') === 'value');
$handle->methodA->calledWith('argument');
```

### [Kahlan] usage

Install the [eloquent/phony-kahlan] package, then:

```php
use function Eloquent\Phony\Kahlan\mock;

describe('Phony', function () {
    it('integrates with Kahlan', function () {
        $handle = mock('ClassA');
        $handle->methodA->with('argument')->returns('value');

        $mock = $handle->get();

        expect($mock->methodA('argument'))->toBe('value');
        $handle->methodA->calledWith('argument');
    });
});
```

The [eloquent/phony-kahlan] package also provides auto-wired mocks:

```php
use function Eloquent\Phony\Kahlan\on;

describe('Phony for Kahlan', function () {
    it('supports auto-wiring', function (ClassA $mock) {
        $handle = on($mock);
        $handle->methodA->with('argument')->returns('value');

        expect($mock->methodA('argument'))->toBe('value');
        $handle->methodA->calledWith('argument');
    });
});
```

### [Peridot] usage

Install the [eloquent/phony-peridot] package, then:

```php
use function Eloquent\Phony\mock;

describe('Phony', function () {
    it('integrates with Peridot', function () {
        $handle = mock(ClassA::class);
        $handle->methodA->with('argument')->returns('value');

        $mock = $handle->get();

        expect($mock->methodA('argument'))->to->equal('value');
        $handle->methodA->calledWith('argument');
    });
});
```

The [eloquent/phony-peridot] package also provides auto-wired mocks:

```php
use function Eloquent\Phony\on;

describe('Phony for Peridot', function () {
    it('supports auto-wiring', function (ClassA $mock) {
        $handle = on($mock);
        $handle->methodA->with('argument')->returns('value');

        expect($mock->methodA('argument'))->to->equal('value');
        $handle->methodA->calledWith('argument');
    });
});
```

### [Pho] usage

Install the [eloquent/phony-pho] package, then:

```php
use function Eloquent\Phony\Pho\mock;

describe('Phony', function () {
    it('integrates with Pho', function () {
        $handle = mock(ClassA::class);
        $handle->methodA->with('argument')->returns('value');

        $mock = $handle->get();

        expect($mock->methodA('argument'))->toBe('value');
        $handle->methodA->calledWith('argument');
    });
});
```

### [PHPUnit] usage

Install the [eloquent/phony-phpunit] package, then:

```php
use Eloquent\Phony\Phpunit\Phony;

class PhonyTest extends PHPUnit_Framework_TestCase
{
    public function testIntegration()
    {
        $handle = Phony::mock(ClassA::class);
        $handle->methodA->with('argument')->returns('value');

        $mock = $handle->get();

        $this->assertSame('value', $mock->methodA('argument'));
        $handle->methodA->calledWith('argument');
    }
}
```

### [SimpleTest] usage

Install the [eloquent/phony-simpletest] package, then:

```php
use Eloquent\Phony\Simpletest\Phony;

class PhonyTest extends UnitTestCase
{
    public function testIntegration()
    {
        $handle = Phony::mock(ClassA::class);
        $handle->methodA->with('argument')->returns('value');

        $mock = $handle->get();

        $this->assertSame($mock->methodA('argument'), 'value');
        $handle->methodA->calledWith('argument');
    }
}
```

### Integration with test frameworks

In order to provide the easiest integration with test frameworks, *Phony*
exposes the same [API] through multiple namespaces. Integration is as simple as
picking the correct [Composer] package for the framework in use, and importing
the relevant namespace:

- For [Kahlan], use [eloquent/phony-kahlan] and import
  `Eloquent\Phony\Kahlan`.
- For [PHPUnit], use [eloquent/phony-phpunit] and import
  `Eloquent\Phony\Phpunit`.
- For [Peridot], use [eloquent/phony-peridot] and import `Eloquent\Phony`.
- For [Pho], use [eloquent/phony-pho] and import `Eloquent\Phony\Pho`.
- For [SimpleTest], use [eloquent/phony-simpletest] and import
  `Eloquent\Phony\Simpletest`.
- For other frameworks, or standalone usage, use [eloquent/phony] and import
  `Eloquent\Phony`.

### Importing

There are two ways to import *Phony*'s [API]. The most appropriate choice will
depend on the test framework in use, and the user's preferred coding style.

#### Importing with [use function]

If the version of PHP in use supports [use function], the top-level functions
can be imported from the appropriate namespace and used directly:

```php
use function Eloquent\Phony\mock;

$handle = mock(ClassA::class);
```

#### Importing without [use function]

A static facade implementation is also provided for those who prefer a more
"traditional" approach:

```php
use Eloquent\Phony\Phony;

$handle = Phony::mock(ClassA::class);
```

## Mocks

*Mocks* are objects that can be used as a substitute for another object. This
can be useful when a "real" object becomes difficult to use in a test.

### Mocking basics

Any class, interface, or trait can be mocked. To create a mock, use
[`mock()`](#facade.mock):

```php
$handle = mock(ClassA::class);        // with `use function`
$handle = Phony::mock(ClassA::class); // without `use function`
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

To access the actual mock object, call the [`get()`](#handle.get) method of the
handle:

```php
$mock = $handle->get();
```

### Partial mocks

*Phony* supports "partial mocks", or mocks that do not override methods by
default. To create a partial mock, use [`partialMock()`](#facade.partialMock):

```php
$handle = partialMock(ClassA::class);        // with `use function`
$handle = Phony::partialMock(ClassA::class); // without `use function`
```

Constructor arguments can be passed to [`partialMock()`](#facade.partialMock) as
the second parameter:

```php
$handle = partialMock(ClassA::class, ['argumentA', 'argumentB']);
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

$handle = mock(Animal::class); // a generic animal mock
$handle->proxy($cat);          // now it behaves exactly like `$cat`

listen($handle->get());        // outputs 'It said: Meow meow meow? Meow.'
```

The [`proxy()`](#handle.proxy) method is also fluent, meaning that mock creation
and proxying can be done in a single expression:

```php
$handle = mock(Animal::class)->proxy(new Cat());
```

### Mocking multiple types

Multiple interfaces and/or traits can be mocked simultaneously by passing an
array of types to [`mock()`](#facade.mock) or
[`partialMock()`](#facade.partialMock):

```php
$handle = mock([InterfaceA::class, InterfaceB::class, TraitA::class]);        // with `use function`
$handle = Phony::mock([InterfaceA::class, InterfaceB::class, TraitA::class]); // without `use function`
```

A single base class may also be mocked with other types:

```php
$handle = mock([ClassA::class, InterfaceA::class, TraitA::class]);        // with `use function`
$handle = Phony::mock([ClassA::class, InterfaceA::class, TraitA::class]); // without `use function`
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

$mock = $handle->get();

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

$handle->count->returns(111);
$mock = $handle->get();

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

$mock = $handle->get();

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

$mock = $handle->get();
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

$mock = $handle->get();

echo $mock->set('a', 1)->get('a'); // outputs '1'
```

### Static mocks

*Phony* can be used to stub the behavior of static methods of generated mock
classes. To modify the behavior of a static method, use
[`onStatic()`](#facade.onStatic) to obtain a static handle from either an
existing handle, or a mock instance:

```php
$handle = mock(DateTime::class);
$mock = $handle->get();

$static = onStatic($handle);        // with `use function`
$static = Phony::onStatic($handle); // without `use function`

$static = onStatic($mock);        // with `use function`
$static = Phony::onStatic($mock); // without `use function`
```

This static handle is just like a normal [mock handle], except that it refers to
static methods instead of instance methods:

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

### Calling a constructor manually

In order to stub methods called in the constructor of a partial mock, it is
necessary to defer construction of the mock object. To accomplish this, pass
`null` as the second argument to [`partialMock()`](#facade.partialMock), which
will cause *Phony* to bypass the constructor:

```php
$handle = partialMock(ClassA::class, null);
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
$handle = mock(ClassA::class);
$mock = $handle->get();

$handle->setLabel('a');

echo $handle->label();   // outputs 'a'
echo on($mock)->label(); // outputs 'a'
```

The [`setLabel()`](#handle.setLabel) method is also fluent, meaning that mock
creation and label setting can be done in a single expression:

```php
$mock = mock(ClassA::class)->setLabel('a')->get();
```

When a verification fails for a labeled mock, the output is similar to the
following:

![Example verification output when using a mock label][mock-label-image]

### Mock handles

Mock handles are objects that provide access to [stubs] for each method of a
mock object. Each method stub implements both [the stub API] for stubbing, and
[the spy API] for verification.

Mock handles are returned when [`mock()`](#facade.mock) or
[`partialMock()`](#facade.partialMock) is called:

```php
$handle = mock(ClassA::class);
$handle = partialMock(ClassA::class);
```

They can also be retrieved at any time from a mock instance, or another handle,
by using [`on()`](#facade.on):

```php
$handle = on($mock);        // with `use function`
$handle = Phony::on($mock); // without `use function`

$handle = on($otherHandle);        // with `use function`
$handle = Phony::on($otherHandle); // without `use function`
```

To access the actual mock object, call the [`get()`](#handle.get) method of the
handle:

```php
$mock = $handle->get();
```

Note that a static handle variant exists. See [Static mocks].

#### Mock handle substitution

*Phony* will sometimes accept a mock handle as equivalent to the mock it
represents. This simplifies some common mocking scenarios, and improves test
readability.

One such scenario is returning a [mock] from another [stub] \(this includes
stubbed mock methods). Returning a handle from a stub is equivalent to returning
the mock itself:

```php
$database = mock(Database::class);
$result = mock(Result::class);

// these two statements are equivalent
$database->select->returns($result);
$database->select->returns($result->get());
```

Another common situation is the use of a mock handle when
[matching stub arguments]. Use of a mock handle in a argument list is equivalent
to use of the mock itself:

```php
$database = mock(Database::class);
$query = mock(Query::class);
$result = mock(Result::class);

// these two statements are equivalent
$database->select->with($query)->returns($result);
$database->select->with($query->get())->returns($result);
```

The same is true when [verifying that a spy was called with specific arguments]:

```php
$database = mock(Database::class);
$query = mock(Query::class);

// these two statements are equivalent
$database->select->calledWith($query);
$database->select->calledWith($query->get());
```

There are other edge-case situations where *Phony* will exhibit this behavior.
Refer to the [API] documentation for more detailed information.

### Mock builders

Mock builders provide an alternative method for defining and creating mocks,
when more fine-grained control is desired. To create a mock builder, use
[`mockBuilder()`](#facade.mockBuilder):

```php
$builder = mockBuilder();        // with `use function`
$builder = Phony::mockBuilder(); // without `use function`
```

Types to mock can be passed directly to [`mockBuilder()`](#facade.mockBuilder)
in a similar fashion to [`mock()`](#facade.mock):

```php
$builder = mockBuilder([ClassA::class, InterfaceA::class]);
```

#### Customizing the mock class

Mock builders implement a fluent interface, with many methods for customizing
the generated mock class:

```php
$builder
    ->like(ClassA::class, InterfaceA::class)
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
automatically wrap the returned mock in a [mock handle]. To obtain a handle,
use [`on()`](#facade.on):

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
$mock = $handle->get();

$mock->methodA('a');
$handle->stopRecording();
$mock->methodA('b');
$handle->startRecording();
$mock->methodA('c');

$handle->methodA->calledWith('a'); // passes
$handle->methodA->calledWith('c'); // passes

$handle->methodA->calledWith('b'); // fails
```

### Mocking and non-public methods

*Phony*'s mocks do not require special setup for `protected` methods. [Stubbing]
and [verification] work exactly the same for `protected` methods as they do for
`public` methods:

```php
class Cat
{
    public function speak()
    {
        echo $this->think();
    }

    protected function think()
    {
        return 'Meow.';
    }
}

$handle = partialMock(Cat::class);
$cat = $handle->get();

$cat->speak();            // outputs 'Meow.'
$handle->think->called(); // passes

$handle->think->returns('Cower in fear, mortal.');

$cat->speak(); // outputs 'Cower in fear, mortal.'
```

Note however, that *Phony* does not magically make non-public methods accessible
from outside the class. See [Accessing non-public methods and properties] for
how to achieve this.

Also note that *Phony* **cannot** mock `private` methods. This is because the
mock class generated by *Phony* is a *sub-class* of the original type, and
"overriding" a `private` method does not change the behavior of the original
method.

#### Accessing non-public methods and properties

In some cases, it may be necessary to bypass the [visibility] restrictions
imposed by the `protected` and `private` keywords in order to test a
particularly difficult-to-reach branch of code.

This should be considered a [code smell], and ideally the system under test
should be refactored to avoid the need for such measures. If all else fails,
`protected` and `private` keywords *can* effectively be bypassed.

*Phony* does not help in these kinds of cases, because it does not change the
visibility of methods when mocking. That is, `public` methods remain `public`,
and `protected` methods remain `protected`.

The [Liberator] library is specifically designed to aid in circumventing
`protected` and `private` keywords to provide access to normally inaccessible
methods and properties.

It is also possible to bypass visibility restrictions, without a library, via
reflection:

```php
class Cat
{
    protected function think()
    {
        return $this->thought;
    }

    private $thought = 'Meow.';
}

$cat = new Cat();

$think = new ReflectionMethod($cat, 'think');
$think->setAccessible(true);
echo $think->invoke($cat); // outputs 'Meow.'

$thought = new ReflectionProperty($cat, 'thought');
$thought->setAccessible(true);
echo $thought->getValue($cat); // outputs 'Meow.'
```

### Mocking problematic classes

Some PHP classes can be difficult, if not impossible, to mock. In particular,
`final` classes can only be mocked via [proxy mocks], and only in certain
circumstances.

For up-to-date information on known issues with other problematic classes, see
[Mocking problematic classes][wiki-mocking-problematic-classes] in the wiki.

### Terminology

By some popular definitions, *Phony*'s mocks are not technically mocks at all,
because they do not implement "expectations". According to these definitions,
*Phony*'s mocks would be more correctly called stubs.

However, throughout *Phony*'s [API] and documentation, the term "mock" is used
to refer to any test double that is an object. The term "stub" is used to refer
to a callable that can be programmed to provide canned answers to incoming
calls.

## Stubs

*Stubs* are callable entities that can be configured to behave according to a
set of rules when called. In *Phony*, every stub also implements [the spy API].

### Stubbing an existing callable

Any callable can be stubbed, by passing the callable to
[`stub()`](#facade.stub):

```php
$stub = stub($callable);        // with `use function`
$stub = Phony::stub($callable); // without `use function`
```

By default, the created stub will return "empty" values. The exact value depends
on the [return type] of the callable, but in many cases it will be `null`:

```php
$stubA = stub(
    function () {
        return 'a';
    }
);
$stubB = stub(
    function (): int {
        return 111;
    }
);

var_dump($stubA()); // outputs 'NULL'
var_dump($stubB()); // outputs 'int(0)'
```

See [Default values for return types] for more information, and
[`emptyValue()`](#facade.emptyValue) for a full list of types and associated
"empty" values.

The stub can be configured to behave differently in specific circumstances,
whilst falling back to the default behavior during regular operation:

```php
$stub = stub('max')->with(2, 3, 1)->returns(9);

var_dump($stub(1, 2, 3)); // outputs 'NULL'
var_dump($stub(2, 3, 1)); // outputs 'int(9)'
```

The stub can also be configured to behave exactly like the original callable:

```php
$stub = stub('max')->forwards();

var_dump($stub(1, 2, 3)); // outputs 'int(3)'
var_dump($stub(4, 5, 6)); // outputs 'int(6)'
```

#### Stubbing global functions

When an "unqualified" function (one with no preceding backslash) is used from
within a namespace, PHP will attempt to find the function in the calling
namespace first, before looking for the function in the *global* namespace. This
behavior is known as [global function fallback].

This behavior can be exploited to allow a stub to replace a global function
during testing. To do so, two conditions must be met:

- The function must be called without any qualifying namespace or backslash.
- The function must be called from within a namespace; function calls from the
  global namespace cannot be stubbed.

To stub a function in the global namespace, use
[`stubGlobal()`](#facade.stubGlobal):

```php
$stub = stubGlobal($function, $namespace);        // with `use function`
$stub = Phony::stubGlobal($function, $namespace); // without `use function`
```

Where `$function` is the name of the function in the global namespace, and
`$namespace` is the namespace from which the function will be called.

To demonstrate, the following code will work well with
[`stubGlobal()`](#facade.stubGlobal):

```php
namespace Foo\Bar;

printf('Keep it %s.', 'real');
```

Under normal conditions, this code would output:

    Keep it real.

But if the `printf()` function is stubbed before executing this code, its
behavior can be changed:

```php
namespace Foo\Bar;
use function Eloquent\Phony\stubGlobal;

$stub = stubGlobal('printf', __NAMESPACE__)->returns("You're a total phony!");

printf('Keep it %s.', 'real');
```

This code will now output:

    You're a total phony!

##### Restoring global functions after stubbing

Leaving global functions stubbed after a test can affect the results of other
tests in the same namespace that make use of the same global function. To avoid
this problem, it is necessary to restore the behavior of stubbed global
functions after the test concludes.

To restore all stubbed and/or spied global functions, use
[`restoreGlobalFunctions()`](#facade.restoreGlobalFunctions):

```php
restoreGlobalFunctions();        // with `use function`
Phony::restoreGlobalFunctions(); // without `use function`
```

This method is suitable for use in the "tear down" phase of a test.

##### Alternatives for stubbing global functions

If the system under test is not suitable for stubbing via
[global function fallback], an alternative is to accept a callback via
dependency injection that can take a stub during testing:

```php
use function Eloquent\Phony\stub;

function functionA($sprintf = 'sprintf')
{
    echo $sprintf('Keep it %s.', 'real');
}

$stub = stub('sprintf')->returns("You're a total phony!");

functionA();      // outputs 'Keep it real.'
functionA($stub); // outputs "You're a total phony!"
```

Another alternative is to use a library like [Isolator], that allows the
injection of an explicit dependency that represents the functions in the global
namespace. This dependency can then be mocked during testing.

### Anonymous stubs

*Anonymous stubs* are stubs that do not wrap an existing callable. An anonymous
stub is created by calling [`stub()`](#facade.stub) without passing a callable:

```php
$stub = stub();        // with `use function`
$stub = Phony::stub(); // without `use function`
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
$handle = mock(ClassA::class);
$mock = $handle->get();
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
$factorial = stub(
    function ($phonySelf, $n) {
        if (0 === $n) {
            return 1;
        }

        return $n * $phonySelf($phonySelf, $n - 1);
    }
);
$factorial->forwards();

echo $factorial(0); // outputs '1'
echo $factorial(1); // outputs '1'
echo $factorial(2); // outputs '2'
echo $factorial(3); // outputs '6'
echo $factorial(4); // outputs '24'
echo $factorial(5); // outputs '120'
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
[`returns()`](#stub.returns) were called with no arguments, causing the stub to
return an "empty" value by default. For example, the two following stubs behave
the same:

```php
$stubA = stub($callable)
    ->with('*')->returns()
    ->with('a')->returns('x');

$stubB = stub($callable)
    // implicit ->with('*')->returns()
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

See [`emptyValue()`](#facade.emptyValue) for a full list of types and associated
"empty" values.

When using a specific class name as a [return type], the return value will be a
[mock] of the specified type:

```php
$stub = stub(function (): DateTime {})->returns();
$result = $stub();

echo $result instanceof DateTime ? 'true' : 'false'; // outputs 'true'
```

By necessity, the returned value will not be wrapped in a [mock handle].

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
interface Fluent
{
    public function methodA();
    public function methodB();
}

$handle = mock(Fluent::class)
$handle->methodA->returnsSelf();
$handle->methodB->returns('x');

$fluent = $handle->get();

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

Both [`does()`](#stub.does) and [`doesWith()`](#stub.doesWith) support
[magic "self" values]:

```php
$stub = stub()->does(
    function ($phonySelf, $argument) {
        // $phonySelf is the "self" value, $argument is the first argument
    }
);
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

This technique can be used to return a stub or mocked method to its default
behavior in specific circumstances:

```php
class Cat
{
    public function speak()
    {
        return 'Cower in fear, mortal.';
    }
}

$handle = mock(Cat::class);
$handle->speak->returns('Meow.');
$handle->speak(true)->forwards();

$cat = $handle->get();

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

Both [`calls()`](#stub.calls) and [`callsWith()`](#stub.callsWith) support
[magic "self" values]:

```php
$stub = stub()->calls(
    function ($phonySelf, $argument) {
        // $phonySelf is the "self" value, $argument is the first argument
    }
);
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
object can be used to further customize the behavior of the generator. See the
subsequent headings for details of these customizations.

Certain methods, such as [`returns()`](#generatorAnswer.returns), or
[`throws()`](#generatorAnswer.throws), mark the "end" of generator answer. When
a generator answer is "ended", the original stub is returned, allowing continued
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
[`generates()`](#stub.generates) as any iterable value:

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

##### Yielding individual values from a generator

For more complicated generator behavior stubbing,
[`yields()`](#generatorAnswer.yields) can be used to interleave yields with
other actions:

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

##### Yielding multiple values from a generator

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

Calling [`returns()`](#generatorAnswer.returns) with multiple arguments allows
for easy specification of the generator return value on subsequent invocations.
For example, the two following stubs behave the same:

```php
$stubA = stub()
    ->generates()->returns('x', 'y');

$generatorA = $stubA();
$generatorB = $stubA();
iterator_to_array($generatorA);
iterator_to_array($generatorB);

echo $generatorA->getReturn(); // outputs 'x'
echo $generatorB->getReturn(); // outputs 'y'

$stubB = stub()
    ->generates()->returns('x')
    ->generates()->returns('y');

$generatorA = $stubB();
$generatorB = $stubB();
iterator_to_array($generatorA);
iterator_to_array($generatorB);

echo $generatorA->getReturn(); // outputs 'x'
echo $generatorB->getReturn(); // outputs 'y'
```

Note that it is perfectly valid to call [`returns()`](#generatorAnswer.returns)
with no arguments in order to end the generator by returning `null`:

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

The stub [self value] can be returned from a generator by using
[`returnsSelf()`](#generatorAnswer.returnsSelf) on any [generator answer]:

```php
$handle = mock();
$handle->methodA->generates()->returnsSelf();

$mock = $handle->get();
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

Calling [`throws()`](#generatorAnswer.throws) with multiple arguments allows for
easy specification of the thrown exception on subsequent invocations. For
example, the two following stubs behave the same:

```php
$exceptionA = new RuntimeException('You done goofed.');
$exceptionB = new RuntimeException('Consequences will never be the same.');

$stubA = stub()
    ->generates()->throws($exceptionA, $exceptionB);

$generatorA = $stubA();
$generatorB = $stubA();

iterator_to_array($generatorA); // throws $exceptionA
iterator_to_array($generatorB); // throws $exceptionB

$stubB = stub()
    ->generates()->throws($exceptionA)
    ->generates()->throws($exceptionB);

$generatorA = $stubB();
$generatorB = $stubB();

iterator_to_array($generatorA); // throws $exceptionA
iterator_to_array($generatorB); // throws $exceptionB
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

Most of the methods in [the spy API] are mirrored in [the call API].

### Spying on an existing callable

Any callable can be wrapped in a spy, by passing the callable to
[`spy()`](#facade.spy):

```php
$spy = spy($callable);        // with `use function`
$spy = Phony::spy($callable); // without `use function`
```

The created spy will behave exactly like the wrapped callable:

```php
$spy = spy('max');

echo $spy(2, 3, 1); // outputs '3'
```

#### Spying on global functions

When an "unqualified" function (one with no preceding backslash) is used from
within a namespace, PHP will attempt to find the function in the calling
namespace first, before looking for the function in the *global* namespace. This
behavior is known as [global function fallback].

This behavior can be exploited to allow a spy to record interactions with a
global function during testing. To do so, two conditions must be met:

- The function must be called without any qualifying namespace or backslash.
- The function must be called from within a namespace; function calls from the
  global namespace cannot be spied on.

To spy on a function in the global namespace, use
[`spyGlobal()`](#facade.spyGlobal):

```php
$spy = spyGlobal($function, $namespace);        // with `use function`
$spy = Phony::spyGlobal($function, $namespace); // without `use function`
```

Where `$function` is the name of the function in the global namespace, and
`$namespace` is the namespace from which the function will be called.

To demonstrate, the following code shows that if the `sprintf()` function is
spied on before it is called, its input and output can be verified:

```php
namespace Foo\Bar;
use function Eloquent\Phony\spyGlobal;

$spy = spyGlobal('sprintf', __NAMESPACE__);

$message = sprintf('Keep it %s.', 'real');

$spy->calledWith('Keep it %s.', 'real'); // verification passes
$spy->returned('Keep it real.');         // verification passes
```

##### Restoring global functions after spying

Leaving global functions spied after a test can result in additional memory use,
as all further interactions with the function will be recorded by *Phony*. To
avoid this problem, it is necessary to restore the behavior of spied global
functions after the test concludes.

To restore all spied and/or stubbed global functions, use
[`restoreGlobalFunctions()`](#facade.restoreGlobalFunctions):

```php
restoreGlobalFunctions();        // with `use function`
Phony::restoreGlobalFunctions(); // without `use function`
```

This method is suitable for use in the "tear down" phase of a test.

##### Alternatives for spying on global functions

If the system under test is not suitable for spying via
[global function fallback], an alternative is to accept a callback via
dependency injection that can take a spy during testing:

```php
use function Eloquent\Phony\spy;

function functionA($sprintf = 'sprintf')
{
    $message = $sprintf('Keep it %s.', 'real');
}

$spy = spy('sprintf');

functionA($spy);

$spy->calledWith('Keep it %s.', 'real'); // verification passes
$spy->returned('Keep it real.');         // verification passes
```

Another alternative is to use a library like [Isolator], that allows the
injection of an explicit dependency that represents the functions in the global
namespace. This dependency can then be mocked during testing.

### Anonymous spies

*Anonymous spies* are spies that do not wrap an existing callable. Their only
purpose is to record input arguments. An anonymous spy is created by calling
[`spy()`](#facade.spy) without passing a callable:

```php
$spy = spy();        // with `use function`
$spy = Phony::spy(); // without `use function`
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

Similar methods also exist for [verification results]:

```php
$spy->called()->firstCall();
$spy->called()->lastCall();
$spy->called()->callAt(0);
```

### Verifying spy input

#### Verifying that a call was made

To verify that a spy was called, use [`called()`](#spy.called):

```php
$spy->called();
```

Example output from [`called()`](#spy.called):

![Example output from $spy->called()][spy-called-image]

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

Arguments can be retrieved by calling [`arguments()`](#call.arguments) or
[`argument()`](#call.argument) on an [individual call] recorded via the spy:

```php
$spy->firstCall()->arguments(); // all arguments as an array
$spy->firstCall()->argument();  // first argument
$spy->firstCall()->argument(1); // second argument
```

Calls can also be retrieved from any [verification result]:

```php
$spy->called()->firstCall()->arguments(); // all arguments as an array
$spy->called()->firstCall()->argument();  // first argument
$spy->called()->firstCall()->argument(1); // second argument
```

Example output from [`calledWith()`](#spy.calledWith):

![Example output from $spy->calledWith()][spy-called-with-image]

### Verifying spy output

#### Verifying spy return values

To verify a spy's return value, use [`returned()`](#spy.returned):

```php
$spy->returned();    // returned anything
$spy->returned('a'); // returned 'a'
```

Return values can be retrieved by calling [`returnValue()`](#call.returnValue)
on an [individual call] recorded via the spy:

```php
$value = $spy->firstCall()->returnValue();
```

Calls can also be retrieved from any [verification result]:

```php
$value = $spy->called()->firstCall()->returnValue();
```

Example output from [`returned()`](#spy.returned):

![Example output from $spy->returned()][spy-returned-image]

##### Verifying generators returned by spies

To verify that a spy returned a [generator], use
[`generated()`](#spy.generated):

```php
$spy->generated();
```

The result returned by [`generated()`](#spy.generated) can be used for further
verification of the generator's behavior:

```php
$generator = $spy->generated(); // returned a generator

$generator->produced('a'); // generator yielded 'a'
$generator->returned('b'); // generator returned 'b'
```

See [Generator and iterable verification] for a complete explanation of the
available verifications.

Example output from [`generated()`](#spy.generated):

![Example output from $spy->generated()][spy-generated-image]

##### Verifying iterables returned by spies

To verify that a spy returned an iterable value, such as an array or iterator,
use [`iterated()`](#spy.iterated):

```php
$spy->iterated();
```

If [iterable spies] are enabled, the result returned by
[`iterated()`](#spy.iterated) can be used for further verification of the
iterable's behavior:

```php
$iterable = $spy->iterated(); // returned an iterable

$iterable->produced('a');      // iterable produced 'a'
$iterable->produced('b', 'c'); // iterable produced 'b' => 'c'
```

See [Generator and iterable verification] for a complete explanation of the
available verifications.

Example output from [`iterated()`](#spy.iterated):

![Example output from $spy->iterated()][spy-iterated-image]

#### Verifying spy exceptions

To verify that a spy threw an exception, use [`threw()`](#spy.threw):

```php
$spy->threw();                                         // threw any exception
$spy->threw('RuntimeException');                       // threw a runtime exception
$spy->threw(new RuntimeException('You done goofed.')); // threw a runtime exception with a specific message
```

Thrown exceptions can be retrieved by calling [`exception()`](#call.exception)
on an [individual call] recorded via the spy:

```php
$exception = $spy->firstCall()->exception();
```

Calls can also be retrieved from any [verification result]:

```php
$exception = $spy->called()->firstCall()->exception();
```

Example output from [`threw()`](#spy.threw):

![Example output from $spy->threw()][spy-threw-image]

### Verifying spy progress

To verify that a spy call has completed, use [`completed()`](#spy.completed):

```php
$spy->completed();
```

When [generator spies] are in use, a call that returns a generator will not be
considered "complete" until the generator has been completely consumed via
iteration.

Similarly, when [iterable spies] are in use, a call that returns an iterable
will not be considered "complete" until the iterable has been completely
consumed via iteration.

To ignore iterable events, and simply verify that a spy has returned a value
*or* thrown an exception, use [`responded()`](#spy.responded):

```php
$spy->responded();
```

Responses can be retrieved by calling [`response()`](#call.response) on an
[individual call] recorded via the spy:

```php
list($exception, $value) = $spy->firstCall()->response();
```

Generator responses can be retrieved by calling
[`generatorResponse()`](#call.generatorResponse) on an [individual call]
recorded via the spy:

```php
list($exception, $value) = $spy->firstCall()->generatorResponse();
```

Calls can also be retrieved from any [verification result]:

```php
list($exception, $value) = $spy->called()->firstCall()->response();
list($exception, $value) = $spy->called()->firstCall()->generatorResponse();
```

Example output from [`completed()`](#spy.completed):

![Example output from $spy->completed()][spy-completed-image]

Example output from [`responded()`](#spy.responded):

![Example output from $spy->responded()][spy-responded-image]

### Verifying cardinality with spies

When used with a spy, cardinality modifiers change the amount of **calls** that
must meet the requirements of a subsequent verification.

This differs slightly from their usage with calls, where cardinality modifiers
change the amount of **events** within the call that must meet the requirements
of a subsequent verification.

Note that cardinality also applies differently when using generator and iterable
verification. See [Verifying cardinality with generators and iterables].

Cardinality must be specified **before** verification, and can be applied to
any verification:

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

![Example verification output when using a spy label][spy-label-image]

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
[The call API] mirrors the methods available on [the spy API].

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

Example output from [`calledWith()`](#call.calledWith):

![Example output from $call->calledWith()][call-called-with-image]

### Verifying call output

#### Verifying call return values

To verify a call's return value, use [`returned()`](#call.returned):

```php
$call->returned();    // returned anything
$call->returned('a'); // returned 'a'
```

Return values can be retrieved with [`returnValue()`](#call.returnValue):

```php
$value = $call->returnValue();
```

Example output from [`returned()`](#call.returned):

![Example output from $call->returned()][call-returned-image]

##### Verifying generators returned by calls

To verify that a call returned a [generator], use
[`generated()`](#call.generated):

```php
$call->generated();
```

The result returned by [`generated()`](#call.generated) can be used for further
verification of the generator's behavior:

```php
$generator = $call->generated(); // returned a generator

$generator->produced('a'); // generator yielded 'a'
$generator->returned('b'); // generator returned 'b'
```

See [Generator and iterable verification] for a complete explanation of the
available verifications.

Example output from [`generated()`](#call.generated):

![Example output from $call->generated()][call-generated-image]

##### Verifying iterables returned by calls

To verify that a call returned an iterable value, such as an array or iterator,
use [`iterated()`](#call.iterated):

```php
$call->iterated();
```

If [iterable spies] are enabled, the result returned by
[`iterated()`](#call.iterated) can be used for further verification of the
iterable's behavior:

```php
$iterable = $call->iterated(); // returned an iterable

$iterable->produced('a');      // iterable produced 'a'
$iterable->produced('b', 'c'); // iterable produced 'b' => 'c'
```

See [Generator and iterable verification] for a complete explanation of the
available verifications.

Example output from [`iterated()`](#call.iterated):

![Example output from $call->iterated()][call-iterated-image]

#### Verifying call exceptions

To verify that a call threw an exception, use [`threw()`](#call.threw):

```php
$call->threw();                                         // threw any exception
$call->threw('RuntimeException');                       // threw a runtime exception
$call->threw(new RuntimeException('You done goofed.')); // threw a runtime exception with a specific message
```

Thrown exceptions can be retrieved with [`exception()`](#call.exception):

```php
$exception = $call->exception();
```

Example output from [`threw()`](#call.threw):

![Example output from $call->threw()][call-threw-image]

### Verifying call progress

To verify that a call has completed, use [`completed()`](#call.completed):

```php
$call->completed();
```

When [generator spies] are in use, a call that returns a generator will not be
considered "complete" until the generator has been completely consumed via
iteration.

Similarly, when [iterable spies] are in use, a call that returns an iterable
will not be considered "complete" until the iterable has been completely
consumed via iteration.

To ignore iterable events, and simply verify that a call has returned a value
*or* thrown an exception, use [`responded()`](#call.responded):

```php
$call->responded();
```

Responses can be retrieved with [`exception()`](#call.exception):

```php
list($exception, $value) = $call->response();
```

Generator responses can be retrieved with [`exception()`](#call.exception):

```php
list($exception, $value) = $call->generatorResponse();
```

Example output from [`completed()`](#call.completed):

![Example output from $call->completed()][call-completed-image]

Example output from [`responded()`](#call.responded):

![Example output from $call->responded()][call-responded-image]

### Verifying cardinality with calls

When used with a call, cardinality modifiers change the amount of times that
**events** within that call must meet the requirements of a subsequent
verification.

This differs slightly from their usage with spies, where cardinality modifiers
change the amount of **calls** that must meet the requirements of a subsequent
verification.

Cardinality must be specified **before** verification, and can be applied to
any verification:

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
verification methods and functions across its [API], but they all behave in a
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
    - [Verifying spy output]
        - [Verifying spy return values]
            - [Verifying generators returned by spies]
            - [Verifying iterables returned by spies]
        - [Verifying spy exceptions]
    - [Verifying spy progress]
    - [Verifying cardinality with spies]
        - [Verifying that a spy event happened an exact number of times]
        - [Verifying that a spy event happened a bounded number of times]
        - [Verifying that all spy events happen the same way]
- [Calls]
    - [Verifying call input]
        - [Verifying that a call was made with specific arguments]
    - [Verifying call output]
        - [Verifying call return values]
            - [Verifying generators returned by calls]
            - [Verifying iterables returned by calls]
        - [Verifying call exceptions]
    - [Verifying call progress]
    - [Verifying cardinality with calls]
        - [Verifying that a call event happened an exact number of times]
        - [Verifying that a call event happened a bounded number of times]
        - [Verifying that all call events happen the same way]
- [Verification]
    - [Standard verification]
    - [Check verification]
    - [Understanding verification output]
        - [Expected behavior output]
        - [Cardinality output]
        - [Actual behavior output]
    - [Generator and iterable verification]
        - [Verifying iteration]
        - [Verifying produced values]
        - [Verifying values received by generators]
        - [Verifying exceptions received by generators]
        - [Verifying generator return values]
        - [Verifying generator exceptions]
        - [Verifying cardinality with generators and iterables]
        - [Iterable spy substitution]
        - [Iterable verification caveats]
            - [Using iterable spies changes the return value]
            - [Repeated iteration of iterable spies]
            - [Spying on iterables that implement array-like interfaces]
            - [Nested iterable spies]
    - [Order verification]
        - [Dynamic order verification]
        - [Order verification caveats]
            - [Intermediate events in order verification]
            - [Similar events in order verification]
    - [Verifying that there was no interaction with a mock]
    - [Using colored verification output]

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

### Understanding verification output

See also: [The export format].

Typical verification output looks something like the following example. Hovering
over a section of the example will provide an explanation of that part of the
output:

![Example verification output][verification-image]

#### Expected behavior output

The first part of the output explains what was expected. In the above example:

- `Expected ClassA[label]->methodA call` -
  Expected a call to `methodA` on a mock of `ClassA`, with a label of `label`.
- `with arguments:` - The arguments should match the conditions detailed below.
- `✓ "aardvark" (1 match)` - The first argument should be the string
  `"aardvark"`, and 1 call had a matching first argument.
- `✗ #0["bonobo", "chameleon", "dugong"] (0 matches)` - The second argument
  should be an array with 3 string elements, but no calls had a matching second
  argument.

#### Cardinality output

The second part of the output explains the "cardinality" of both the expectation
(if relevant), and the actual events. In the above example:

- `Matched 0 of 2:` - There were 2 calls, and neither of them matched.

#### Actual behavior output

The third part of the output explains what actually happened. In the above
example:

- `✗ Call #0:` - The first call did not match.
- `✓ "aardvark"` - The first argument of the first call matched.
- `✗ #0["bonobo", "[-chameleon-]{+capybara+}", "dugong"]` - The second argument
  of the first call did not match. The second array element was expected to be
  `"chameleon"`, but it was actually `"capybara"`.
- `✗ Call #1:` - The second call did not match.
- `✗ "[-aardvark-]{+armadillo+}"` - The first argument of the second call did
  not match. The argument was expected to be `"aardvark"`, but it was actually
  `"armadillo"`.
- `✗ #0["bonobo", "chameleon", "[-dugong-]{+dormouse+}"]` - The second argument
  of the second call did not match. The third array element was expected to be
  `"dugong"`, but it was actually `"dormouse"`.

### Generator and iterable verification

When spying on a callable that returns a [generator], additional verification
can be performed on events that happen after the generator is returned, such as
the yielding of values. This is accomplished via the use of "generator spies".

The use of generator spies is an optional behavior that can be disabled by
calling [`setUseGeneratorSpies()`](#spy.setUseGeneratorSpies) on a spy:

```php
$spy->setUseGeneratorSpies(false);
```

"Iterable spies" also exist for other iterators and arrays, but are disabled
by default because they typically change the return type of the spy. This
feature can be enabled by calling
[`setUseIterableSpies()`](#spy.setUseIterableSpies) on a spy:

```php
$spy->setUseIterableSpies(true);
```

Both [`setUseGeneratorSpies()`](#spy.setUseGeneratorSpies) and
[`setUseIterableSpies()`](#spy.setUseIterableSpies) are fluent, meaning
spy creation and setting of these options can be done in a single expression:

```php
$spy = spy()
    ->setUseIterableSpies(true)
    ->setUseGeneratorSpies(false);
```

#### Verifying iteration

To verify that iteration of a generator or iterable commenced, use
[`used()`](#verification.used) on any [generator verification result] or
[iterable verification result]:

```php
$subject->generated()->used(); // iteration of the generator commenced
$subject->iterated()->used();  // iteration of the iterable commenced
```

To verify that iteration of a generator or iterable completed, use
[`consumed()`](#verification.consumed) on any [generator verification result] or
[iterable verification result]:

```php
$subject->generated()->consumed(); // iteration of the generator completed
$subject->iterated()->consumed();  // iteration of the iterable completed
```

Example output from [`used()`](#verification.used):

![Example output from $iterable->used()][verification-used-image]

Example output from [`consumed()`](#verification.consumed):

![Example output from $iterable->consumed()][verification-consumed-image]

#### Verifying produced values

To verify that a value was yielded by a generator, use
[`produced()`](#verification.produced) on any [generator verification result]:

```php
$generator = $subject->generated(); // returned a generator

$generator->produced();         // produced anything
$generator->produced('a');      // produced 'a' with any key
$generator->produced('a', 'b'); // produced 'b' with key 'a'
```

To verify that a value was produced by an iterable, use
[`produced()`](#verification.produced) on any [iterable verification result]:

```php
$iterable = $subject->iterated(); // returned an iterable

$iterable->produced();         // produced anything
$iterable->produced('a');      // produced 'a' with any key
$iterable->produced('a', 'b'); // produced 'b' with key 'a'
```

Example output from [`produced()`](#verification.produced):

![Example output from $iterable->produced()][verification-produced-image]

#### Verifying values received by generators

To verify that a value was received by a generator, use
[`received()`](#verification.received) on any [generator verification result]:

```php
$generator = $subject->generated(); // returned a generator

$generator->received();    // received anything
$generator->received('a'); // received 'a'
```

Example output from [`received()`](#verification.received):

![Example output from $generator->received()][verification-received-image]

#### Verifying exceptions received by generators

To verify that an exception was received by a generator, use
[`receivedException()`](#verification.receivedException) on any
[generator verification result]:

```php
$generator = $subject->generated(); // returned a generator

$generator->receivedException();                                         // received any exception
$generator->receivedException('RuntimeException');                       // received a runtime exception
$generator->receivedException(new RuntimeException('You done goofed.')); // received a runtime exception with a specific message
```

Example output from [`receivedException()`](#verification.receivedException):

![Example output from $generator->receivedException()][verification-received-exception-image]

#### Verifying generator return values

To verify a generator's return value, use [`returned()`](#verification.returned)
on any [generator verification result]:

```php
$generator = $subject->generated(); // returned a generator

$generator->returned();    // returned anything
$generator->returned('a'); // returned 'a'
```

Example output from [`returned()`](#verification.returned):

![Example output from $generator->returned()][verification-returned-image]

#### Verifying generator exceptions

To verify that a generator threw an exception, use
[`threw()`](#verification.threw) on any [generator verification result]:

```php
$generator = $subject->generated(); // returned a generator

$generator->threw();                                         // threw any exception
$generator->threw('RuntimeException');                       // threw a runtime exception
$generator->threw(new RuntimeException('You done goofed.')); // threw a runtime exception with a specific message
```

Example output from [`threw()`](#verification.threw):

![Example output from $generator->threw()][verification-threw-image]

#### Verifying cardinality with generators and iterables

When used **before** `generated()` or `iterated()`, cardinality modifiers change
the amount of calls that must return a [generator] or iterable:

```php
$subject->twice()->generated(); // returned a generator exactly 2 times
$subject->twice()->iterated();  // returned an iterable exactly 2 times
```

When used **after** `generated()` or `iterated()`, cardinality modifiers relate
to the subset of calls that already satisfied the initial verification:

```php
$subject->generated()->twice()->produced('a'); // produced 'a' from exactly 2 generator calls
$subject->iterated()->twice()->produced('a');  // produced 'a' from exactly 2 iterable calls
```

See [Verifying cardinality with spies] and [Verifying cardinality with calls]
for a full list or cardinality modifiers.

#### Iterable spy substitution

Phony will sometimes accept an iterable spy, or generator spy, as equivalent to
the iterable value it is spying on. This simplifies some stubbing and
verification scenarios.

When stubbing, iterable spies and generator spies are substituted when
[matching stub arguments]:

```php
$stubA = stub()->setUseIterableSpies(true)->returnsArgument();
$iterable = ['a', 'b'];
$iterableSpy = $stubA($iterable);

$stubB = stub();

// these two statements are equivalent
$stubB->with($iterable)->returns('c');
$stubB->with($iterableSpy)->returns('c');
```

The same is true when [verifying that a spy was called with specific arguments]:

```php
$stubA = stub()->setUseIterableSpies(true)->returnsArgument();
$iterable = ['a', 'b'];
$iterableSpy = $stubA($iterable);

$stubB = stub();
$stubB($iterableSpy);

// these two statements are equivalent
$database->select->calledWith($iterable);
$database->select->calledWith($iterableSpy);
```

And also when [verifying spy return values]:

```php
$stub = stub()->setUseIterableSpies(true)->returnsArgument();
$iterable = ['a', 'b'];
$iterableSpy = $stub($iterable);

// these two statements are equivalent
$stub->returned($iterable);    // passes
$stub->returned($iterableSpy); // passes
```

There are other edge-case situations where *Phony* will exhibit this behavior.
Refer to the [API] documentation for more detailed information.

#### Iterable verification caveats

##### Using iterable spies changes the return value

When generator spies are enabled, the return value of spied functions that
produce generators will automatically be wrapped in another generator that spies
on the original. This is usually not a problem, because it is impossible to
distinguish the two generators aside from an identity comparison (`===`).

If the system under test relies upon the identity of returned generators, it may
be advisable to disable generator spies using
[`$spy->setUseGeneratorSpies(false)`](#spy.setUseGeneratorSpies).

Similarly, when iterable spies are enabled, the return value of functions that
produce iterable values will automatically be wrapped in an iterator that spies
on the original value. This can be more problematic than with generator spies,
because it changes the *type* of the return value. That is why iterable spies
are disabled by default.

##### Repeated iteration of iterable spies

Events are only recorded the first time an iterable spy is iterated over. If the
underlying value is an array, or is iterated with an iterator that supports
[`rewind()`], subsequent iterations will behave as expected, but no events will
be recorded.

This has no relevance for [generator spies], as generators cannot be rewound.

##### Spying on iterables that implement array-like interfaces

It's quite common for iterable objects to also implement [ArrayAccess] and
[Countable]. Iterable spies *always* implement these interfaces, which may be
incompatible with code that checks for these interfaces at run time.

If the underlying iterable object implements [ArrayAccess] and/or [Countable],
*Phony* will pass relevant calls through to the underlying implementation. If
these interfaces are *not* implemented, then the behavior of calls to
[ArrayAccess] and/or [Countable] is undefined, but will most likely result in an
error.

##### Nested iterable spies

It is possible for an iterable value to become nested in multiple iterable
spies. Consider the following:

```php
$spy = spy(
    function (Traversable $traversable) {
        return $traversable;
    }
);
$spy->setUseIterableSpies(true);

$traversable = new ArrayIterator(['a', 'b']);
$iterableSpy = $spy($traversable);
$nestedIterableSpy = $spy($iterableSpy);

// note that $traversable !== $iterableSpy !== $nestedIterableSpy
// in other words, they are all different iterator instances

iterator_to_array($nestedIterableSpy); // consume the outer-most traversable

$firstCall = $spy->callAt(0);          // the call that returned $iterableSpy
$firstCall->iterated()->produced('a'); // passes
$firstCall->iterated()->produced('b'); // passes

$secondCall = $spy->callAt(1);          // the call that returned $nestedIterableSpy
$secondCall->iterated()->produced('a'); // passes
$secondCall->iterated()->produced('b'); // passes
```

This example demonstrates that even when iterable spies are nested, in most
cases this is desirable, because it means that the iterable events are recorded
against *both* of the relevant calls.

Note that this nesting behavior can lead to some confusing results when an
iterable is partially traversed before being wrapped in another iterable spy.
Behavior in these circumstances is up to the PHP runtime, but generally
speaking, it "just works".

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

Example output from [`inOrder()`](#facade.inOrder):

![Example output from inOrder()][facade-in-order-image]

#### Dynamic order verification

When the number of events to verify is dynamic, the [`...` operator] can be
used:

```php
$calledEvents = [];
$returnedEvents = [];

foreach ($spies as $spy) {
    $calledEvents[] = $spy->called();
    $returnedEvents[] = $spy->returned();
}

// with `use function`
inOrder(
    anyOrder(...$calledEvents),
    anyOrder(...$returnedEvents)
);

// without `use function`
Phony::inOrder(
    Phony::anyOrder(...$calledEvents),
    Phony::anyOrder(...$returnedEvents)
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
$handle = mock(ClassA::class);
$mock = $handle->get();

$handle->noInteraction(); // passes

$mock->methodA();

$handle->noInteraction(); // fails
```

This verification will fail if any of the mock's methods have been called.

Example output from [`noInteraction()`](#handle.noInteraction):

![Example output from $handle->noInteraction()][handle-no-interaction-image]

### Using colored verification output

To control the use of [ANSI colored output], use
[`setUseColor()`](#facade.setUseColor):

```php
setUseColor($useColor);        // with `use function`
Phony::setUseColor($useColor); // without `use function`
```

Passing `true` turns color on, passing `false` turns color off, and passing
`null` will cause *Phony* to automatically detect whether color should be used
(this is the default behavior).

## Matchers

*Matchers* are used to determine whether the arguments of a call match certain
criteria. Utilized correctly, they can make verifications less brittle, and
improve the quality of a test suite.

*Phony* implements only a few matchers itself, and provides first-class support
for numerous third-party matcher libraries.

### Matcher integrations

#### [Kahlan] argument matchers

[Kahlan] is a popular "describe-it" style testing framework.
[Kahlan argument matchers] can be used in any *Phony* verification:

```php
$spy->calledWith(Arg::toBe('a'));
```

Kahlan argument matchers are supported when using the [eloquent/phony-kahlan]
package.

#### [Hamcrest] matchers

[Hamcrest] is a popular stand-alone matcher library, that originated in Java,
and has been ported to many languages, including an "official" port for PHP. Its
matchers can be used in any *Phony* verification:

```php
$spy->calledWith(equalTo('a'));
```

Hamcrest matchers are supported regardless of [which Composer package] is in
use.

#### [PHPUnit] constraints

[PHPUnit] is a popular unit testing framework. [PHPUnit matchers] \(referred to
as "constraints") can be used in any *Phony* verification:

```php
// where $this is a PHPUnit test case
$spy->calledWith($this->equalTo('a'));
```

PHPUnit constraints are supported when using the [eloquent/phony-phpunit]
package.

#### [SimpleTest] expectations

[SimpleTest] is a legacy unit testing framework. [SimpleTest matchers]
\(referred to as "expectations") can be used in any *Phony* verification:

```php
$spy->calledWith(new EqualExpectation('a'));
```

SimpleTest expectations are supported when using the [eloquent/phony-simpletest]
package.

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
$matcher = any();        // with `use function`
$matcher = Phony::any(); // without `use function`

$spy->calledWith(any()); // typical usage
```

### The "equal to" matcher

This is the default matcher used by *Phony*. It takes a single argument, and
matches values that are equal to that argument:

```php
$matcher = equalTo($value);        // with `use function`
$matcher = Phony::equalTo($value); // without `use function`

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
$matcher = equalTo(mock(ClassX::class)->setLabel('x')->get());

$a = mock(ClassX::class)->setLabel('x');
$a->methodX->returns('x');

echo $matcher->matches($a->get()) ? 'true' : 'false'; // outputs 'true'

$b = mock(ClassX::class)->setLabel('y');
$c = mock(ClassX::class);

echo $matcher->matches($b->get()) ? 'true' : 'false'; // outputs 'false'
echo $matcher->matches($c->get()) ? 'true' : 'false'; // outputs 'false'
```

Since mocks are labeled with a unique integer by default, they can normally be
used to differentiate calls without requiring the use of an 'identical to'
matcher:

```php
$a = mock(ClassX::class)->get();
$b = mock(ClassX::class)->get();

$stub = stub();
$stub->with($a)->returns('a');
$stub->with($b)->returns('b');

echo $stub($a); // outputs 'a'
echo $stub($b); // outputs 'b'
```

### The "instance of" matcher

The "instance of" matcher is a matcher that is functionally equivalent to the
`instanceof` operator:

```php
$matcher = anInstanceOf($type);        // with `use function`
$matcher = Phony::anInstanceOf($type); // without `use function`

$spy->calledWith(anInstanceOf(Iterator::class)); // typical usage
```

Just like `instanceof`, the "instance of" matcher supports both class names and
interfaces; which can be specified as either a string, or an object:

```php
$arrayIterator = new ArrayIterator([]);
$emptyIterator = new EmptyIterator();
$nonIterator = (object) [];

$matcher = anInstanceOf(Iterator::class);

var_dump($matcher->matches($arrayIterator)); // outputs 'bool(true)'
var_dump($matcher->matches($emptyIterator)); // outputs 'bool(true)'
var_dump($matcher->matches($nonIterator));   // outputs 'bool(false)'

$matcher = anInstanceOf(new ArrayIterator([]));

var_dump($matcher->matches($arrayIterator)); // outputs 'bool(true)'
var_dump($matcher->matches($emptyIterator)); // outputs 'bool(false)'
var_dump($matcher->matches($nonIterator));   // outputs 'bool(false)'
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
$spy->calledWith('*', 'b', 'c'); // this is not supported
$spy->calledWith('a', '*', 'c'); // this is not supported
```

## The exporter

When a *Phony* verification fails, the failure message will often contain string
representations of the actual, or expected PHP values involved. These string
representations are generated by *Phony*'s internal exporter.

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
`['a' => 1, 'b' => 2]`          | `'#0["a": 1, "b": 2]`
`(object) ['a' => 1, 'b' => 2]` | `'#0{a: 1, b: 2}'`
`new ClassA()`                  | `'ClassA#0{}'`

#### Export identifiers and references

Exported arrays, objects, and "wrapper" types (such as [mock handles]) include a
numeric identifier that can be used to identify repeated occurrences of the same
value. This is represented as a number sign (`#`) followed by the identifier:

```php
$value = (object) [];
// $value is exported as '#0{}'
```

When a value appears multiple times, its internal structure will only be
described the first time. Subsequent appearances will be indicated by a
reference to the value's identifier. This is represented as an ampersand (`&`)
followed by the identifier:

```php
$inner = [1, 2];
$value = [&$inner, &$inner];
// $value is exported as '#0[#1[1, 2], &1[]]'

$inner = (object) ['a' => 1];
$value = (object) ['b' => $inner, 'c' => $inner];
// $value is exported as '#0{b: #1{a: 1}, c: &1{}}'

$inner = mock(ClassA::class)->setLabel('mock-label');
$value = [$inner, $inner];
// $value is exported as '#0[handle#1(PhonyMock_ClassA_0#0{}[mock-label]), &1()]'
```

##### Export reference types

Array references appear followed by brackets (e.g. `&0[]`), object references
appear followed by braces (e.g. `&0{}`), and wrapper references appear followed
by parentheses (e.g. `&0()`):

```php
$array = [];
$object = (object) [];
$wrapper = spy('implode')->setLabel('spy-label');

$value = [&$array, &$array];
// $value is exported as '#0[#1[], &1[]]'

$value = [$object, $object];
// $value is exported as '#0[#0{}, &0{}]'

$value = [$wrapper, $wrapper];
// $value is exported as '#0[spy#1(implode)[spy-label], &1()]'
```

This is necessary in order to disambiguate references, because arrays and other
types can sometimes have the same identifier:

```php
$value = [
    (object) [],
    [
        (object) [],
    ],
];
// $value is exported as '#0[#0{}, #1[#1{}]]'
```

##### Export reference exclusions

As well as excluding the content, object references exclude the class name, and
wrapper references exclude the type, for brevity:

```php
$inner = new ClassA();
$inner->c = "d";
$value = (object) ['a' => $inner, 'b' => $inner];
// $value is exported as '#0{a: ClassA#1{c: "d"}, b: &1{}}'

$inner = mock();
$value = [$inner, $inner];
// $value is exported as '#0[handle#0(PhonyMock_0#1{}[0]), &0()]'
```

##### Export identifier persistence

Identifiers for objects and wrappers are persistent across invocations of the
exporter, and share a single sequence of numbers:

```php
$a = (object) [];
$b = (object) [];
$c = mock();

$value = [$a, $b, $c, $a];
// $value is exported as '#0[#0{}, #1{}, handle#2(PhonyMock_0#3{}[0]), &0{}]'

$value = [$b, $a, $b, $c];
// $value is exported as '#0[#1{}, #0{}, &1{}, handle#2(PhonyMock_0#3{}[0])]'
```

But due to PHP's limitations, array identifiers are only persistent within a
single exporter invocation:

```php
$a = [];
$b = [];

$valueA = [&$a, &$b, &$a];
$valueB = [&$b, &$a, &$b];
// both $valueA and $valueB are exported as '#0[#1[], #2[], &1[]]'
```

#### Exporting recursive values

If a recursive value is exported, the points of recursion are exported as
[references], in the same way that multiple instances of the same value are
handled:

```php
$value = [];
$value[] = &$value;
// $value is exported as '#0[&0[]]'

$value = (object) [];
$value->a = $value;
// $value is exported as '#0{a: &0{}}'
```

#### Exporter special cases

For certain types of values, the exporter will exhibit special behavior, in
order to improve the usefulness of its output, or to improve performance in
common use cases.

##### Exporting closures

When a closure is exported, the file path and start line number are included in
the output:

```php
$closure = function () {}; // file path is /path/to/example.php, line number is 123
// $closure is exported as 'Closure#0{}[example.php:123]'
```

Only the basename of the path is included, for brevity.

##### Exporting exceptions

When an exception is exported, some internal PHP details are stripped from the
output, including file path, line number, and stack trace:

```php
$exception = new Exception('a', 1, new Exception());
// $exception is exported as 'Exception#0{message: "a", code: 1, previous: Exception#1{}}'
```

Additionally, when the message is `''`, the code is `0`, and/or the previous
exception is `null`, these values are excluded for brevity:

```php
$exception = new RuntimeException();
// $exception is exported as 'RuntimeException#0{}'
```

##### Exporting mocks

When a mock is exported, some internal *Phony* details are stripped from the
output. In addition, if a [label][labeling mocks] has been set on the mock, this
will be included in the output:

```php
$handle = mock(ClassA::class)->setLabel('mock-label');

$mock = $handle->get();
// $mock is exported as 'PhonyMock_ClassA_0#0{}[mock-label]'
```

When a [mock handle] is exported, it is represented as a wrapper around the mock
itself:

```php
$handle = mock(ClassA::class)->setLabel('mock-label');
// $handle is exported as 'handle#1(PhonyMock_ClassA_0#0{}[mock-label])'
```

When a [static handle] is exported, it is represented as a wrapper around the
mock class:

```php
$staticHandle = onStatic($handle);
// $staticHandle is exported as 'static-handle#0(PhonyMock_ClassA_0)'
```

##### Exporting stubs

When a [stub] is exported, it is represented as a wrapper around the callable
entity that is stubbed, with the [label][spy label] also included in the output:

```php
$stub = stub('implode')->setLabel('stub-label');
// $stub is exported as 'stub#0(implode)[stub-label]'
```

Anonymous stubs have a simpler representation, since they don't wrap any
callable entity:

```php
$stub = stub()->setLabel('stub-label');
// $stub is exported as 'stub#0[stub-label]'
```

In the case of method stubs, class name information is also included:

```php
$handle = mock(ClassA::class)->setLabel('mock-label');
$staticHandle = onStatic($handle);

$stub = $handle->methodA->setLabel('stub-label');
// $stub is exported as 'stub#0(ClassA[mock-label]->methodA)[stub-label]'

$stub = $staticHandle->staticMethodA->setLabel('stub-label');
// $stub is exported as 'stub#1(ClassA::staticMethodA)[stub-label]'
```

##### Exporting spies

When a [spy] is exported, it is represented as a wrapper around the callable
entity that is spied on, with the [label][spy label] also included in the
output:

```php
$spy = spy('implode')->setLabel('spy-label');
// $spy is exported as 'spy#0(implode)[spy-label]'
```

Anonymous spies have a simpler representation, since they don't wrap any
callable entity:

```php
$spy = spy()->setLabel('spy-label');
// $spy is exported as 'spy#0[spy-label]'
```

In the case of method spies, class name information is also included:

```php
$object = new ClassA();

$spy = spy([$object, 'methodA'])->setLabel('spy-label');
// $spy is exported as 'spy#0(ClassA->methodA)[spy-label]'

$spy = spy([ClassA::class, 'staticMethodA'])->setLabel('spy-label');
// $spy is exported as 'spy#1(ClassA::staticMethodA)[spy-label]'
```

### Export depth

For complicated nested structures, exporting the entire value right down to its
innermost values is not always desirable. *Phony* sets a limit on how deep into
a nested structure the exporter will descend.

When a value is beyond the export depth, and has sub-values, its contents will
be replaced with a special notation that simply indicates how many sub-values
exist within that value:

```php
$value = [[], ['a', 'b', 'c']];
// $value is exported as '#0[#1[], #2[~3]]'

$value = [(object) [], (object) ['a', 'b', 'c']];
// $value is exported as '#0[#0{}, #1{~3}]'
```

#### Setting the export depth

To set the export depth, use [`setExportDepth()`](#facade.setExportDepth):

```php
setExportDepth($depth);        // with `use function`
Phony::setExportDepth($depth); // without `use function`
```

Where `$depth` is an integer indicating the desired export depth.

Negative values are treated as infinite depth, and will cause *Phony* to export
values in their entirety. Note that this can produce immense amounts of output
for large nested structures.

<a name="api" />

## The API

### The top-level API

This is the API presented by *Phony* once it is [imported]. These may be
functions or static methods depending on the method of importing:

- [`mock()`](#facade.mock)
- [`partialMock()`](#facade.partialMock)
- [`mockBuilder()`](#facade.mockBuilder)
- [`on()`](#facade.on)
- [`onStatic()`](#facade.onStatic)
- [`stub()`](#facade.stub)
- [`spy()`](#facade.spy)
- [`stubGlobal()`](#facade.stubGlobal)
- [`spyGlobal()`](#facade.spyGlobal)
- [`restoreGlobalFunctions()`](#facade.restoreGlobalFunctions)
- [`any()`](#facade.any)
- [`equalTo()`](#facade.equalTo)
- [`anInstanceOf()`](#facade.anInstanceOf)
- [`wildcard()`](#facade.wildcard)
- [`emptyValue()`](#facade.emptyValue)
- [`inOrder()`](#facade.inOrder)
- [`checkInOrder()`](#facade.checkInOrder)
- [`anyOrder()`](#facade.anyOrder)
- [`checkAnyOrder()`](#facade.checkAnyOrder)
- [`setUseColor()`](#facade.setUseColor)
- [`setExportDepth()`](#facade.setExportDepth)

<a name="facade.mock" />

----

> *[handle][handle-api]* [**mock**](#facade.mock)($types = []) *(with [use function])*<br />
> *[handle][handle-api]* Phony::[**mock**](#facade.mock)($types = []) *(without [use function])*

Create a new [full mock], and return a [mock handle].

*Each value in `$types` can be either a class name, or an [ad hoc mock]
definition. If only a single type is being mocked, the class name or definition
can be passed without being wrapped in an array.*

*See [Mocking basics].*

<a name="facade.partialMock" />

----

> *[handle][handle-api]* [**partialMock**](#facade.partialMock)($types = [], $arguments = []) *(with [use function])*<br />
> *[handle][handle-api]* Phony::[**partialMock**](#facade.partialMock)($types = [], $arguments = []) *(without [use function])*

Create a new [partial mock], and return a [mock handle].

*Each value in `$types` can be either a class name, or an [ad hoc mock]
definition. If only a single type is being mocked, the class name or definition
can be passed without being wrapped in an array.*

*Omitting `$arguments` will cause the original constructor to be called with an
empty argument list. However, if a `null` value is supplied for `$arguments`,
the original constructor will not be called at all.*

*See [Partial mocks], [Calling a constructor manually].*

<a name="facade.mockBuilder" />

----

> *[builder][mock-builder-api]* [**mockBuilder**](#facade.mockBuilder)($types = []) *(with [use function])*<br />
> *[builder][mock-builder-api]* Phony::[**mockBuilder**](#facade.mockBuilder)($types = []) *(without [use function])*

Create a new [mock builder].

*Each value in `$types` can be either a class name, or an [ad hoc mock]
definition. If only a single type is being mocked, the class name or definition
can be passed without being wrapped in an array.*

<a name="facade.on" />

----

> *[handle][handle-api]* [**on**](#facade.on)($mock) *(with [use function])*<br />
> *[handle][handle-api]* Phony::[**on**](#facade.on)($mock) *(without [use function])*

Returns a [mock handle] for `$mock`.

<a name="facade.onStatic" />

----

> *[handle][handle-api]* [**onStatic**](#facade.onStatic)($class) *(with [use function])*<br />
> *[handle][handle-api]* Phony::[**onStatic**](#facade.onStatic)($class) *(without [use function])*

Returns a static [static handle] for `$class`.

*See [Static mocks].*

<a name="facade.stub" />

----

> *[stub][stub-api]* [**stub**](#facade.stub)($callback = null) *(with [use function])*<br />
> *[stub][stub-api]* Phony::[**stub**](#facade.stub)($callback = null) *(without [use function])*

Create a new [stub].

*See [Stubbing an existing callable], [Anonymous stubs].*

<a name="facade.spy" />

----

> *[spy][spy-api]* [**spy**](#facade.spy)($callback = null) *(with [use function])*<br />
> *[spy][spy-api]* Phony::[**spy**](#facade.spy)($callback = null) *(without [use function])*

Create a new [spy].

*See [Spying on an existing callable], [Anonymous spies].*

<a name="facade.stubGlobal" />

----

> *[stub][stub-api]* [**stubGlobal**](#facade.stubGlobal)($function, $namespace) *(with [use function])*<br />
> *[stub][stub-api]* Phony::[**stubGlobal**](#facade.stubGlobal)($function, $namespace) *(without [use function])*

Create a stub of a function in the global namespace, and declare it as a
function in another namespace.

*Stubs created via this function do not forward to the original function by
default. This differs from stubs created by other methods.*

*See [Stubbing global functions].*

<a name="facade.spyGlobal" />

----

> *[spy][spy-api]* [**spyGlobal**](#facade.spyGlobal)($function, $namespace) *(with [use function])*<br />
> *[spy][spy-api]* Phony::[**spyGlobal**](#facade.spyGlobal)($function, $namespace) *(without [use function])*

Create a spy of a function in the global namespace, and declare it as a function
in another namespace.

*See [Spying on global functions].*

<a name="facade.restoreGlobalFunctions" />

----

> *void* [**restoreGlobalFunctions**](#facade.restoreGlobalFunctions)() *(with [use function])*<br />
> *void* Phony::[**restoreGlobalFunctions**](#facade.restoreGlobalFunctions)() *(without [use function])*

Restores the behavior of any functions in the global namespace that have been
altered via [`spyGlobal()`](#facade.spyGlobal) or
[`stubGlobal()`](#facade.stubGlobal).

*See [Spying on global functions], [Stubbing global functions].*

<a name="facade.any" />

----

> *[matcher][matcher-api]* [**any**](#facade.any)() *(with [use function])*<br />
> *[matcher][matcher-api]* Phony::[**any**](#facade.any)() *(without [use function])*

Create a new ["any" matcher].

<a name="facade.equalTo" />

----

> *[matcher][matcher-api]* [**equalTo**](#facade.equalTo)($value) *(with [use function])*<br />
> *[matcher][matcher-api]* Phony::[**equalTo**](#facade.equalTo)($value) *(without [use function])*

Create a new ["equal to" matcher].

<a name="facade.anInstanceOf" />

----

> *[matcher][matcher-api]* [**anInstanceOf**](#facade.anInstanceOf)($type) *(with [use function])*<br />
> *[matcher][matcher-api]* Phony::[**anInstanceOf**](#facade.anInstanceOf)($type) *(without [use function])*

Create a new ["instance of" matcher].

*The `$type` parameter accepts either a class name, an interface name, or an
object.*

<a name="facade.wildcard" />

----

> *[wildcard][wildcard-api]* [**wildcard**](#facade.wildcard)($value = null, $minimumArguments = 0, $maximumArguments = -1) *(with [use function])*<br />
> *[wildcard][wildcard-api]* Phony::[**wildcard**](#facade.wildcard)($value = null, $minimumArguments = 0, $maximumArguments = -1) *(without [use function])*

Create a new ["wildcard" matcher].

*The `$value` parameter accepts a value, or a [matcher], to check against each
argument that the wildcard matches.*

*Negative values for `$maximumArguments` represent "no maximum".*

<a name="facade.emptyValue" />

----

> *mixed* [**emptyValue**](#facade.emptyValue)($type) *(with [use function])*<br />
> *mixed* Phony::[**emptyValue**](#facade.emptyValue)($type) *(without [use function])*

Create a new "empty" value.

*The `$type` parameter accepts a [ReflectionType], which can be created via
PHP's built-in [reflection] API.*

*This table details the "empty" value that will be returned for each type:*

Type                        | Empty value
----------------------------|---------------
*no type, or nullable type* | `null`
`bool`                      | `false`
`int`                       | `0`
`float`                     | `.0`
`string`                    | `''`
`array`                     | `[]`
`iterable`                  | `[]`
`object`                    | `(object) []`
`stdClass`                  | `(object) []`
`callable`                  | `stub()`
`Closure`                   | `function () {}`
`Generator`                 | `(function () {return; yield;})()`
`ClassName`                 | `mock(ClassName::class)->get()`

<a name="facade.inOrder" />

----

> *[verification][verification-api]* [**inOrder**](#facade.inOrder)(...$events) *(with [use function])*
> throws [AssertionException]<br />
> *[verification][verification-api]* Phony::[**inOrder**](#facade.inOrder)(...$events) *(without [use function])*
> throws [AssertionException]

Throws an exception unless the supplied events happened in chronological order.

*Each value in `$events` should be an event, or a [verification result].*

*See [Order verification].*

<a name="facade.checkInOrder" />

----

> *[verification][verification-api]|null* [**checkInOrder**](#facade.checkInOrder)(...$events) *(with [use function])*<br />
> *[verification][verification-api]|null* Phony::[**checkInOrder**](#facade.checkInOrder)(...$events) *(without [use function])*

Checks if the supplied events happened in chronological order.

*Each value in `$events` should be an event, or a [verification result].*

*See [Order verification], [Check verification].*

<a name="facade.anyOrder" />

----

> *[verification][verification-api]* [**anyOrder**](#facade.anyOrder)(...$events) *(with [use function])*
> throws [AssertionException]<br />
> *[verification][verification-api]* Phony::[**anyOrder**](#facade.anyOrder)(...$events) *(without [use function])*
> throws [AssertionException]

Throws an exception unless at least one event is supplied.

*Each value in `$events` should be an event, or a [verification result].*

*See [Order verification].*

<a name="facade.checkAnyOrder" />

----

> *[verification][verification-api]|null* [**checkAnyOrder**](#facade.checkAnyOrder)(...$events) *(with [use function])*<br />
> *[verification][verification-api]|null* Phony::[**checkAnyOrder**](#facade.checkAnyOrder)(...$events) *(without [use function])*

Checks that at least one event is supplied.

*Each value in `$events` should be an event, or a [verification result].*

*See [Order verification], [Check verification].*

<a name="facade.setUseColor" />

----

> *void* [**setUseColor**](#facade.setUseColor)($useColor) *(with [use function])*<br />
> *void* Phony::[**setUseColor**](#facade.setUseColor)($useColor) *(without [use function])*

Turn on or off the use of [ANSI colored output].

*Pass `null` to detect automatically.*

*See [Using colored verification output].*

<a name="facade.setExportDepth" />

----

> *int* [**setExportDepth**](#facade.setExportDepth)($depth) *(with [use function])*<br />
> *int* Phony::[**setExportDepth**](#facade.setExportDepth)($depth) *(without [use function])*

Set the default export depth, and return the previous depth.

*Negative depths are treated as infinite depth.*

*See [Setting the export depth].*

### The mock handle API

[Mock handles] implement the following methods:

- [`$handle->get()`](#handle.get)
- [`$handle->$method`](`#handle.__get)
- [`$handle->stub()`](#handle.stub)
- [`$handle->label()`](#handle.label)
- [`$handle->setLabel()`](#handle.setLabel)
- [`$handle->construct()`](#handle.construct)
- [`$handle->constructWith()`](#handle.constructWith)
- [`$handle->clazz()`](#handle.clazz)
- [`$handle->className()`](#handle.className)
- [`$handle->full()`](#handle.full)
- [`$handle->partial()`](#handle.partial)
- [`$handle->proxy()`](#handle.proxy)
- [`$handle->defaultAnswerCallback()`](#handle.defaultAnswerCallback)
- [`$handle->setDefaultAnswerCallback()`](#handle.setDefaultAnswerCallback)
- [`$handle->noInteraction()`](#handle.noInteraction)
- [`$handle->checkNoInteraction()`](#handle.checkNoInteraction)
- [`$handle->stopRecording()`](#handle.stopRecording)
- [`$handle->startRecording()`](#handle.startRecording)

See also:

- [`mock()`](#facade.mock)
- [`partialMock()`](#facade.partialMock)
- [`on()`](#facade.on)
- [`onStatic()`](#facade.onStatic)

<a name="handle.get" />

----

> *mock* $handle->[**get**](#handle.get)()

Get the [mock].

*This method is not available on [static mock handles].*

*See [Mocking basics], [Mock handles].*

<a name="handle.__get" />
<a name="handle.stub" />

----

> $handle->[**$method**](#handle.__get) or
> *[stub][stub-api]* $handle->[**stub**](#handle.stub)($method, $isNewRule = true)

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

> *fluent* $handle->[**setLabel**](#handle.setLabel)($label)

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
possible; as in the case of a `final` class.*

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

[Mock builders] implement the following methods:

- [`clone $builder`](#builder.__clone)
- [`$builder->types()`](#builder.types)
- [`$builder->like()`](#builder.like)
- [`$builder->addMethod()`](#builder.addMethod)
- [`$builder->addProperty()`](#builder.addProperty)
- [`$builder->addStaticMethod()`](#builder.addStaticMethod)
- [`$builder->addStaticProperty()`](#builder.addStaticProperty)
- [`$builder->addConstant()`](#builder.addConstant)
- [`$builder->named()`](#builder.named)
- [`$builder->isFinalized()`](#builder.isFinalized)
- [`$builder->finalize()`](#builder.finalize)
- [`$builder->isBuilt()`](#builder.isBuilt)
- [`$builder->build()`](#builder.build)
- [`$builder->className()`](#builder.className)
- [`$builder->get()`](#builder.get)
- [`$builder->full()`](#builder.full)
- [`$builder->partial()`](#builder.partial)
- [`$builder->partialWith()`](#builder.partialWith)

See also:

- [`mockBuilder()`](#facade.mockBuilder)

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

> *fluent* $builder->[**like**](#builder.like)(...$types)

Add classes, interfaces, or traits.

*Each type value can be either a class name, or an [ad hoc mock] definition.*

*See [Customizing the mock class].*

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

> *fluent* $builder->[**named**](#builder.named)($className)

Set the class name.

*See [Customizing the mock class].*

<a name="builder.isFinalized" />

----

> *bool* $builder->[**isFinalized**](#builder.isFinalized)()

Returns `true` if this builder is finalized.

*See [Generating mock classes from a builder].*

<a name="builder.finalize" />

----

> *fluent* $builder->[**finalize**](#builder.finalize)()

Finalize the mock builder.

*See [Generating mock classes from a builder].*

<a name="builder.isBuilt" />

----

> *bool* $builder->[**isBuilt**](#builder.isBuilt)()

Returns `true` if the mock class has been built.

*See [Generating mock classes from a builder].*

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

> *mock* $builder->[**partialWith**](#builder.partialWith)($arguments = [], $label = '')

Create a new [partial mock].

*The constructor will be called with `$arguments`, unless `$arguments` is
`null`, in which case the constructor will not be called at all.*

*This method will always create a new mock.*

*Calling this method will finalize the mock builder.*

*This method supports reference parameters.*

*See [Creating mocks from a builder].*

### The stub API

In addition to [the spy API], [stubs] implement the following methods:

- [`$stub->self()`](#stub.self)
- [`$stub->setSelf()`](#stub.setSelf)
- [`$stub->defaultAnswerCallback()`](#stub.defaultAnswerCallback)
- [`$stub->setDefaultAnswerCallback()`](#stub.setDefaultAnswerCallback)
- [`$stub->with()`](#stub.with)
- [`$stub->calls()`](#stub.calls)
- [`$stub->callsWith()`](#stub.callsWith)
- [`$stub->callsArgument()`](#stub.callsArgument)
- [`$stub->callsArgumentWith()`](#stub.callsArgumentWith)
- [`$stub->setsArgument()`](#stub.setsArgument)
- [`$stub->does()`](#stub.does)
- [`$stub->doesWith()`](#stub.doesWith)
- [`$stub->forwards()`](#stub.forwards)
- [`$stub->returns()`](#stub.returns)
- [`$stub->returnsArgument()`](#stub.returnsArgument)
- [`$stub->returnsSelf()`](#stub.returnsSelf)
- [`$stub->throws()`](#stub.throws)
- [`$stub->generates()`](#stub.generates)

See also:

- [`stub()`](#facade.stub)
- [`stubGlobal()`](#facade.stubGlobal)
- [`$handle->$method`](#handle.__get)
- [`$handle->stub()`](#handle.stub)

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

*This method supports [mock handle substitution] and
[iterable spy substitution].*

*See [Matching stub arguments].*

<a name="stub.calls" />

----

> *fluent* $stub->[**calls**](#stub.calls)(...$callbacks)

Add callbacks to be called as part of an answer.

*Note that all supplied callbacks will be called in the same invocation.*

*See [Invoking callables].*

<a name="stub.callsWith" />

----

> *fluent* $stub->[**callsWith**](#stub.callsWith)($callback, $arguments = [], $prefixSelf = false, $suffixArgumentsObject = false, $suffixArguments = true)

Add a callback to be called as part of an answer.

*Note that all supplied callbacks will be called in the same invocation.*

*This method supports [mock handle substitution].*

*See [Invoking callables].*

<a name="stub.callsArgument" />

----

> *fluent* $stub->[**callsArgument**](#stub.callsArgument)(...$indices)

Add argument callbacks to be called as part of an answer.

*Calling this method with no arguments is equivalent to calling it with a single
argument of `0`.*

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

*This method supports [mock handle substitution].*

*See [Invoking arguments].*

<a name="stub.setsArgument" />

----

> *fluent* $stub->[**setsArgument**](#stub.setsArgument)($indexOrValue = null, $value = null)

Set the value of an argument passed by reference as part of an answer.

*If called with no arguments, sets the first argument to `null`.*

*If called with one argument, sets the first argument to `$indexOrValue`.*

*If called with two arguments, sets the argument at `$indexOrValue` to
`$value`.*

*This method supports [mock handle substitution].*

*See [Setting passed-by-reference arguments].*

<a name="stub.does" />

----

> *fluent* $stub->[**does**](#stub.does)(...$callbacks)

Add callbacks as answers.

*See [Using a callable as an answer].*

<a name="stub.doesWith" />

----

> *fluent* $stub->[**doesWith**](#stub.doesWith)($callback, $arguments = [], $prefixSelf = false, $suffixArgumentsObject = false, $suffixArguments = true)

Add a callback as an answer.

*The supplied arguments support reference parameters.*

*This method supports [mock handle substitution].*

*See [Using a callable as an answer].*

<a name="stub.forwards" />

----

> *fluent* $stub->[**forwards**](#stub.forwards)($arguments = [], $prefixSelf = false, $suffixArgumentsObject = false, $suffixArguments = true)

Add an answer that calls the wrapped callback.

*The supplied arguments support reference parameters.*

*This method supports [mock handle substitution].*

*See [Forwarding to the original callable].*

<a name="stub.returns" />

----

> *fluent* $stub->[**returns**](#stub.returns)(...$values)

Add answers that return values.

*Calling this method with no arguments is equivalent to calling it with a single
argument of `null`.*

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

> *fluent* $stub->[**throws**](#stub.throws)(...$exceptions)

Add answers that throw exceptions.

*Calling this method with no arguments is equivalent to calling it with a single
argument of `null`.*

*This method supports [mock handle substitution].*

*See [Throwing exceptions].*

<a name="stub.generates" />

----

> *[generator-answer][generator-answer-api]* $stub->[**generates**](#stub.generates)($values = [])

Add an answer that returns a generator, and return the answer for further
behavior customization.

*Any supplied `$values` will be yielded from the resulting generator.*

*See [Stubbing generators], [Yielding from a generator].*

### The generator answer API

When [stubbing generators], calls to [`generates()`](#stub.generates) produce
"generator answers", which implement the following methods:

- [`$generatorAnswer->calls()`](#generatorAnswer.calls)
- [`$generatorAnswer->callsWith()`](#generatorAnswer.callsWith)
- [`$generatorAnswer->callsArgument()`](#generatorAnswer.callsArgument)
- [`$generatorAnswer->callsArgumentWith()`](#generatorAnswer.callsArgumentWith)
- [`$generatorAnswer->setsArgument()`](#generatorAnswer.setsArgument)
- [`$generatorAnswer->yields()`](#generatorAnswer.yields)
- [`$generatorAnswer->yieldsFrom()`](#generatorAnswer.yieldsFrom)
- [`$generatorAnswer->returns()`](#generatorAnswer.returns)
- [`$generatorAnswer->returnsArgument()`](#generatorAnswer.returnsArgument)
- [`$generatorAnswer->returnsSelf()`](#generatorAnswer.returnsSelf)
- [`$generatorAnswer->throws()`](#generatorAnswer.throws)

See also:

- [`$stub->generates()`](#stub.generates)

<a name="generatorAnswer.calls" />

----

> *fluent* $generatorAnswer->[**calls**](#generatorAnswer.calls)(...$callbacks)

Add callbacks to be called as part of the answer.

*See [Invoking callables in a generator].*

<a name="generatorAnswer.callsWith" />

----

> *fluent* $generatorAnswer->[**callsWith**](#generatorAnswer.callsWith)($callback, $arguments = [], $prefixSelf = false, $suffixArgumentsObject = false, $suffixArguments = true)

Add callbacks to be called as part of the answer.

*This method supports [mock handle substitution].*

*See [Invoking callables in a generator].*

<a name="generatorAnswer.callsArgument" />

----

> *fluent* $generatorAnswer->[**callsArgument**](#generatorAnswer.callsArgument)(...$indices)

Add argument callbacks to be called as part of the answer.

*Calling this method with no arguments is equivalent to calling it with a single
argument of `0`.*

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

*This method supports [mock handle substitution].*

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

*This method supports [mock handle substitution].*

*See [Yielding individual values from a generator].*

<a name="generatorAnswer.yieldsFrom" />

----

> *fluent* $generatorAnswer->[**yieldsFrom**](#generatorAnswer.yieldsFrom)($values)

Add a set of yielded values to the answer.

*The `$values` argument can be an array, an iterator, or even another
generator.*

*This method supports [mock handle substitution].*

*See [Yielding multiple values from a generator].*

<a name="generatorAnswer.returns" />

----

> *[stub][stub-api]* $generatorAnswer->[**returns**](#generatorAnswer.returns)(...$values)

End the generator by returning a value.

*Calling this method with no arguments is equivalent to calling it with a single
argument of `null`.*

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

> *[stub][stub-api]* $generatorAnswer->[**throws**](#generatorAnswer.throws)(...$exceptions)

End the generator by throwing an exception.

*Calling this method with no arguments is equivalent to calling it with a single
argument of `null`.*

*This method supports [mock handle substitution].*

*See [Throwing exceptions from a generator].*

### The spy API

Both [spies] and [stubs] implement the following methods:

- [`$spy()`](#spy.__invoke)
- [`$spy->invoke()`](#spy.invoke)
- [`$spy->invokeWith()`](#spy.invokeWith)
- [`$spy->label()`](#spy.label)
- [`$spy->setLabel()`](#spy.setLabel)
- [`$spy->useGeneratorSpies()`](#spy.useGeneratorSpies)
- [`$spy->setUseGeneratorSpies()`](#spy.setUseGeneratorSpies)
- [`$spy->useIterableSpies()`](#spy.useIterableSpies)
- [`$spy->setUseIterableSpies()`](#spy.setUseIterableSpies)
- [`$spy->stopRecording()`](#spy.stopRecording)
- [`$spy->startRecording()`](#spy.startRecording)
- [`$spy->hasCalls()`](#spy.hasCalls)
- [`$spy->callCount()`](#spy.callCount)
- [`$spy->allCalls()`](#spy.allCalls)
- [`$spy->firstCall()`](#spy.firstCall)
- [`$spy->lastCall()`](#spy.lastCall)
- [`$spy->callAt()`](#spy.callAt)
- [`$spy->hasEvents()`](#spy.hasEvents)
- [`$spy->eventCount()`](#spy.eventCount)
- [`$spy->allEvents()`](#spy.allEvents)
- [`$spy->firstEvent()`](#spy.firstEvent)
- [`$spy->lastEvent()`](#spy.lastEvent)
- [`$spy->eventAt()`](#spy.eventAt)
- [`$spy->called()`](#spy.called)
- [`$spy->checkCalled()`](#spy.checkCalled)
- [`$spy->calledWith()`](#spy.calledWith)
- [`$spy->checkCalledWith()`](#spy.checkCalledWith)
- [`$spy->returned()`](#spy.returned)
- [`$spy->checkReturned()`](#spy.checkReturned)
- [`$spy->threw()`](#spy.threw)
- [`$spy->checkThrew()`](#spy.checkThrew)
- [`$spy->responded()`](#spy.responded)
- [`$spy->checkResponded()`](#spy.checkResponded)
- [`$spy->completed()`](#spy.completed)
- [`$spy->checkCompleted()`](#spy.checkCompleted)
- [`$spy->generated()`](#spy.generated)
- [`$spy->checkGenerated()`](#spy.checkGenerated)
- [`$spy->iterated()`](#spy.iterated)
- [`$spy->checkIterated()`](#spy.checkIterated)
- [`$spy->never()`](#spy.never)
- [`$spy->once()`](#spy.once)
- [`$spy->twice()`](#spy.twice)
- [`$spy->thrice()`](#spy.thrice)
- [`$spy->times()`](#spy.times)
- [`$spy->atLeast()`](#spy.atLeast)
- [`$spy->atMost()`](#spy.atMost)
- [`$spy->between()`](#spy.between)
- [`$spy->always()`](#spy.always)

See also:

- [`stub()`](#facade.stub)
- [`spy()`](#facade.spy)

<a name="spy.__invoke" />
<a name="spy.invoke" />

----

> [**$spy(...$arguments)**](#spy.__invoke) or
> *mixed* $spy->[**invoke**](#spy.invoke)(...$arguments)
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

> *string* $spy->[**label**](#spy.label)()

Get the [label][labeling spies].

<a name="spy.setLabel" />

----

> *fluent* $spy->[**setLabel**](#spy.setLabel)($label)

Set the [label][labeling spies].

<a name="spy.useGeneratorSpies" />

----

> *bool* $spy->[**useGeneratorSpies**](#spy.useGeneratorSpies)()

Returns `true` if this spy uses [generator spies].

<a name="spy.setUseGeneratorSpies" />

----

> *fluent* $spy->[**setUseGeneratorSpies**](#spy.setUseGeneratorSpies)($useGeneratorSpies)

Turn on or off the use of [generator spies].

<a name="spy.useIterableSpies" />

----

> *bool* $spy->[**useIterableSpies**](#spy.useIterableSpies)()

Returns `true` if this spy uses [iterable spies].

<a name="spy.setUseIterableSpies" />

----

> *fluent* $spy->[**setUseIterableSpies**](#spy.setUseIterableSpies)($useIterableSpies)

Turn on or off the use of [iterable spies].

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

<a name="spy.hasCalls" />

----

> *bool* $spy->[**hasCalls**](#spy.hasCalls)()

Returns `true` if any calls were recorded.

*See [Call verification].*

<a name="spy.callCount" />

----

> *int* $spy->[**callCount**](#spy.callCount)()

Get the number of calls.

*See [Call count].*

<a name="spy.allCalls" />

----

> *array\<[call][call-api]>* $spy->[**allCalls**](#spy.allCalls)()

Get all calls as an array.

*See [Call verification].*

<a name="spy.firstCall" />

----

> *[call][call-api]* $spy->[**firstCall**](#spy.firstCall)()
> throws [UndefinedCallException]

Get the first call.

*See [Individual calls].*

<a name="spy.lastCall" />

----

> *[call][call-api]* $spy->[**lastCall**](#spy.lastCall)()
> throws [UndefinedCallException]

Get the last call.

*See [Individual calls].*

<a name="spy.callAt" />

----

> *[call][call-api]* $spy->[**callAt**](#spy.callAt)($index = 0)
> throws [UndefinedCallException]

Get the call at `$index`.

*Negative indices are offset from the end of the list. That is, `-1` indicates
the last element, and `-2` indicates the second last element.*

*See [Individual calls].*

<a name="spy.hasEvents" />

----

> *bool* $spy->[**hasEvents**](#spy.hasEvents)()

Returns `true` if any events were recorded.

*See [Similar events in order verification].*

<a name="spy.eventCount" />

----

> *int* $spy->[**eventCount**](#spy.eventCount)()

Get the number of events.

*See [Similar events in order verification].*

<a name="spy.allEvents" />

----

> *array\<[event][event-api]>* $spy->[**allEvents**](#spy.allEvents)()

Get all events as an array.

*See [Similar events in order verification].*

<a name="spy.firstEvent" />

----

> *[event][event-api]* $spy->[**firstEvent**](#spy.firstEvent)()
> throws [UndefinedEventException]

Get the first event.

*See [Similar events in order verification].*

<a name="spy.lastEvent" />

----

> *[event][event-api]* $spy->[**lastEvent**](#spy.lastEvent)()
> throws [UndefinedEventException]

Get the last event.

*See [Similar events in order verification].*

<a name="spy.eventAt" />

----

> *[event][event-api]* $spy->[**eventAt**](#spy.eventAt)($index = 0)
> throws [UndefinedEventException]

Get the event at `$index`.

*Negative indices are offset from the end of the list. That is, `-1` indicates
the last element, and `-2` indicates the second last element.*

*See [Similar events in order verification].*

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

*This method supports [mock handle substitution] and
[iterable spy substitution].*

*See [Verifying that a spy was called with specific arguments].*

<a name="spy.checkCalledWith" />

----

> *[verification][verification-api]|null* $spy->[**checkCalledWith**](#spy.checkCalledWith)(...$arguments)

Checks if called with the supplied arguments.

*This method supports [mock handle substitution] and
[iterable spy substitution].*

*See [Verifying that a spy was called with specific arguments],
[Check verification].*

<a name="spy.returned" />

----

> *[verification][verification-api]* $spy->[**returned**](#spy.returned)($value = null)
> throws [AssertionException]

Throws an exception unless this spy returned the supplied value.

*When called with no arguments, this method simply checks that the spy returned
any value.*

*This method supports [mock handle substitution] and
[iterable spy substitution].*

*See [Verifying spy return values].*

<a name="spy.checkReturned" />

----

> *[verification][verification-api]|null* $spy->[**checkReturned**](#spy.checkReturned)($value = null)

Checks if this spy returned the supplied value.

*When called with no arguments, this method simply checks that the spy returned
any value.*

*This method supports [mock handle substitution] and
[iterable spy substitution].*

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

*This method supports [mock handle substitution] and
[iterable spy substitution].*

*See [Verifying spy exceptions].*

<a name="spy.checkThrew" />

----

> *[verification][verification-api]|null* $spy->[**checkThrew**](#spy.checkThrew)($type = null)

Checks if this spy threw an exception of the supplied type.

*When called with no arguments, this method simply checks that the spy threw any
exception.*

*When called with a string, this method checks that the spy threw an exception
that is an instance of `$type`.*

*When called with an exception instance, this method checks that the spy threw
an exception that is equal to the supplied instance.*

*When called with a [matcher], this method checks that the spy threw an
exception that matches the supplied matcher.*

*This method supports [mock handle substitution] and
[iterable spy substitution].*

*See [Verifying spy exceptions], [Check verification].*

<a name="spy.responded" />

----

> *[verification][verification-api]* $spy->[**responded**](#spy.responded)()
> throws [AssertionException]

Throws an exception unless this spy responded.

*A spy that has "responded" has returned a value, or thrown an exception.*

*See [Verifying spy progress].*

<a name="spy.checkResponded" />

----

> *[verification][verification-api]|null* $spy->[**checkResponded**](#spy.checkResponded)()

Checks if this spy responded.

*A spy that has "responded" has returned a value, or thrown an exception.*

*See [Verifying spy progress], [Check verification].*

<a name="spy.completed" />

----

> *[verification][verification-api]* $spy->[**completed**](#spy.completed)()
> throws [AssertionException]

Throws an exception unless this spy completed.

*When [generator spies] are in use, a call that returns a generator will not be
considered "complete" until the generator has been completely consumed via
iteration.*

*Similarly, when [iterable spies] are in use, a call that returns an iterable
will not be considered "complete" until the iterable has been completely
consumed via iteration.*

*See [Verifying spy progress].*

<a name="spy.checkCompleted" />

----

> *[verification][verification-api]|null* $spy->[**checkCompleted**](#spy.checkCompleted)()

Checks if this spy completed.

*When [generator spies] are in use, a call that returns a generator will not be
considered "complete" until the generator has been completely consumed via
iteration.*

*Similarly, when [iterable spies] are in use, a call that returns an iterable
will not be considered "complete" until the iterable has been completely
consumed via iteration.*

*See [Verifying spy progress], [Check verification].*

<a name="spy.generated" />

----

> *[generator-verification][generator-verification-api]* $spy->[**generated**](#spy.generated)()
> throws [AssertionException]

Throws an exception unless this spy returned a generator.

*See [Verifying generators returned by spies],
[Generator and iterable verification].*

<a name="spy.checkGenerated" />

----

> *[generator-verification][generator-verification-api]|null* $spy->[**checkGenerated**](#spy.checkGenerated)()

Checks if this spy returned a generator.

*See [Verifying generators returned by spies],
[Generator and iterable verification].*

<a name="spy.iterated" />

----

> *[iterable-verification][iterable-verification-api]* $spy->[**iterated**](#spy.iterated)()
> throws [AssertionException]

Throws an exception unless this spy returned an iterable.

*See [Verifying iterables returned by spies],
[Generator and iterable verification].*

<a name="spy.checkIterated" />

----

> *[iterable-verification][iterable-verification-api]|null* $spy->[**checkIterated**](#spy.checkIterated)()

Checks if this spy returned an iterable.

*See [Verifying iterables returned by spies],
[Generator and iterable verification].*

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

### The call API

[Calls] implement the following methods:

- [`$call->arguments()`](#call.arguments)
- [`$call->argument()`](#call.argument)
- [`$call->argumentCount()`](#call.argumentCount)
- [`$call->returnValue()`](#call.returnValue)
- [`$call->generatorReturnValue()`](#call.generatorReturnValue)
- [`$call->exception()`](#call.exception)
- [`$call->generatorException()`](#call.generatorException)
- [`$call->response()`](#call.response)
- [`$call->generatorResponse()`](#call.generatorResponse)
- [`$call->hasResponded()`](#call.hasResponded)
- [`$call->isIterable()`](#call.isIterable)
- [`$call->isGenerator()`](#call.isGenerator)
- [`$call->hasCompleted()`](#call.hasCompleted)
- [`$call->time()`](#call.time)
- [`$call->responseTime()`](#call.responseTime)
- [`$call->endTime()`](#call.endTime)
- [`$call->responseDuration()`](#call.responseDuration)
- [`$call->duration()`](#call.duration)
- [`$call->index()`](#call.index)
- [`$call->sequenceNumber()`](#call.sequenceNumber)
- [`$call->calledWith()`](#call.calledWith)
- [`$call->checkCalledWith()`](#call.checkCalledWith)
- [`$call->returned()`](#call.returned)
- [`$call->checkReturned()`](#call.checkReturned)
- [`$call->threw()`](#call.threw)
- [`$call->checkThrew()`](#call.checkThrew)
- [`$call->responded()`](#call.responded)
- [`$call->checkResponded()`](#call.checkResponded)
- [`$call->completed()`](#call.completed)
- [`$call->checkCompleted()`](#call.checkCompleted)
- [`$call->generated()`](#call.generated)
- [`$call->checkGenerated()`](#call.checkGenerated)
- [`$call->iterated()`](#call.iterated)
- [`$call->checkIterated()`](#call.checkIterated)
- [`$call->never()`](#call.never)
- [`$call->once()`](#call.once)
- [`$call->twice()`](#call.twice)
- [`$call->thrice()`](#call.thrice)
- [`$call->times()`](#call.times)
- [`$call->atLeast()`](#call.atLeast)
- [`$call->atMost()`](#call.atMost)
- [`$call->between()`](#call.between)
- [`$call->always()`](#call.always)

See also:

- [`$spy->allCalls()`](#spy.allCalls)
- [`$spy->firstCall()`](#spy.firstCall)
- [`$spy->lastCall()`](#spy.lastCall)
- [`$spy->callAt()`](#spy.callAt)
- [`$verification->allCalls()`](#verification.allCalls)
- [`$verification->firstCall()`](#verification.firstCall)
- [`$verification->lastCall()`](#verification.lastCall)
- [`$verification->callAt()`](#verification.callAt)

<a name="call.arguments" />

----

> *[arguments][arguments-api]* $call->[**arguments**](#call.arguments)()

Get the arguments.

*See [Verifying that a spy was called with specific arguments],
[Verifying that a call was made with specific arguments].*

<a name="call.argument" />

----

> *mixed* $call->[**argument**](#call.argument)($index = 0)
> throws [UndefinedArgumentException]

Get an argument by index.

*Negative indices are offset from the end of the list. That is, `-1` indicates
the last element, and `-2` indicates the second last element.*

*See [Verifying that a spy was called with specific arguments],
[Verifying that a call was made with specific arguments].*

<a name="call.argumentCount" />

----

> *int* $call->[**argumentCount**](#call.argumentCount)()

Get the number of arguments.

*See [Verifying that a spy was called with specific arguments],
[Verifying that a call was made with specific arguments].*

<a name="call.returnValue" />

----

> *mixed* $call->[**returnValue**](#call.returnValue)()
> throws [UndefinedResponseException]

Get the return value.

*An [UndefinedResponseException] will be thrown if the call has not yet
responded, or threw an exception.*

*See [Verifying spy return values], [Verifying call return values].*

<a name="call.generatorReturnValue" />

----

> *mixed* $call->[**generatorReturnValue**](#call.generatorReturnValue)()
> throws [UndefinedResponseException]

Get the generator return value.

*An [UndefinedResponseException] will be thrown if the call has not yet
responded, did not respond by returning a generator, has not completed
iteration, or if the generator ended by throwing an exception.*

*See [Verifying generator return values].*

<a name="call.exception" />

----

> *[Throwable]* $call->[**exception**](#call.exception)()
> throws [UndefinedResponseException]

Get the thrown exception.

*An [UndefinedResponseException] will be thrown if the call has not yet
responded, or did not throw an exception.*

*See [Verifying spy exceptions], [Verifying call exceptions].*

<a name="call.generatorException" />

----

> *[Throwable]* $call->[**generatorException**](#call.generatorException)()
> throws [UndefinedResponseException]

Get the exception thrown by the generator.

*An [UndefinedResponseException] will be thrown if the call has not yet
responded, did not respond by returning a generator, has not completed
iteration, or if the generator ended by returning a value.*

*See [Verifying generator exceptions].*

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

*See [Verifying spy progress], [Verifying call progress].*

<a name="call.generatorResponse" />

----

> *tuple\<[Throwable]|null,mixed>* $call->[**generatorResponse**](#call.generatorResponse)()
> throws [UndefinedResponseException]

Get the generator response.

*This method returns a 2-tuple. The first element is the thrown exception, or
`null` if no exception was thrown. The second element is the returned value,
or `null` if an exception was thrown.*

*An [UndefinedResponseException] will be thrown if the call has not yet
responded, did not respond by returning a generator, or if the generator has not
completed iteration.*

*See [Verifying spy progress], [Verifying call progress].*

<a name="call.hasResponded" />

----

> *bool* $call->[**hasResponded**](#call.hasResponded)()

Returns true if this call has responded.

*A call that has "responded" has returned a value, or thrown an exception.*

*See [Verifying spy progress], [Verifying call progress].*

<a name="call.isIterable" />

----

> *bool* $call->[**isIterable**](#call.isIterable)()

Returns true if this call has responded with an iterable.

*A call that has "responded" has returned a value, or thrown an exception.*

*See [Verifying spy progress], [Verifying call progress].*

<a name="call.isGenerator" />

----

> *bool* $call->[**isGenerator**](#call.isGenerator)()

Returns true if this call has responded with a generator.

*A call that has "responded" has returned a value, or thrown an exception.*

*See [Verifying spy progress], [Verifying call progress].*

<a name="call.hasCompleted" />

----

> *bool* $call->[**hasCompleted**](#call.hasCompleted)()

Returns true if this call has completed.

*When [generator spies] are in use, a call that returns a generator will not be
considered "complete" until the generator has been completely consumed via
iteration.*

*Similarly, when [iterable spies] are in use, a call that returns an iterable
will not be considered "complete" until the iterable has been completely
consumed via iteration.*

*See [Verifying spy progress], [Verifying call progress].*

<a name="call.time" />

----

> *float* $call->[**time**](#call.time)()

Get the time at which the call occurred, in seconds since the Unix epoch.

*See [Verifying spy progress], [Verifying call progress].*

<a name="call.responseTime" />

----

> *float|null* $call->[**responseTime**](#call.responseTime)()

Get the time at which the call responded, in seconds since the Unix epoch.

*If the call has not yet responded, `null` will be returned.*

*A call that has "responded" has returned a value, or thrown an exception.*

*See [Verifying spy progress], [Verifying call progress].*

<a name="call.endTime" />

----

> *float|null* $call->[**endTime**](#call.endTime)()

Get the time at which the call completed, in seconds since the Unix epoch.

*If the call has not yet completed, `null` will be returned.*

*When [generator spies] are in use, a call that returns a generator will not be
considered "complete" until the generator has been completely consumed via
iteration.*

*Similarly, when [iterable spies] are in use, a call that returns an iterable
will not be considered "complete" until the iterable has been completely
consumed via iteration.*

*See [Verifying spy progress], [Verifying call progress].*

<a name="call.responseDuration" />

----

> *float|null* $call->[**responseDuration**](#call.responseDuration)()

Get the call response duration, in seconds.

*If the call has not yet responded, `null` will be returned.*

*A call that has "responded" has returned a value, or thrown an exception.*

*See [Verifying spy progress], [Verifying call progress].*

<a name="call.duration" />

----

> *float|null* $call->[**duration**](#call.duration)()

Get the call response duration, in seconds.

*If the call has not yet completed, `null` will be returned.*

*When [generator spies] are in use, a call that returns a generator will not be
considered "complete" until the generator has been completely consumed via
iteration.*

*Similarly, when [iterable spies] are in use, a call that returns an iterable
will not be considered "complete" until the iterable has been completely
consumed via iteration.*

*See [Verifying spy progress], [Verifying call progress].*

<a name="call.index" />

----

> *int* $call->[**index**](#call.index)()

Get the call index.

*This number tracks the order of this call with respect to other calls made
against the same spy.*

<a name="call.sequenceNumber" />

----

> *int* $call->[**sequenceNumber**](#call.sequenceNumber)()

Get the sequence number.

*The sequence number is a unique number assigned to every event that Phony
records. The numbers are assigned sequentially, meaning that sequence numbers
can be used to determine event order.*

<a name="call.calledWith" />

----

> *[verification][verification-api]* $call->[**calledWith**](#call.calledWith)(...$arguments)
> throws [AssertionException]

Throws an exception unless called with the supplied arguments.

*This method supports [mock handle substitution] and
[iterable spy substitution].*

*See [Verifying that a call was made with specific arguments].*

<a name="call.checkCalledWith" />

----

> *[verification][verification-api]|null* $call->[**checkCalledWith**](#call.checkCalledWith)(...$arguments)

Checks if called with the supplied arguments.

*This method supports [mock handle substitution] and
[iterable spy substitution].*

*See [Verifying that a call was made with specific arguments],
[Check verification].*

<a name="call.returned" />

----

> *[verification][verification-api]* $call->[**returned**](#call.returned)($value = null)
> throws [AssertionException]

Throws an exception unless this call returned the supplied value.

*When called with no arguments, this method simply checks that the call returned
any value.*

*This method supports [mock handle substitution] and
[iterable spy substitution].*

*See [Verifying call return values].*

<a name="call.checkReturned" />

----

> *[verification][verification-api]|null* $call->[**checkReturned**](#call.checkReturned)($value = null)

Checks if this call returned the supplied value.

*When called with no arguments, this method simply checks that the call returned
any value.*

*This method supports [mock handle substitution] and
[iterable spy substitution].*

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

*This method supports [mock handle substitution] and
[iterable spy substitution].*

*See [Verifying call exceptions].*

<a name="call.checkThrew" />

----

> *[verification][verification-api]|null* $call->[**checkThrew**](#call.checkThrew)($type = null)

Checks if this call threw an exception of the supplied type.

*When called with no arguments, this method simply checks that the call threw
any exception.*

*When called with a string, this method checks that the call threw an exception
that is an instance of `$type`.*

*When called with an exception instance, this method checks that the call threw
an exception that is equal to the supplied instance.*

*When called with a [matcher], this method checks that the call threw an
exception that matches the supplied matcher.*

*This method supports [mock handle substitution] and
[iterable spy substitution].*

*See [Verifying call exceptions], [Check verification].*

<a name="call.responded" />

----

> *[verification][verification-api]* $call->[**responded**](#call.responded)()
> throws [AssertionException]

Throws an exception unless this call responded.

*A call that has "responded" has returned a value, or thrown an exception.*

*See [Verifying call progress].*

<a name="call.checkResponded" />

----

> *[verification][verification-api]|null* $call->[**checkResponded**](#call.checkResponded)()

Checks if this call responded.

*A call that has "responded" has returned a value, or thrown an exception.*

*See [Verifying call progress], [Check verification].*

<a name="call.completed" />

----

> *[verification][verification-api]* $call->[**completed**](#call.completed)()
> throws [AssertionException]

Throws an exception unless this call completed.

*When [generator spies] are in use, a call that returns a generator will not be
considered "complete" until the generator has been completely consumed via
iteration.*

*Similarly, when [iterable spies] are in use, a call that returns an iterable
will not be considered "complete" until the iterable has been completely
consumed via iteration.*

*See [Verifying call progress].*

<a name="call.checkCompleted" />

----

> *[verification][verification-api]|null* $call->[**checkCompleted**](#call.checkCompleted)()

Checks if this call completed.

*When [generator spies] are in use, a call that returns a generator will not be
considered "complete" until the generator has been completely consumed via
iteration.*

*Similarly, when [iterable spies] are in use, a call that returns an iterable
will not be considered "complete" until the iterable has been completely
consumed via iteration.*

*See [Verifying call progress], [Check verification].*

<a name="call.generated" />

----

> *[generator-verification][generator-verification-api]* $call->[**generated**](#call.generated)()
> throws [AssertionException]

Throws an exception unless this call returned a generator.

*See [Verifying generators returned by calls],
[Generator and iterable verification].*

<a name="call.checkGenerated" />

----

> *[generator-verification][generator-verification-api]|null* $call->[**checkGenerated**](#call.checkGenerated)()

Checks if this call returned a generator.

*See [Verifying generators returned by calls],
[Generator and iterable verification].*

<a name="call.iterated" />

----

> *[iterable-verification][iterable-verification-api]* $call->[**iterated**](#call.iterated)()
> throws [AssertionException]

Throws an exception unless this call returned an iterable.

*See [Verifying iterables returned by calls],
[Generator and iterable verification].*

<a name="call.checkIterated" />

----

> *[iterable-verification][iterable-verification-api]|null* $call->[**checkIterated**](#call.checkIterated)()

Checks if this call returned an iterable.

*See [Verifying iterables returned by calls],
[Generator and iterable verification].*

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

- [`$verification->received()`](#verification.received)
- [`$verification->checkReceived()`](#verification.checkReceived)
- [`$verification->receivedException()`](#verification.receivedException)
- [`$verification->checkReceivedException()`](#verification.checkReceivedException)
- [`$verification->returned()`](#verification.returned)
- [`$verification->checkReturned()`](#verification.checkReturned)
- [`$verification->threw()`](#verification.threw)
- [`$verification->checkThrew()`](#verification.checkThrew)

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

When [verifying that a spy was called with specific arguments], or
[verifying that a call was made with specific arguments], calls to
[`arguments()`](#call.arguments) produce objects which implement the following
methods:

- [`$arguments->all()`](#arguments.all)
- [`count($arguments)`](#arguments.count)
- [`$arguments->count()`](#arguments.count)
- [`foreach ($arguments as $index => $argument)`](#arguments.implements.Traversable)
- [`$arguments->has()`](#arguments.has)
- [`$arguments->get()`](#arguments.get)
- [`$arguments->set()`](#arguments.set)
- [`$arguments->copy()`](#arguments.copy)

See also:

- [`$call->arguments()`](#call.arguments)
- [`$stub->callsWith()`](#stub.callsWith)
- [`$stub->callsArgumentWith()`](#stub.callsArgumentWith)
- [`$stub->doesWith()`](#stub.doesWith)
- [`$stub->forwards()`](#stub.forwards)
- [`$generatorAnswer->callsWith()`](#generatorAnswer.callsWith)
- [`$generatorAnswer->callsArgumentWith()`](#generatorAnswer.callsArgumentWith)

<a name="arguments.all" />

----

> *array\<mixed>* $arguments->[**all**](#arguments.all)()

Get the arguments as an array.

*Arguments passed by reference will be references in the returned array.*

*See [Verifying that a spy was called with specific arguments],
[Verifying that a call was made with specific arguments].*

<a name="arguments.count" />

----

> [**count($arguments)**](#arguments.count) or
> *int* $arguments->[**count**](#arguments.count)()

Returns the total number of arguments.

*See [Verifying that a spy was called with specific arguments],
[Verifying that a call was made with specific arguments].*

<a name="arguments.implements.Traversable" />

----

> [**foreach ($arguments as $index => $argument)**](#arguments.implements.Traversable) { /* ... */ }

Arguments implement the [Traversable] interface, allowing them to be used in a
`foreach` statement.

*See [Verifying that a spy was called with specific arguments],
[Verifying that a call was made with specific arguments].*

<a name="arguments.has" />

----

> *bool* $arguments->[**has**](#arguments.has)($index = 0)

Returns `true` if an argument exists at `$index`.

*Negative indices are offset from the end of the list. That is, `-1` indicates
the last element, and `-2` indicates the second last element.*

*See [Verifying that a spy was called with specific arguments],
[Verifying that a call was made with specific arguments].*

<a name="arguments.get" />

----

> *mixed* $arguments->[**get**](#arguments.get)($index = 0)
> throws [UndefinedArgumentException]

Get the argument at `$index`.

*Negative indices are offset from the end of the list. That is, `-1` indicates
the last element, and `-2` indicates the second last element.*

*See [Verifying that a spy was called with specific arguments],
[Verifying that a call was made with specific arguments].*

<a name="arguments.set" />

----

> *fluent* $arguments->[**set**](#arguments.set)($indexOrValue = null, $value = null)
> throws [UndefinedArgumentException]

Set an argument by index.

*If called with no arguments, sets the first argument to `null`.*

*If called with one argument, sets the first argument to `$indexOrValue`.*

*If called with two arguments, sets the argument at `$indexOrValue` to
`$value`.*

*See [Verifying that a spy was called with specific arguments],
[Verifying that a call was made with specific arguments].*

<a name="arguments.copy" />

----

> *[arguments][arguments-api]* $arguments->[**copy**](#arguments.copy)()

Copy these arguments, breaking any references.

*See [Verifying that a spy was called with specific arguments],
[Verifying that a call was made with specific arguments].*

### The verification result API

All forms of [verification] produce "verification results" that implement the
following methods:

- [`$verification->hasCalls()`](#verification.hasCalls)
- [`$verification->callCount()`](#verification.callCount)
- [`$verification->allCalls()`](#verification.allCalls)
- [`$verification->firstCall()`](#verification.firstCall)
- [`$verification->lastCall()`](#verification.lastCall)
- [`$verification->callAt()`](#verification.callAt)
- [`$verification->hasEvents()`](#verification.hasEvents)
- [`$verification->eventCount()`](#verification.eventCount)
- [`$verification->allEvents()`](#verification.allEvents)
- [`$verification->firstEvent()`](#verification.firstEvent)
- [`$verification->lastEvent()`](#verification.lastEvent)
- [`$verification->eventAt()`](#verification.eventAt)

See also:

- [`$spy->called()`](#spy.called)
- [`$spy->checkCalled()`](#spy.checkCalled)
- [`$spy->calledWith()`](#spy.calledWith)
- [`$spy->checkCalledWith()`](#spy.checkCalledWith)
- [`$spy->returned()`](#spy.returned)
- [`$spy->checkReturned()`](#spy.checkReturned)
- [`$spy->threw()`](#spy.threw)
- [`$spy->checkThrew()`](#spy.checkThrew)
- [`$spy->responded()`](#spy.responded)
- [`$spy->checkResponded()`](#spy.checkResponded)
- [`$spy->completed()`](#spy.completed)
- [`$spy->checkCompleted()`](#spy.checkCompleted)
- [`$spy->generated()`](#spy.generated)
- [`$spy->checkGenerated()`](#spy.checkGenerated)
- [`$spy->iterated()`](#spy.iterated)
- [`$spy->checkIterated()`](#spy.checkIterated)
- [`$call->calledWith()`](#call.calledWith)
- [`$call->checkCalledWith()`](#call.checkCalledWith)
- [`$call->returned()`](#call.returned)
- [`$call->checkReturned()`](#call.checkReturned)
- [`$call->threw()`](#call.threw)
- [`$call->checkThrew()`](#call.checkThrew)
- [`$call->responded()`](#call.responded)
- [`$call->checkResponded()`](#call.checkResponded)
- [`$call->completed()`](#call.completed)
- [`$call->checkCompleted()`](#call.checkCompleted)
- [`$call->generated()`](#call.generated)
- [`$call->checkGenerated()`](#call.checkGenerated)
- [`$call->iterated()`](#call.iterated)
- [`$call->checkIterated()`](#call.checkIterated)
- [`$verification->used()`](#verification.used)
- [`$verification->checkUsed()`](#verification.checkUsed)
- [`$verification->produced()`](#verification.produced)
- [`$verification->checkProduced()`](#verification.checkProduced)
- [`$verification->consumed()`](#verification.consumed)
- [`$verification->checkConsumed()`](#verification.checkConsumed)
- [`$verification->received()`](#verification.received)
- [`$verification->checkReceived()`](#verification.checkReceived)
- [`$verification->receivedException()`](#verification.receivedException)
- [`$verification->checkReceivedException()`](#verification.checkReceivedException)
- [`$verification->returned()`](#verification.returned)
- [`$verification->checkReturned()`](#verification.checkReturned)
- [`$verification->threw()`](#verification.threw)
- [`$verification->checkThrew()`](#verification.checkThrew)
- [`$handle->noInteraction()`](#handle.noInteraction)
- [`$handle->checkNoInteraction()`](#handle.checkNoInteraction)
- [`inOrder()`](#facade.inOrder)
- [`checkInOrder()`](#facade.checkInOrder)
- [`anyOrder()`](#facade.anyOrder)
- [`checkAnyOrder()`](#facade.checkAnyOrder)

<a name="verification.hasCalls" />

----

> *bool* $verification->[**hasCalls**](#verification.hasCalls)()

Returns `true` if this verification matched any calls.

*See [Call verification].*

<a name="verification.callCount" />

----

> *int* $verification->[**callCount**](#verification.callCount)()

Get the number of calls.

*See [Call count].*

<a name="verification.allCalls" />

----

> *array\<[call][call-api]>* $verification->[**allCalls**](#verification.allCalls)()

Get all calls as an array.

*See [Call verification].*

<a name="verification.firstCall" />

----

> *[call][call-api]* $verification->[**firstCall**](#verification.firstCall)()
> throws [UndefinedCallException]

Get the first call.

*See [Individual calls].*

<a name="verification.lastCall" />

----

> *[call][call-api]* $verification->[**lastCall**](#verification.lastCall)()
> throws [UndefinedCallException]

Get the last call.

*See [Individual calls].*

<a name="verification.callAt" />

----

> *[call][call-api]* $verification->[**callAt**](#verification.callAt)($index = 0)
> throws [UndefinedCallException]

Get the call at `$index`.

*Negative indices are offset from the end of the list. That is, `-1` indicates
the last element, and `-2` indicates the second last element.*

*See [Individual calls].*

<a name="verification.hasEvents" />

----

> *bool* $verification->[**hasEvents**](#verification.hasEvents)()

Returns `true` if this verification matched any events.

*See [Similar events in order verification].*

<a name="verification.eventCount" />

----

> *int* $verification->[**eventCount**](#verification.eventCount)()

Get the number of events.

*See [Similar events in order verification].*

<a name="verification.allEvents" />

----

> *array\<[event][event-api]>* $verification->[**allEvents**](#verification.allEvents)()

Get all events as an array.

*See [Similar events in order verification].*

<a name="verification.firstEvent" />

----

> *[event][event-api]* $verification->[**firstEvent**](#verification.firstEvent)()
> throws [UndefinedEventException]

Get the first event.

*See [Similar events in order verification].*

<a name="verification.lastEvent" />

----

> *[event][event-api]* $verification->[**lastEvent**](#verification.lastEvent)()
> throws [UndefinedEventException]

Get the last event.

*See [Similar events in order verification].*

<a name="verification.eventAt" />

----

> *[event][event-api]* $verification->[**eventAt**](#verification.eventAt)($index = 0)
> throws [UndefinedEventException]

Get the event at `$index`.

*Negative indices are offset from the end of the list. That is, `-1` indicates
the last element, and `-2` indicates the second last element.*

*See [Similar events in order verification].*

### The iterable verification result API

In addition to [the verification result API], iterable verification results
implement the following methods:

- [`$verification->used()`](#verification.used)
- [`$verification->checkUsed()`](#verification.checkUsed)
- [`$verification->produced()`](#verification.produced)
- [`$verification->checkProduced()`](#verification.checkProduced)
- [`$verification->consumed()`](#verification.consumed)
- [`$verification->checkConsumed()`](#verification.checkConsumed)
- [`$verification->never()`](#verification.never)
- [`$verification->once()`](#verification.once)
- [`$verification->twice()`](#verification.twice)
- [`$verification->thrice()`](#verification.thrice)
- [`$verification->times()`](#verification.times)
- [`$verification->atLeast()`](#verification.atLeast)
- [`$verification->atMost()`](#verification.atMost)
- [`$verification->between()`](#verification.between)
- [`$verification->always()`](#verification.always)

See also:

- [`$spy->iterated()`](#spy.iterated)
- [`$spy->checkIterated()`](#spy.checkIterated)
- [`$call->iterated()`](#call.iterated)
- [`$call->checkIterated()`](#call.checkIterated)

<a name="verification.used" />

----

> *[verification][verification-api]* $verification->[**used**](#verification.used)()
> throws [AssertionException]

Throws an exception unless iteration of the iterable commenced.

*See [Verifying iteration].*

<a name="verification.checkUsed" />

----

> *[verification][verification-api]|null* $verification->[**checkUsed**](#verification.checkUsed)()

Checks if iteration of the iterable commenced.

*See [Verifying iteration], [Check verification].*

<a name="verification.produced" />

----

> *[verification][verification-api]* $verification->[**produced**](#verification.produced)($keyOrValue = null, $value = null)
> throws [AssertionException]

Throws an exception unless the iterable produced the supplied values.

*When called with no arguments, this method simply checks that the iterable
produced any value.*

*With a single argument, it checks that a value matching the argument was
produced.*

*With two arguments, it checks that a key and value matching the respective
arguments were produced together.*

*This method supports [mock handle substitution] and
[iterable spy substitution].*

*See [Verifying produced values].*

<a name="verification.checkProduced" />

----

> *[verification][verification-api]|null* $verification->[**checkProduced**](#verification.checkProduced)($keyOrValue = null, $value = null)

Checks if the iterable produced the supplied values.

*When called with no arguments, this method simply checks that the iterable
produced any value.*

*With a single argument, it checks that a value matching the argument was
produced.*

*With two arguments, it checks that a key and value matching the respective
arguments were produced together.*

*This method supports [mock handle substitution] and
[iterable spy substitution].*

*See [Verifying produced values], [Check verification].*

<a name="verification.consumed" />

----

> *[verification][verification-api]* $verification->[**consumed**](#verification.consumed)()
> throws [AssertionException]

Throws an exception unless iteration of the iterable completed.

*See [Verifying iteration].*

<a name="verification.checkConsumed" />

----

> *[verification][verification-api]|null* $verification->[**checkConsumed**](#verification.checkConsumed)()

Checks if iteration of the iterable completed.

*See [Verifying iteration], [Check verification].*

<a name="verification.never" />

----

> *fluent* $verification->[**never**](#verification.never)()

Requires that the next verification never matches.

*See [Verifying cardinality with generators and iterables],
[Verifying that a spy event happened an exact number of times],
[Verifying that a call event happened an exact number of times].*

<a name="verification.once" />

----

> *fluent* $verification->[**once**](#verification.once)()

Requires that the next verification matches only once.

*See [Verifying cardinality with generators and iterables],
[Verifying that a spy event happened an exact number of times],
[Verifying that a call event happened an exact number of times].*

<a name="verification.twice" />

----

> *fluent* $verification->[**twice**](#verification.twice)()

Requires that the next verification matches exactly two times.

*See [Verifying cardinality with generators and iterables],
[Verifying that a spy event happened an exact number of times],
[Verifying that a call event happened an exact number of times].*

<a name="verification.thrice" />

----

> *fluent* $verification->[**thrice**](#verification.thrice)()

Requires that the next verification matches exactly three times.

*See [Verifying cardinality with generators and iterables],
[Verifying that a spy event happened an exact number of times],
[Verifying that a call event happened an exact number of times].*

<a name="verification.times" />

----

> *fluent* $verification->[**times**](#verification.times)($times)

Requires that the next verification matches exactly `$times` times.

*See [Verifying cardinality with generators and iterables],
[Verifying that a spy event happened an exact number of times],
[Verifying that a call event happened an exact number of times].*

<a name="verification.atLeast" />

----

> *fluent* $verification->[**atLeast**](#verification.atLeast)($minimum)

Requires that the next verification matches a number of times greater than or
equal to `$minimum`.

*See [Verifying cardinality with generators and iterables],
[Verifying that a spy event happened a bounded number of times],
[Verifying that a call event happened a bounded number of times].*

<a name="verification.atMost" />

----

> *fluent* $verification->[**atMost**](#verification.atMost)($maximum)

Requires that the next verification matches a number of times less than or equal
to `$maximum`.

*See [Verifying cardinality with generators and iterables],
[Verifying that a spy event happened a bounded number of times],
[Verifying that a call event happened a bounded number of times].*

<a name="verification.between" />

----

> *fluent* $verification->[**between**](#verification.between)($minimum, $maximum)

Requires that the next verification matches a number of times greater than or
equal to `$minimum`, and less than or equal to `$maximum`.

*See [Verifying cardinality with generators and iterables],
[Verifying that a spy event happened a bounded number of times],
[Verifying that a call event happened a bounded number of times].*

<a name="verification.always" />

----

> *fluent* $verification->[**always**](#verification.always)()

Requires that the next verification matches for all possible items.

*See [Verifying cardinality with generators and iterables],
[Verifying that all spy events happen the same way],
[Verifying that all call events happen the same way].*

### The generator verification result API

In addition to [the verification result API] and
[the iterable verification result API], generator verification results implement
the following methods:

- [`$verification->received()`](#verification.received)
- [`$verification->checkReceived()`](#verification.checkReceived)
- [`$verification->receivedException()`](#verification.receivedException)
- [`$verification->checkReceivedException()`](#verification.checkReceivedException)
- [`$verification->returned()`](#verification.returned)
- [`$verification->checkReturned()`](#verification.checkReturned)
- [`$verification->threw()`](#verification.threw)
- [`$verification->checkThrew()`](#verification.checkThrew)

See also:

- [`$spy->generated()`](#spy.generated)
- [`$spy->checkGenerated()`](#spy.checkGenerated)
- [`$call->generated()`](#call.generated)
- [`$call->checkGenerated()`](#call.checkGenerated)

<a name="verification.received" />

----

> *[verification][verification-api]* $verification->[**received**](#verification.received)($value = null)
> throws [AssertionException]

Throws an exception unless the generator received the supplied value.

*When called with no arguments, this method simply checks that the generator
received any value.*

*This method supports [mock handle substitution] and
[iterable spy substitution].*

*See [Verifying values received by generators].*

<a name="verification.checkReceived" />

----

> *[verification][verification-api]|null* $verification->[**checkReceived**](#verification.checkReceived)($value = null)

Checks if the generator received the supplied value.

*When called with no arguments, this method simply checks that the generator
received any value.*

*This method supports [mock handle substitution] and
[iterable spy substitution].*

*See [Verifying values received by generators], [Check verification].*

<a name="verification.receivedException" />

----

> *[verification][verification-api]* $verification->[**receivedException**](#verification.receivedException)($type = null)
> throws [AssertionException]

Throws an exception unless the generator received an exception of the supplied
type.

*When called with no arguments, this method simply checks that the generator
received any exception.*

*When called with a string, this method checks that the generator received an
exception that is an instance of `$type`.*

*When called with an exception instance, this method checks that the generator
received an exception that is equal to the supplied instance.*

*When called with a [matcher], this method checks that the generator received an
exception that matches the supplied matcher.*

*This method supports [mock handle substitution] and
[iterable spy substitution].*

*See [Verifying exceptions received by generators].*

<a name="verification.checkReceivedException" />

----

> *[verification][verification-api]|null* $verification->[**checkReceivedException**](#verification.checkReceivedException)($type = null)

Checks if the generator received an exception of the supplied type.

*When called with no arguments, this method simply checks that the generator
received any exception.*

*When called with a string, this method checks that the generator received an
exception that is an instance of `$type`.*

*When called with an exception instance, this method checks that the generator
received an exception that is equal to the supplied instance.*

*When called with a [matcher], this method checks that the generator received an
exception that matches the supplied matcher.*

*This method supports [mock handle substitution] and
[iterable spy substitution].*

*See [Verifying exceptions received by generators], [Check verification].*

<a name="verification.returned" />

----

> *[verification][verification-api]* $verification->[**returned**](#verification.returned)($value = null)
> throws [AssertionException]

Throws an exception unless the generator returned the supplied value.

*When called with no arguments, this method simply checks that the generator
returned any value.*

*This method supports [mock handle substitution] and
[iterable spy substitution].*

*See [Verifying generator return values].*

<a name="verification.checkReturned" />

----

> *[verification][verification-api]|null* $verification->[**checkReturned**](#verification.checkReturned)($value = null)

Checks if the generator returned the supplied value.

*When called with no arguments, this method simply checks that the generator
returned any value.*

*This method supports [mock handle substitution] and
[iterable spy substitution].*

*See [Verifying generator return values], [Check verification].*

<a name="verification.threw" />

----

> *[verification][verification-api]* $verification->[**threw**](#verification.threw)($type = null)
> throws [AssertionException]

Throws an exception unless this generator threw an exception of the supplied
type.

*When called with no arguments, this method simply checks that the generator
threw any exception.*

*When called with a string, this method checks that the generator threw an
exception that is an instance of `$type`.*

*When called with an exception instance, this method checks that the generator
threw an exception that is equal to the supplied instance.*

*When called with a [matcher], this method checks that the generator threw an
exception that matches the supplied matcher.*

*This method supports [mock handle substitution] and
[iterable spy substitution].*

*See [Verifying generator exceptions].*

<a name="verification.checkThrew" />

----

> *[verification][verification-api]|null* $verification->[**checkThrew**](#verification.checkThrew)($type = null)

Checks if this generator threw an exception of the supplied type.

*When called with no arguments, this method simply checks that the generator
threw any exception.*

*When called with a string, this method checks that the generator threw an
exception that is an instance of `$type`.*

*When called with an exception instance, this method checks that the generator
threw an exception that is equal to the supplied instance.*

*When called with a [matcher], this method checks that the generator threw an
exception that matches the supplied matcher.*

*This method supports [mock handle substitution] and
[iterable spy substitution].*

*See [Verifying generator exceptions], [Check verification].*

### The event API

When events are exposed from an API, they implement the following methods:

- [`$event->time()`](#event.time)
- [`$event->sequenceNumber()`](#event.sequenceNumber)

See also:

- [`$spy->allEvents()`](#spy.allEvents)
- [`$spy->firstEvent()`](#spy.firstEvent)
- [`$spy->lastEvent()`](#spy.lastEvent)
- [`$spy->eventAt()`](#spy.eventAt)
- [`$verification->allEvents()`](#verification.allEvents)
- [`$verification->firstEvent()`](#verification.firstEvent)
- [`$verification->lastEvent()`](#verification.lastEvent)
- [`$verification->eventAt()`](#verification.eventAt)

<a name="event.time" />

----

> *float* $event->[**time**](#event.time)()

Get the time at which the event occurred, in seconds since the Unix epoch.

<a name="event.sequenceNumber" />

----

> *int* $event->[**sequenceNumber**](#event.sequenceNumber)()

Get the sequence number.

*The sequence number is a unique number assigned to every event that Phony
records. The numbers are assigned sequentially, meaning that sequence numbers
can be used to determine event order.*

### The matcher API

[Matchers] implement the following methods:

- [`$matcher->matches()`](#matcher.matches)
- [`"$matcher"`](#matcher.__toString)
- [`$matcher->describe()`](#matcher.describe)

See also:

- [`any()`](#facade.any)
- [`equalTo()`](#facade.equalTo)
- [`anInstanceOf()`](#facade.anInstanceOf)
- [`$wildcard->matcher()`](#wildcard.matcher)

<a name="matcher.matches" />

----

> *bool* $matcher->[**matches**](#matcher.matches)($value)

Returns `true` if `$value` matches this matcher's criteria.

*See [Matchers].*

<a name="matcher.__toString" />
<a name="matcher.describe" />

----

> [**"$matcher"**](#matcher.__toString) or
> *string* $matcher->[**describe**](#matcher.describe)()

Describe this matcher.

*See [Matchers].*

### The wildcard matcher API

The ["wildcard" matcher] implements the following methods:

- [`$wildcard->matcher()`](#wildcard.matcher)
- [`$wildcard->minimumArguments()`](#wildcard.minimumArguments)
- [`$wildcard->maximumArguments()`](#wildcard.maximumArguments)
- [`"$wildcard"`](#wildcard.__toString)
- [`$wildcard->describe()`](#wildcard.describe)

See also:

- [`wildcard()`](#facade.wildcard)

<a name="wildcard.matcher" />

----

> *[matcher][matcher-api]* $wildcard->[**matcher**](#wildcard.matcher)()

Get the matcher to use for each argument.

*See [The "wildcard" matcher].*

<a name="wildcard.minimumArguments" />

----

> *int* $wildcard->[**minimumArguments**](#wildcard.minimumArguments)()

Get the minimum number of arguments to match.

*See [The "wildcard" matcher].*

<a name="wildcard.maximumArguments" />

----

> *int* $wildcard->[**maximumArguments**](#wildcard.maximumArguments)()

Get the maximum number of arguments to match.

*Negative values are used to represent "no maximum".*

*See [The "wildcard" matcher].*

<a name="wildcard.__toString" />
<a name="wildcard.describe" />

----

> [**"$wildcard"**](#wildcard.__toString) or
> *string* $wildcard->[**describe**](#wildcard.describe)()

Describe this matcher.

*See [The "wildcard" matcher].*

### Thrown exceptions

#### AssertionException

Thrown when a verification fails. The exact exception class and implementation
depends on the [testing framework] in use. See [Standard verification].

Other than the standard PHP [Exception] methods, assertion exceptions have no
public API methods.

#### UndefinedArgumentException

Thrown when an argument that was requested does not exist.

Namespace: `Eloquent\Phony\Call\Exception`

<a name="undefinedargumentexception.index" />

----

> *int* $exception->[**index**](#undefinedargumentexception.index)()

Get the index.

#### UndefinedCallException

Thrown when a call that was requested does not exist.

Namespace: `Eloquent\Phony\Call\Exception`

<a name="undefinedcallexception.index" />

----

> *int* $exception->[**index**](#undefinedcallexception.index)()

Get the index.

#### UndefinedEventException

Thrown when an event that was requested does not exist.

Namespace: `Eloquent\Phony\Event\Exception`

<a name="undefinedeventexception.index" />

----

> *int* $exception->[**index**](#undefinedeventexception.index)()

Get the index.

#### UndefinedResponseException

Thrown when the call has not yet produced a response of the requested type.

This can occur when an individual call is queried for its response details
before the call has returned a value, or thrown an exception.

Other than the standard PHP [Exception] methods, undefined response exceptions
have no public API methods.

## License

For the full copyright and license information, please view the [LICENSE file].

<!-- Heading references -->

[accessing non-public methods and properties]: #accessing-non-public-methods-and-properties
[actual behavior output]: #actual-behavior-output
[ad hoc definition magic "self" values]: #ad-hoc-definition-magic-self-values
[ad hoc definition values]: #ad-hoc-definition-values
[ad hoc mocks]: #ad-hoc-mocks
[alternatives for spying on global functions]: #alternatives-for-spying-on-global-functions
[alternatives for stubbing global functions]: #alternatives-for-stubbing-global-functions
[anonymous spies]: #anonymous-spies
[anonymous stubs]: #anonymous-stubs
[answers that perform multiple actions]: #answers-that-perform-multiple-actions
[assertionexception]: #assertionexception
[call count]: #call-count
[call verification]: #call-verification
[calling a constructor manually]: #calling-a-constructor-manually
[calls]: #calls
[cardinality output]: #cardinality-output
[check verification]: #check-verification
[comparing exceptions]: #comparing-exceptions
[comparing mocks]: #comparing-mocks
[copying mock builders]: #copying-mock-builders
[creating mocks from a builder]: #creating-mocks-from-a-builder
[customizing the mock class]: #customizing-the-mock-class
[default values for return types]: #default-values-for-return-types
[dynamic order verification]: #dynamic-order-verification
[example test suites]: #example-test-suites
[expected behavior output]: #expected-behavior-output
[export depth]: #export-depth
[export identifier persistence]: #export-identifier-persistence
[export identifiers and references]: #export-identifiers-and-references
[export reference exclusions]: #export-reference-exclusions
[export reference types]: #export-reference-types
[exporter special cases]: #exporter-special-cases
[exporting closures]: #exporting-closures
[exporting exceptions]: #exporting-exceptions
[exporting mocks]: #exporting-mocks
[exporting recursive values]: #exporting-recursive-values
[exporting spies]: #exporting-spies
[exporting stubs]: #exporting-stubs
[forwarding to the original callable]: #forwarding-to-the-original-callable
[generating mock classes from a builder]: #generating-mock-classes-from-a-builder
[generator and iterable verification]: #generator-and-iterable-verification
[generator iterations that perform multiple actions]: #generator-iterations-that-perform-multiple-actions
[hamcrest matchers]: #hamcrest-matchers
[help]: #help
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
[iterable spy substitution]: #iterable-spy-substitution
[iterable verification caveats]: #iterable-verification-caveats
[kahlan usage]: #kahlan-usage
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
[mocking and non-public methods]: #mocking-and-non-public-methods
[mocking basics]: #mocking-basics
[mocking multiple types]: #mocking-multiple-types
[mocking problematic classes]: #mocking-problematic-classes
[mocks]: #mocks
[multiple answers]: #multiple-answers
[multiple rules]: #multiple-rules
[nested iterable spies]: #nested-iterable-spies
[order verification caveats]: #order-verification-caveats
[order verification]: #order-verification
[overriding rules]: #overriding-rules
[partial mocks]: #partial-mocks
[pausing mock recording]: #pausing-mock-recording
[pausing spy recording]: #pausing-spy-recording
[peridot usage]: #peridot-usage
[pho usage]: #pho-usage
[phpunit constraints]: #phpunit-constraints
[phpunit usage]: #phpunit-usage
[proxy mocks]: #proxy-mocks
[repeated iteration of iterable spies]: #repeated-iteration-of-iterable-spies
[restoring global functions after spying]: #restoring-global-functions-after-spying
[restoring global functions after stubbing]: #restoring-global-functions-after-stubbing
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
[spying on global functions]: #spying-on-global-functions
[spying on iterables that implement array-like interfaces]: #spying-on-iterables-that-implement-array-like-interfaces
[standalone usage]: #standalone-usage
[standard verification]: #standard-verification
[static mocks]: #static-mocks
[stub "self" values]: #stub-self-values
[stub rules and answers]: #stub-rules-and-answers
[stubbing an existing callable]: #stubbing-an-existing-callable
[stubbing generators]: #stubbing-generators
[stubbing global functions]: #stubbing-global-functions
[stubs]: #stubs
[terminology]: #terminology
[the "any" matcher]: #the-any-matcher
[the "equal to" matcher]: #the-equal-to-matcher
[the "instance of" matcher]: #the-instance-of-matcher
[the "wildcard" matcher]: #the-wildcard-matcher
[the api]: #the-api
[the arguments api]: #the-arguments-api
[the call api]: #the-call-api
[the default answer callback]: #the-default-answer-callback
[the default rule and answer]: #the-default-rule-and-answer
[the event api]: #the-event-api
[the export format]: #the-export-format
[the exporter]: #the-exporter
[the generator answer api]: #the-generator-answer-api
[the generator verification result api]: #the-generator-verification-result-api
[the iterable verification result api]: #the-iterable-verification-result-api
[the matcher api]: #the-matcher-api
[the mock builder api]: #the-mock-builder-api
[the mock handle api]: #the-mock-handle-api
[the spy api]: #the-spy-api
[the stub api]: #the-stub-api
[the top-level api]: #the-top-level-api
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
[understanding verification output]: #understanding-verification-output
[usage]: #usage
[using a callable as an answer]: #using-a-callable-as-an-answer
[using colored verification output]: #using-colored-verification-output
[using iterable spies changes the return value]: #using-iterable-spies-changes-the-return-value
[using this documentation]: #using-this-documentation
[verification]: #verification
[verifying call exceptions]: #verifying-call-exceptions
[verifying call input]: #verifying-call-input
[verifying call output]: #verifying-call-output
[verifying call progress]: #verifying-call-progress
[verifying call return values]: #verifying-call-return-values
[verifying cardinality with calls]: #verifying-cardinality-with-calls
[verifying cardinality with generators and iterables]: #verifying-cardinality-with-generators-and-iterables
[verifying cardinality with spies]: #verifying-cardinality-with-spies
[verifying exceptions received by calls]: #verifying-exceptions-received-by-calls
[verifying exceptions received by generators]: #verifying-exceptions-received-by-generators
[verifying exceptions received by spies]: #verifying-exceptions-received-by-spies
[verifying generator exceptions]: #verifying-generator-exceptions
[verifying generator return values]: #verifying-generator-return-values
[verifying generators returned by calls]: #verifying-generators-returned-by-calls
[verifying generators returned by spies]: #verifying-generators-returned-by-spies
[verifying iterables returned by calls]: #verifying-iterables-returned-by-calls
[verifying iterables returned by spies]: #verifying-iterables-returned-by-spies
[verifying iteration]: #verifying-iteration
[verifying produced values]: #verifying-produced-values
[verifying spy exceptions]: #verifying-spy-exceptions
[verifying spy input]: #verifying-spy-input
[verifying spy output]: #verifying-spy-output
[verifying spy progress]: #verifying-spy-progress
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
[verifying values received by generators]: #verifying-values-received-by-generators
[verifying values received by spies]: #verifying-values-received-by-spies
[when to use the "equal to" matcher]: #when-to-use-the-equal-to-matcher
[yielding from a generator]: #yielding-from-a-generator
[yielding individual values from a generator]: #yielding-individual-values-from-a-generator
[yielding multiple values from a generator]: #yielding-multiple-values-from-a-generator

<!-- Shortcut references -->

["any" matcher]: #the-any-matcher
["equal to" matcher]: #the-equal-to-matcher
["instance of" matcher]: #the-instance-of-matcher
["wildcard" matcher]: #the-wildcard-matcher
[ad hoc mock]: #ad-hoc-mocks
[api]: #api
[default answer callback]: #the-default-answer-callback
[full mock]: #mocking-basics
[generator answer]: #the-generator-answer-api
[generator spies]: #generator-and-iterable-verification
[generator verification result]: #the-generator-verification-result-api
[imported]: #importing
[individual call]: #individual-calls
[iterable spies]: #generator-and-iterable-verification
[iterable verification result]: #the-iterable-verification-result-api
[matcher]: #matchers
[mock builder]: #mock-builders
[mock handle]: #mock-handles
[mock]: #mocks
[partial mock]: #partial-mocks
[references]: #export-identifiers-and-references
[self value]: #stub-self-values
[spy label]: #labeling-spies
[spy]: #spies
[static handle]: #static-mocks
[static mock handles]: #static-mocks
[stub]: #stubs
[stubbing]: #stubs
[testing framework]: #integration-with-test-frameworks
[testing frameworks]: #integration-with-test-frameworks
[verification result]: #the-verification-result-api
[verification results]: #the-verification-result-api
[which composer package]: #integration-with-test-frameworks

<!-- API references -->

[arguments-api]: #the-arguments-api
[call-api]: #the-call-api
[event-api]: #the-event-api
[generator-answer-api]: #the-generator-answer-api
[generator-verification-api]: #the-generator-verification-result-api
[handle-api]: #the-mock-handle-api
[iterable-verification-api]: #the-iterable-verification-result-api
[matcher-api]: #the-matcher-api
[mock-builder-api]: #the-mock-builder-api
[spy-api]: #the-spy-api
[stub-api]: #the-stub-api
[verification-api]: #the-verification-result-api
[wildcard-api]: #the-wildcard-matcher-api

<!-- Image references -->

[call-called-with-image]: img/verification/called-with/call-argument-mismatch.svg
[call-completed-image]: img/verification/completed/call-none-completed.svg
[call-generated-image]: img/verification/generated/call-no-generator.svg
[call-iterated-image]: img/verification/iterated/call-no-iterable.svg
[call-responded-image]: img/verification/responded/call-no-responses.svg
[call-returned-image]: img/verification/returned/call-value-mismatch.svg
[call-threw-image]: img/verification/threw/call-value-mismatch.svg
[facade-in-order-image]: img/verification/in-order/unexpected-order.svg
[handle-no-interaction-image]: img/verification/no-interaction/parent-class.svg
[mock-label-image]: img/verification/called-with/mock-label.svg
[spy-called-image]: img/verification/called/no-calls.svg
[spy-called-with-image]: img/verification/called-with/argument-mismatch.svg
[spy-completed-image]: img/verification/completed/none-completed.svg
[spy-generated-image]: img/verification/generated/no-generator.svg
[spy-iterated-image]: img/verification/iterated/no-iterable.svg
[spy-label-image]: img/verification/called-with/spy-label.svg
[spy-responded-image]: img/verification/responded/no-responses.svg
[spy-returned-image]: img/verification/returned/value-mismatch.svg
[spy-threw-image]: img/verification/threw/value-mismatch.svg
[verification-consumed-image]: img/verification/consumed/iterated-unconsumed.svg
[verification-image]: img/verification.svg
[verification-produced-image]: img/verification/produced/iterated-key-value-mismatch.svg
[verification-received-exception-image]: img/verification/received-exception/value-mismatch.svg
[verification-received-image]: img/verification/received/value-mismatch.svg
[verification-returned-image]: img/verification/generated-returned/value-mismatch.svg
[verification-threw-image]: img/verification/generated-threw/value-mismatch.svg
[verification-used-image]: img/verification/used/iterated-unused.svg

<!-- External references -->

[@ezzatron]: https://github.com/ezzatron
[`__invoke()`]: http://php.net/language.oop5.magic#object.invoke
[`rewind()`]: http://php.net/iterator.rewind
[ansi colored output]: https://en.wikipedia.org/wiki/ANSI_escape_code#Colors
[arrayaccess]: http://php.net/arrayaccess
[code smell]: https://en.wikipedia.org/wiki/Code_smell
[composer]: http://getcomposer.org/
[countable]: http://php.net/countable
[eloquent/phony-kahlan]: https://packagist.org/packages/eloquent/phony-kahlan
[eloquent/phony-peridot]: https://packagist.org/packages/eloquent/phony-peridot
[eloquent/phony-pho]: https://packagist.org/packages/eloquent/phony-pho
[eloquent/phony-phpunit]: https://packagist.org/packages/eloquent/phony-phpunit
[eloquent/phony-simpletest]: https://packagist.org/packages/eloquent/phony-simpletest
[eloquent/phony]: https://packagist.org/packages/eloquent/phony
[error]: http://php.net/class.error
[exception]: http://php.net/class.exception
[fluent interfaces]: http://en.wikipedia.org/wiki/Fluent_interface
[generator]: http://php.net/language.generators.overview
[generators]: http://php.net/language.generators.overview
[github issue]: https://github.com/eloquent/phony/issues
[global function fallback]: http://php.net/language.namespaces.fallback
[hamcrest]: https://github.com/hamcrest/hamcrest-php
[isolator]: https://github.com/IcecaveStudios/isolator
[kahlan]: https://kahlan.github.io/docs/
[kahlan argument matchers]: https://kahlan.github.io/docs/matchers.html#argument
[liberator]: https://github.com/eloquent/liberator
[license file]: https://github.com/eloquent/phony/blob/HEAD/LICENSE
[peridot]: http://peridot-php.github.io/
[pho]: https://github.com/danielstjules/pho
[phony-examples]: https://github.com/eloquent/phony-examples
[phpunit matchers]: https://phpunit.de/manual/current/en/appendixes.assertions.html#appendixes.assertions.assertThat
[phpunit]: https://phpunit.de/
[reflection]: http://php.net/reflection
[reflectionclass]: http://php.net/reflectionclass
[reflectiontype]: http://php.net/reflectiontype
[return type]: http://php.net/functions.returning-values#functions.returning-values.type-declaration
[simpletest matchers]: http://www.simpletest.org/en/expectation_documentation.html
[simpletest]: https://github.com/simpletest/simpletest
[throwable]: http://php.net/class.throwable
[traversable]: http://php.net/traversable
[twitter]: https://twitter.com/ezzatron
[use function]: http://php.net/language.namespaces.importing
[visibility]: http://php.net/language.oop5.visibility
[wiki-mocking-problematic-classes]: https://github.com/eloquent/phony/wiki/Mocking-problematic-classes
[yield]: http://php.net/language.generators.syntax#control-structures.yield
