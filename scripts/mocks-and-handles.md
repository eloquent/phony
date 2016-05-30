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

<!--
Also please note that when I refer to Phony's mocks as "mocks", it's probably
not the textbook correct term. Wikipedia has a list of definitions for different
types of [test doubles], and according to those definitions, Phony's mocks would
actually fall somewhere between "stubs" and "spies".

[test doubles]: https://en.wikipedia.org/wiki/Test_double#Types_of_test_doubles

But, since Phony *also* has stubs and spies for functions and other callables
(more on that in another video), its object-based test doubles are referred to
as mocks, for simplicity. It's also the generally accepted term in the PHP
community for anything that does what a Phony mock does.
-->

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

To take advantage of that, I'm going to remove the last part of this use
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

And now I can refer to the `mock()` function by using the last part of the `use`
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
just did there. We're getting a bit off topic though, so let's move on.

So that's basically your best option for importing Phony if you need to work
with PHP versions older than 5.6. However, if you *can* take advantage of the
`use statement` available from PHP 5.6 onward, then you can simplify things even
further.

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

And we're done. Simple as that. This last option is definitely my favorite when
technical constraints allow for it, and I recommend you use it when you can
also.
