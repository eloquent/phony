# Mocks and handles

Hello friends! In this video, I'm going to talk a bit about mock objects in
Phony; how to create them, and how to use them in your tests.

I'm also briefly going to touch on the differences and similarities between
Phony, and other popular mocking frameworks.

A quick warning before we go any further; this video is going to assume that
you're already familiar with the concept of mocks, and other test doubles, and
is primarily aimed at those looking to make the switch from other mocking
frameworks to Phony.

I *am* planning to produce some more beginner-focused videos in the future, so
check the description for links to those, and other useful resources.

All right, let's get started.

## Creating mocks

### Importing Phony

> (open example)

```php
<?php

class PhonyTest extends PHPUnit_Framework_TestCase
{
    public function testExample()
    {
    }
}
```

Creating a mock object in Phony is actually a very simple process. All we need
to do is call Phony's `mock()` function. But first, we need to import the
`mock()` function itself.

There are actually *three* ways we can go about this, and which one to use
depends mostly on the version of PHP available, and to a lesser extent, it also
depends on your personal preference.

#### Importing a static class

Perhaps the way that's going to be the most familiar to many people, is to bring
in the `Phony` class, and to call `mock()` *statically*. Let's try it that way
first:

> (typing)

```php
<?php

class PhonyTest extends PHPUnit_Framework_TestCase
{
    public function testExample()
    {
        $mock = Phony::mock();
    }
}
```

Now I need to actually import the `Phony` class. In my editor, I have a tool
that can automatically generate the `use` statement for me, so I'm just going to
use that to save some time.

> (typing)

You'll see that there are actually a few different classes named `Phony`
available in different namespaces. If you look at the namespaces themselves,
you'll see that they're named after different testing frameworks.

In most cases, this option here:

> (hover over with mouse)

...in the main Phony namespace will work just fine. However, some testing
frameworks, such as PHPUnit (which we're using today), have more complex
integration needs.

For example, PHPUnit will complain if you don't make any assertions in a test,
so Phony needs to communicate its success results back to PHPUnit in order for
everything to work smoothly.

Luckily, rather than relying on bootstrapping code or configuration, integrating
with the testing framework in Phony is as simple as selecting the correct
namespace. All of these classes:

> (hover over with mouse)

Have exactly the same methods. The only thing it changes is which testing
framework Phony will attempt to integrate with. So since we're using PHPUnit,
let's select this option from the `Phpunit` sub-namespace:

> (click)

```php
<?php

use Eloquent\Phony\Phpunit\Phony;

class PhonyTest extends PHPUnit_Framework_TestCase
{
    public function testExample()
    {
        $mock = Phony::mock();
    }
}
```

And let's write a little assertion to make sure we got something back from
Phony:

> (typing)

```php
<?php

use Eloquent\Phony\Phpunit\Phony;

class PhonyTest extends PHPUnit_Framework_TestCase
{
    public function testExample()
    {
        $mock = Phony::mock();

        $this->assertTrue(is_object($mock));
    }
}
```

I'll just quickly switch to the terminal, and run that...

> (run in terminal)

Okay, that looks fine, so let's switch back to the editor and try out the other
importing options I mentioned earlier.

#### Importing a namespace

Now, the `Phony` class we've imported here is really nothing more than a
collection of static methods; there's no real reason for it to even be a class,
other than the fact that, historically, that's how PHP libraries have exposed
their top-level interfaces.

So the second option for importing the `mock()` function is to take a bit more
of a modern approach, and to use a namespaced function instead. All of the
static methods on a given `Phony` class are also available as functions in the
same namespace.

To take advantage of that, I'm going to remove the last part of this `use`
statement:

> (typing)

```php
<?php

use Eloquent\Phony\Phpunit;

class PhonyTest extends PHPUnit_Framework_TestCase
{
    public function testExample()
    {
        $mock = Phony::mock();

        $this->assertTrue(is_object($mock));
    }
}
```

And now I can refer to the `mock()` function by using the *new* last part of the
statement, followed by a namespace separator, and finally the function name:

> (typing)

```php
<?php

use Eloquent\Phony\Phpunit;

class PhonyTest extends PHPUnit_Framework_TestCase
{
    public function testExample()
    {
        $mock = Phpunit\mock();

        $this->assertTrue(is_object($mock));
    }
}
```

Also, let's add an alias to our `use` statement so that we're not getting
confused by the fact that it says `Phpunit` right before the `mock()` function.
You could use `Phony` for your alias:

> (typing)

```php
<?php

use Eloquent\Phony\Phpunit as Phony;

class PhonyTest extends PHPUnit_Framework_TestCase
{
    public function testExample()
    {
        $mock = Phony\mock();

        $this->assertTrue(is_object($mock));
    }
}
```

But I actually prefer to keep it nice and short and just use `x`, like so:

> (typing)

```php
<?php

use Eloquent\Phony\Phpunit as x;

class PhonyTest extends PHPUnit_Framework_TestCase
{
    public function testExample()
    {
        $mock = x\mock();

        $this->assertTrue(is_object($mock));
    }
}
```

That's going to be a matter of preference. I understand if you hate it, but
that's just how I prefer it.

Now, I'm guessing some people out there might be scratching their heads right
now, because I just used a `use` statement in a way they haven't seen before. If
you're one of those people, I apologize.

Basically, it boils down to the fact that in addition to regular old classes,
you can import *namespaces themselves* with use statements, and that's what I
just did there. In fact, to prove that it works, I'll just run the tests again
quickly:

> (run in terminal)

See? Alles gut, ja?

So that's basically your best option for importing Phony if you need to work
with PHP versions older than 5.6. However, if you *can* take advantage of the
`use function`  statement available from PHP 5.6 onward, then you can simplify
things even further.

#### Importing an individual function

When we have access to the `use function` statement, it allows us to import the
`mock()` function directly from the appropriate namespace. This can make for
very clean, easy to read code, so let's give it a go.

Firstly, we need to change this use statement to refer to the function we want
to import:

> (typing)

```php
<?php

use function Eloquent\Phony\Phpunit\mock;

class PhonyTest extends PHPUnit_Framework_TestCase
{
    public function testExample()
    {
        $mock = x\mock();

        $this->assertTrue(is_object($mock));
    }
}
```

And now we can ditch the namespace reference from the `mock()` function:

> (typing)

```php
<?php

use function Eloquent\Phony\Phpunit\mock;

class PhonyTest extends PHPUnit_Framework_TestCase
{
    public function testExample()
    {
        $mock = mock();

        $this->assertTrue(is_object($mock));
    }
}
```

And we're done. Simple as that. Let's run the tests once more for good measure:

> (run in terminal)

And everything's still working, as you can see.

This last option is definitely my favorite when technical constraints allow for
it, and I recommend that *you* use it, when you can, as well.

### Mocking basics

Now, let's make this example a little bit more realistic.

There's not much point in a mock unless it has the same interface as another
type, right? Let's change this call to `mock()`, and let's create a mock of a
PSR logger:

> (typing)

```php
<?php

use Psr\Log\LoggerInterface;
use function Eloquent\Phony\Phpunit\mock;

class PhonyTest extends PHPUnit_Framework_TestCase
{
    public function testExample()
    {
        $mock = mock(LoggerInterface::class);

        $this->assertTrue(is_object($mock));
    }
}
```

If you haven't seen this `class` constant before, basically it's just equivalent
to the fully-qualified class name of `LoggerInterface`. That is, if you typed
out the full class name as a string:

> (typing)

```php
<?php

use Psr\Log\LoggerInterface;
use function Eloquent\Phony\Phpunit\mock;

class PhonyTest extends PHPUnit_Framework_TestCase
{
    public function testExample()
    {
        $mock = mock('Psr\Log\LoggerInterface');

        $this->assertTrue(is_object($mock));
    }
}
```

This would do exactly the same thing. But if you're working with PHP 5.5 or
above, this way is a bit nicer, especially when dealing with namespaced types
like the PSR `LoggerInterface`:

> (typing)

```php
<?php

use Psr\Log\LoggerInterface;
use function Eloquent\Phony\Phpunit\mock;

class PhonyTest extends PHPUnit_Framework_TestCase
{
    public function testExample()
    {
        $mock = mock(LoggerInterface::class);

        $this->assertTrue(is_object($mock));
    }
}
```

Okay, so now we've got a more useful mock, at least in theory, right? Well,
first let's check that we've actually got what we asked for. Let's change this
assertion just a little, so that it's actually checking that our mock is a
logger:

> (typing)

```php
<?php

use Psr\Log\LoggerInterface;
use function Eloquent\Phony\Phpunit\mock;

class PhonyTest extends PHPUnit_Framework_TestCase
{
    public function testExample()
    {
        $mock = mock(LoggerInterface::class);

        $this->assertTrue($mock instanceof LoggerInterface);
    }
}
```

And let's run the tests to try it out:

> (run in terminal)

Hmm, that didn't so well. Our test is failing, so that tells us that what we're
getting back from this call to `mock()` isn't actually a logger. So what's going
on here?

Well, when you call `mock()` in Phony, what you get back isn't actually the mock
object itself, but another type of object, called a "mock handle".

The mock handle is what we use in our test to control the *actual* mock object's
behavior. It's also what we use to assert, or "verify" what happened to the mock
once it's passed to the system we're actually testing.

Since this object that we've called `$mock` isn't actually the mock itself,
let's rename it to avoid confusion. I'm going to call it `$logger`:

> (typing)

```php
<?php

use Psr\Log\LoggerInterface;
use function Eloquent\Phony\Phpunit\mock;

class PhonyTest extends PHPUnit_Framework_TestCase
{
    public function testExample()
    {
        $logger = mock(LoggerInterface::class);

        $this->assertTrue($logger instanceof LoggerInterface);
    }
}
```

Now, in order to fix up our test, we need to get the *actual* mock object out of
the handle, which we can do by calling `get()` on the handle itself:

> (typing)

```php
<?php

use Psr\Log\LoggerInterface;
use function Eloquent\Phony\Phpunit\mock;

class PhonyTest extends PHPUnit_Framework_TestCase
{
    public function testExample()
    {
        $logger = mock(LoggerInterface::class);

        $this->assertTrue($logger->get() instanceof LoggerInterface);
    }
}
```

And as you can see:

> (run in terminal)

...our tests are now passing again.

This might seem a little strange right now, so to make the whole situation
clearer, let's expand our example and see how our mock interacts with the system
we're going to test.

Let's write our tests first, because who doesn't love a chance to show how good
they are at test driven development, right? I want to create an object called
`LogWriter` that takes the logger as a constructor dependency:

> (typing)

```php
<?php

use Psr\Log\LoggerInterface;
use function Eloquent\Phony\Phpunit\mock;

class PhonyTest extends PHPUnit_Framework_TestCase
{
    public function testExample()
    {
        $logger = mock(LoggerInterface::class);
        $writer = new LogWriter($logger->get());

        $this->assertTrue($logger->get() instanceof LoggerInterface);
    }
}
```

And when I call a method called `write()` on it, with a string:

> (typing)

```php
<?php

use Psr\Log\LoggerInterface;
use function Eloquent\Phony\Phpunit\mock;

class PhonyTest extends PHPUnit_Framework_TestCase
{
    public function testExample()
    {
        $logger = mock(LoggerInterface::class);
        $writer = new LogWriter($logger->get());
        $writer->write('phony');

        $this->assertTrue($logger->get() instanceof LoggerInterface);
    }
}
```

I want to verify that an appropriate debug message was written to the log:

> (typing)

```php
<?php

use Psr\Log\LoggerInterface;
use function Eloquent\Phony\Phpunit\mock;

class PhonyTest extends PHPUnit_Framework_TestCase
{
    public function testExample()
    {
        $logger = mock(LoggerInterface::class);
        $writer = new LogWriter($logger->get());
        $writer->write('phony');

        $logger->debug->calledWith("You're a phony!");
    }
}
```

That should do it. Now, standard practice when doing TDD, let's make sure our
tests fail first before proceeding:

> (run in terminal)

Okay, "class `LogWriter` not found", so let's create that class:

> (typing)

```php
<?php

use Psr\Log\LoggerInterface;
use function Eloquent\Phony\Phpunit\mock;

class LogWriter
{
}

class PhonyTest extends PHPUnit_Framework_TestCase
{
    public function testExample()
    {
        $logger = mock(LoggerInterface::class);
        $writer = new LogWriter($logger->get());
        $writer->write('phony');

        $logger->debug->calledWith("You're a phony!");
    }
}
```

> (run in terminal)

Next problem, "call to undefined method `write()`", so obviously we need to
create the `write()` method:

> (typing)

```php
<?php

use Psr\Log\LoggerInterface;
use function Eloquent\Phony\Phpunit\mock;

class LogWriter
{
    public function write()
    {
    }
}

class PhonyTest extends PHPUnit_Framework_TestCase
{
    public function testExample()
    {
        $logger = mock(LoggerInterface::class);
        $writer = new LogWriter($logger->get());
        $writer->write('phony');

        $logger->debug->calledWith("You're a phony!");
    }
}
```

> (run in terminal)

Okay, now we're actually seeing our first verification failure from Phony. The
failure message is telling us "expected LoggerInterface debug call with
arguments: "You're a phony!", but it was never called".

So at this point, the only way to get the test working is to actually make our
`write()` method call the logger in the right way:

> (typing)

```php
<?php

use Psr\Log\LoggerInterface;
use function Eloquent\Phony\Phpunit\mock;

class LogWriter
{
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function write()
    {
        $this->logger->debug("You're a phony!");
    }

    private $logger;
}

class PhonyTest extends PHPUnit_Framework_TestCase
{
    public function testExample()
    {
        $logger = mock(LoggerInterface::class);
        $writer = new LogWriter($logger->get());
        $writer->write('phony');

        $logger->debug->calledWith("You're a phony!");
    }
}
```

> (run in terminal)

And now we've got a beautiful green success result from our test. But it's not a
very thorough test yet, so let's add another call to `write()`, and another
verification:

> (typing)

```php
<?php

use Psr\Log\LoggerInterface;
use function Eloquent\Phony\Phpunit\mock;

class LogWriter
{
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function write()
    {
        $this->logger->debug("You're a phony!");
    }

    private $logger;
}

class PhonyTest extends PHPUnit_Framework_TestCase
{
    public function testExample()
    {
        $logger = mock(LoggerInterface::class);
        $writer = new LogWriter($logger->get());
        $writer->write('phony');
        $writer->write('fake');

        $logger->debug->calledWith("You're a phony!");
        $logger->debug->calledWith("You're a fake!");
    }
}
```

> (run in terminal)

As you can see, our code isn't responding correctly to the input we're giving
it. Phony tells us that we've got two calls to the logger with the same
arguments, and neither of them match this second verification. Let's fix up our
`write()` method:

> (typing)

```php
<?php

use Psr\Log\LoggerInterface;
use function Eloquent\Phony\Phpunit\mock;

class LogWriter
{
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function write(string $noun)
    {
        $this->logger->debug("You're a $noun!");
    }

    private $logger;
}

class PhonyTest extends PHPUnit_Framework_TestCase
{
    public function testExample()
    {
        $logger = mock(LoggerInterface::class);
        $writer = new LogWriter($logger->get());
        $writer->write('phony');
        $writer->write('fake');

        $logger->debug->calledWith("You're a phony!");
        $logger->debug->calledWith("You're a fake!");
    }
}
```

> (run in terminal)

That's more like it. Our test is passing, and our `write()` method is working
great.
