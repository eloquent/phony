# Phony and Kahlan by example

- [Introduction]
- [Project setup]
- [The domain resolver]
- [Creating the test suite]
- [Top-level test setup and teardown]
  - [Instantiating the domain resolver]
  - [Isolation from global functions]
- [Testing cache hits]
  - [Cache hit setup]
  - [Cache hit specs]
- [Testing cache misses]
  - [Cache miss setup]
  - [Cache miss specs]
- [Testing domain lookup failures]
  - [Domain lookup failure setup]
  - [Domain lookup failure specs]
- [Testing cache write failures]
  - [Cache write failure setup]
  - [Cache write failure specs]
- [Running the test suite]
- [Auto-wired test dependencies]
- [Conclusion]
- [Following on from this guide]

## Introduction

This guide is designed to help you get acquainted with the basics of writing
tests using [Phony] and [Kahlan], without covering every possible detail of the
features used. For more in-depth information, check out these sources of
documentation:

- [Phony documentation]
- [Kahlan documentation]
- The [Phony for Kahlan] repository

The bulk of this guide will involve testing a simple caching DNS resolver, using
a combination of the features provided by Phony and Kahlan.

## Project setup

To install Phony and Kahlan, use [Composer]'s [`require`] command to add them as
development dependencies:

    composer require --dev kahlan/kahlan eloquent/phony-kahlan

For the examples used in this guide, we'll also be making use of the [PSR-16]
cache interfaces, which can be installed by adding the [psr/simple-cache]
package as a regular dependency:

    composer require psr/simple-cache

Let's also configure Composer to autoload the classes that make up the example
system we'll be testing:

```json
{
  "autoload": {
    "psr-4": {
      "Example\\Dns\\": "src"
    }
  },
  "require-dev": {
    "kahlan/kahlan": "^4",
    "eloquent/phony-kahlan": "^1"
  },
  "require": {
    "psr/simple-cache": "^1"
  }
}
```

## The domain resolver

Our DNS resolver is a class that uses a [PSR-16] cache to avoid making multiple
DNS queries for the same domain name:

```php
<?php // src/DomainResolver.php

namespace Example\Dns;

use Psr\SimpleCache\CacheInterface;
use RuntimeException;

/**
 * Resolves domain names to IPv4 addresses.
 */
class DomainResolver
{
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Resolve a domain name.
     *
     * @param string $name The domain name.
     *
     * @return string           The resolved IPv4 address.
     * @throws RuntimeException If the domain name could not be resolved.
     */
    public function resolve(string $name): string
    {
        $cached = $this->cache->get($name);

        if ($cached !== null) {
            return $cached; // cache hit
        }

        $address = gethostbyname($name);

        // handle gethostbyname() failure
        if ($address === $name) {
            throw new RuntimeException('Unable to resolve.');
        }

        // handle cache failure
        if (!$this->cache->set($name, $address)) {
            throw new RuntimeException('Unable to cache.');
        }

        return $address;
    }

    private $cache;
}
```

## Creating the test suite

Let's lay out a Kahlan test suite. We'll leave the specs empty for now:

```php
<?php // spec/DomainResolver.spec.php

namespace Example\Dns;

describe('DomainResolver', function () {
    describe('resolve()', function () {
        context('when there is a matching cache entry', function () {
            it('should return the cached entry', function () {});
            it('should not attempt to resolve the name again', function () {});
            it('should not overwrite the cache entry', function () {});
        });

        context('when there is no matching cache entry', function () {
            it('should return the lookup result', function () {});
            it('should create a cache entry', function () {});
        });

        context('when domain lookup fails', function () {
            it('should throw an exception', function () {});
            it('should not create a cache entry', function () {});
        });

        context('when cache entry creation fails', function () {
            it('should throw an exception', function () {});
        });
    });
});
```

Since the specs make no assertions, Kahlan will mark them all as "pending":

    $ vendor/bin/kahlan
                _     _
      /\ /\__ _| |__ | | __ _ _ __
     / //_/ _` | '_ \| |/ _` | '_ \
    / __ \ (_| | | | | | (_| | | | |
    \/  \/\__,_|_| |_|_|\__,_|_| |_|

    The PHP Test Framework for Freedom, Truth and Justice.

    src directory  : ./src
    spec directory : ./spec

    PPPPPPPP                                                            8 / 8 (100%)


      Pending specifications: 8
      ./spec/DomainResolver.spec.php, line 8
      ./spec/DomainResolver.spec.php, line 9
      ./spec/DomainResolver.spec.php, line 10
      ./spec/DomainResolver.spec.php, line 14
      ./spec/DomainResolver.spec.php, line 15
      ./spec/DomainResolver.spec.php, line 19
      ./spec/DomainResolver.spec.php, line 20
      ./spec/DomainResolver.spec.php, line 24


    Expectations   : 0 Executed
    Specifications : 8 Pending, 0 Excluded, 0 Skipped

    Passed 0 of 0 PASS in 0.004 seconds (using 2MB)

## Top-level test setup and teardown

### Instantiating the domain resolver

To avoid repeating code, we'll create the domain resolver in a `beforeEach()`
block at the top-most level of the test suite. We'll use Phony's [`mock()`]
function to create a test double of the cache dependency required by
`DomainResolver`:

```php
<?php // spec/DomainResolver.spec.php

namespace Example\Dns;

use Psr\SimpleCache\CacheInterface;
use function Eloquent\Phony\Kahlan\mock;

describe('DomainResolver', function () {
    beforeEach(function () {
        $this->cache = mock(CacheInterface::class);
        $this->resolver = new DomainResolver($this->cache->get());
    });

    // ...
});
```

Notice that:

- We imported the `Psr\SimpleCache\CacheInterface` interface, and the
  `Eloquent\Phony\Kahlan\mock` function.
- The [`mock()`] function returns a [mock handle], which we've stored in
  `$this->cache`.
- We retrieved the actual mock object by calling `$this->cache->get()`, and
  passed it to the `DomainResolver`'s constructor.

The separation of "mock handle" and "mock object" is important to understand.
[Mock handles] have two primary purposes:

- To allow for [stubbing], which lets us control how the mock object behaves.
- To allow for [verification], which lets us determine what happened to the mock
  object during testing.

In contrast to this, the "mock object" is what we pass into the system we're
testing. It is responsible for recording incoming method calls, and responding
to them according to rules that we configure via the mock handle.

### Isolation from global functions

The next thing that we need to take care of is the fact that our
`DomainResolver` also makes use of the "global" function [`gethostbyname()`]. If
this function were called during test execution, real DNS queries would be
issued, and it would be impossible to test how our system interacts with this
function.

We can prevent the actual [`gethostbyname()`] function from being called by
using Phony's [`stubGlobal()`] function in the top-level `beforeEach()` block.
We must also use [`restoreGlobalFunctions()`] in a matching `afterEach()` block
to restore the original behavior of [`gethostbyname()`] once each spec has
completed:

```php
<?php // spec/DomainResolver.spec.php

namespace Example\Dns;

use Psr\SimpleCache\CacheInterface;
use function Eloquent\Phony\Kahlan\mock;
use function Eloquent\Phony\Kahlan\restoreGlobalFunctions;
use function Eloquent\Phony\Kahlan\stubGlobal;

describe('DomainResolver', function () {
    beforeEach(function () {
        $this->cache = mock(CacheInterface::class);
        $this->resolver = new DomainResolver($this->cache->get());

        $this->gethostbyname = stubGlobal('gethostbyname', __NAMESPACE__);
    });

    afterEach(function () {
        restoreGlobalFunctions();
    });

    // ...
});
```

Notice that:

- We imported the `Eloquent\Phony\Kahlan\stubGlobal` and
  `Eloquent\Phony\Kahlan\restoreGlobalFunctions` functions.
- The [`stubGlobal()`] function takes a function name and the namespace from
  which the function will be called, and returns a [stub], which we've stored in
  `$this->gethostbyname`.

We can now move on to writing actual specs.

## Testing cache hits

Let's test the behavior of our resolver when the cache already contains a
resolved address for the supplied domain name.

### Cache hit setup

We can use our cache's handle to simulate a cache "hit":

```php
context('when there is a matching cache entry', function () {
    beforeEach(function () {
        $this->cache->get->returns('1.1.1.1');
    });

    // ...
});
```

In this `beforeEach()` block:

- We retrieved the [stub] for the cache's `get()` method by accessing
  `$this->cache->get`.
- We used the [`returns()`] method of the stub to make the method return
  `'1.1.1.1'` when called.

The net result of this, is that when our domain resolver calls `get()` on the
cache, it will return the value we told it to. This is all the setup we require
for this block of specs.

### Cache hit specs

When a cache hit occurs, the resolver should return the cached address, so let's
test that requirement with a spec:

```php
it('should return the cached entry', function () {
    $address = $this->resolver->resolve('example.org.');

    expect($address)->toBe('1.1.1.1');
    $this->cache->get->calledWith('example.org.');
});
```

In this spec:

- We called our resolver with an example domain of `'example.org.'`.
- We used Kahlan's [`expect()`] interface to assert that the returned address
  matches what the cache returned, using `toBe()`.
- We used Phony's [`calledWith()`] method to assert that our domain resolver
  uses the correct key when querying the cache.

We also want to make sure that our domain resolver does **not** call
[`gethostbyname()`] when there's a cache hit:

```php
it('should not attempt to resolve the name again', function () {
    $this->resolver->resolve('example.org.');

    $this->gethostbyname->never()->called();
});
```

In this spec:

- We called our resolver with an example domain of `'example.org.'`.
- We used Phony's [`called()`] method in combination with the [`never()`]
  modifier to assert that [`gethostbyname()`] is not called by our resolver.

We can take a similar approach to check that our resolver does **not** alter the
cache if there's a cache hit:

```php
it('should not overwrite the cache entry', function () {
    $this->resolver->resolve('example.org.');

    $this->cache->set->never()->called();
});
```

Our cache hit specs are now complete.

## Testing cache misses

We need to test the behavior of our resolver when no cache data exists for the
supplied domain name.

### Cache miss setup

When there is a cache miss, the [`gethostbyname()`] function will be called, and
we need to instruct its stub to return a typical result. We also need to
instruct the cache to return `true` from `set()`, in order to simulate a
successful cache write:

```php
context('when there is no matching cache entry', function () {
    beforeEach(function () {
        $this->gethostbyname->returns('1.1.1.1');
        $this->cache->set->returns(true);
    });

    // ...
});
```

By default, our cache will return `null` when `get()` is called, unless we
specify some other behavior. Since this is how the cache should behave when
there is no matching entry, we do not need to modify the behavior of `get()` for
this set of specs, so that's all the setup required here.

### Cache miss specs

When a cache miss occurs, the resolver should perform a real DNS query, and
return the result:

```php
it('should return the lookup result', function () {
    $address = $this->resolver->resolve('example.org.');

    expect($address)->toBe('1.1.1.1');
    $this->gethostbyname->calledWith('example.org.');
});
```

The result should also be stored in the cache for subsequent calls:

```php
it('should create a cache entry', function () {
    $this->resolver->resolve('example.org.');

    $this->cache->set->calledWith('example.org.', '1.1.1.1');
});
```

These two specs are all that are required for the cache miss block.

## Testing domain lookup failures

It's important to test the error paths in our resolver. One error that can occur
is a DNS lookup failure. Let's test this now.

### Domain lookup failure setup

The manual entry for [`gethostbyname()`] states that the function:

> Returns the IPv4 address **or a string containing the unmodified hostname on
> failure.**

So we need to configure [`gethostbyname()`] to return whatever is passed to it:

```php
context('when domain lookup fails', function () {
    beforeEach(function () {
        $this->gethostbyname->returnsArgument();
    });

    // ...
});
```

In this `beforeEach()` block:

- We used the [`returnsArgument()`] method of the stub to make
  [`gethostbyname()`] return the first argument it is called with.

### Domain lookup failure specs

We're about to make use of the `RuntimeException` class, so we need to import it
at the top of the test suite file:

```php
use Psr\SimpleCache\CacheInterface;
use RuntimeException; // <-------------------------------- add this
use function Eloquent\Phony\Kahlan\mock;
use function Eloquent\Phony\Kahlan\restoreGlobalFunctions;
use function Eloquent\Phony\Kahlan\stubGlobal;
```

When a domain lookup failure occurs, we want our resolver to throw an exception
indicating the failure:

```php
it('should throw an exception', function () {
    $resolve = function () {
        $this->resolver->resolve('example.org.');
    };

    expect($resolve)->toThrow(new RuntimeException('Unable to resolve.'));
});
```

In this spec:

- We created a closure in which we call our resolver.
- We used Kahlan's [`expect()`] interface to assert that this closure throws an
  exception like the one we expect, using `toThrow()`.

In the case of a domain lookup failure, the cache should also remain untouched,
so let's test that:

```php
it('should not create a cache entry', function () {
    $resolve = function () {
        $this->resolver->resolve('example.org.');
    };

    expect($resolve)->toThrow();
    $this->cache->set->never()->called();
});
```

This spec is similar to the one above, but we care less about the type of
exception thrown, and more about the interaction with the cache.

## Testing cache write failures

According to [PSR-16], writing to the cache using `set()` can fail, which is
indicated by a return value of `false`. That's an important case that we should
cover with a spec.

### Cache write failure setup

We're only going to write one spec for this case, but we'll still do the setup
inside a `beforeEach()` block, in case we think of something else that needs the
same kind of setup in the future.

We need to get our resolver into a state where it will write to the cache, so we
need to simulate a cache miss, then a successful domain lookup. Then finally, we
need to simulate a failed cache write. The cache miss requires no setup, as
explained earlier; and the other two are handled like so:

```php
context('when cache entry creation fails', function () {
    beforeEach(function () {
        $this->gethostbyname->returns('1.1.1.1');
        $this->cache->set->returns(false);
    });

    // ...
});
```

### Cache write failure specs

Now we can test that the correct exception is thrown, similar to previous specs:

```php
it('should throw an exception', function () {
    $resolve = function () {
        $this->resolver->resolve('example.org.');
    };

    expect($resolve)->toThrow(new RuntimeException('Unable to cache.'));
});
```

This is all that's needed to test this particular failure case, and concludes
the specs we'll be writing for our domain resolver.

## Running the test suite

Our test suite should now look something like this:

```php
<?php // spec/DomainResolver.spec.php

namespace Example\Dns;

use Psr\SimpleCache\CacheInterface;
use RuntimeException;
use function Eloquent\Phony\Kahlan\mock;
use function Eloquent\Phony\Kahlan\restoreGlobalFunctions;
use function Eloquent\Phony\Kahlan\stubGlobal;

describe('DomainResolver', function () {
    beforeEach(function () {
        $this->cache = mock(CacheInterface::class);
        $this->resolver = new DomainResolver($this->cache->get());

        $this->gethostbyname = stubGlobal('gethostbyname', __NAMESPACE__);
    });

    afterEach(function () {
        restoreGlobalFunctions();
    });

    describe('resolve()', function () {
        context('when there is a matching cache entry', function () {
            beforeEach(function () {
                $this->cache->get->returns('1.1.1.1');
            });

            it('should return the cached entry', function () {
                $address = $this->resolver->resolve('example.org.');

                expect($address)->toBe('1.1.1.1');
                $this->cache->get->calledWith('example.org.');
            });

            it('should not attempt to resolve the name again', function () {
                $this->resolver->resolve('example.org.');

                $this->gethostbyname->never()->called();
            });

            it('should not overwrite the cache entry', function () {
                $this->resolver->resolve('example.org.');

                $this->cache->set->never()->called();
            });
        });

        context('when there is no matching cache entry', function () {
            beforeEach(function () {
                $this->gethostbyname->returns('1.1.1.1');
                $this->cache->set->returns(true);
            });

            it('should return the lookup result', function () {
                $address = $this->resolver->resolve('example.org.');

                expect($address)->toBe('1.1.1.1');
                $this->gethostbyname->calledWith('example.org.');
            });

            it('should create a cache entry', function () {
                $this->resolver->resolve('example.org.');

                $this->cache->set->calledWith('example.org.', '1.1.1.1');
            });
        });

        context('when domain lookup fails', function () {
            beforeEach(function () {
                $this->gethostbyname->returnsArgument();
            });

            it('should throw an exception', function () {
                $resolve = function () {
                    $this->resolver->resolve('example.org.');
                };

                expect($resolve)->toThrow(new RuntimeException('Unable to resolve.'));
            });

            it('should not create a cache entry', function () {
                $resolve = function () {
                    $this->resolver->resolve('example.org.');
                };

                expect($resolve)->toThrow();
                $this->cache->set->never()->called();
            });
        });

        context('when cache entry creation fails', function () {
            beforeEach(function () {
                $this->gethostbyname->returns('1.1.1.1');
                $this->cache->set->returns(false);
            });

            it('should throw an exception', function () {
                $resolve = function () {
                    $this->resolver->resolve('example.org.');
                };

                expect($resolve)->toThrow(new RuntimeException('Unable to cache.'));
            });
        });
    });
});
```

Let's use Kahlan to run the suite, and see how we did in terms of test coverage:

    $ phpdbg -qrr vendor/bin/kahlan --coverage=3
                _     _
      /\ /\__ _| |__ | | __ _ _ __
     / //_/ _` | '_ \| |/ _` | '_ \
    / __ \ (_| | | | | | (_| | | | |
    \/  \/\__,_|_| |_|_|\__,_|_| |_|

    The PHP Test Framework for Freedom, Truth and Justice.

    src directory  : ./src
    spec directory : ./spec

    ........                                                            8 / 8 (100%)



    Expectations   : 11 Executed
    Specifications : 0 Pending, 0 Excluded, 0 Skipped

    Passed 8 of 8 PASS in 0.027 seconds (using 7MB)

    Coverage Summary
    ----------------
                                 Lines           %

     \                           7 / 7     100.00%
    └── Example\                 7 / 7     100.00%
       └── Dns\                  7 / 7     100.00%
          └── DomainResolver     7 / 7     100.00%

    Total: 100.00% (7/7)

    Coverage collected in 0.002 seconds (using an additional 0B)

Our coverage is at 100%, and that's definitely good enough for the purposes of
this guide.

## Auto-wired test dependencies

With a little bit of configuration, we can take advantage of a simpler way to
obtain mock objects for our test suite. All that's required is to install
[Phony for Kahlan] inside a [Kahlan configuration file]:

```php
<?php // kahlan-config.php

Eloquent\Phony\Kahlan\install();
```

Now we can simply type hint the mocks we require, rather than creating them
explicitly with [`mock()`]:

```php
beforeEach(function (CacheInterface $cache) {
    $this->resolver = new DomainResolver($cache);

    $this->cache = on($cache);
    $this->gethostbyname = stubGlobal('gethostbyname', __NAMESPACE__);
});
```

Note that in order to retrieve the [mock handle] for the injected mock, we need
to use the [`on()`] function provided by Phony:

```php
use Psr\SimpleCache\CacheInterface;
use RuntimeException;
use function Eloquent\Phony\Kahlan\on; // <--------------- add this
use function Eloquent\Phony\Kahlan\restoreGlobalFunctions;
use function Eloquent\Phony\Kahlan\stubGlobal;
```

Other than that, the rest of our test suite remains unchanged.

## Conclusion

This guide has attempted to demonstrate some useful techniques for testing with
[Phony] and [Kahlan] in combination. That doesn't mean that this was an attempt
at demonstrating good testing practices.

For one thing, we wrote our code first, and then attempted to write
comprehensive tests afterward. In an ideal world, we should probably follow
[test-driven development] principles. We should also attempt to be more
comprehensive in our coverage of error cases.

Nevertheless, hopefully the examples presented here inspire you to continue
learning, and improving your testing skills.

## Following on from this guide

So now that you've dipped your toes into Phony's API, what's next? Well, I would
suggest checking out these sections of the [Phony documentation]:

- [Stubs] and [The stub API]
- [Spies] and [The spy API]
- [Mocks] and [The top-level API]
- [Understanding verification output]

Thanks for reading!

<!-- Heading references -->

[auto-wired test dependencies]: #auto-wired-test-dependencies
[cache hit setup]: #cache-hit-setup
[cache hit specs]: #cache-hit-specs
[cache miss setup]: #cache-miss-setup
[cache miss specs]: #cache-miss-specs
[cache write failure setup]: #cache-write-failure-setup
[cache write failure specs]: #cache-write-failure-specs
[conclusion]: #conclusion
[creating the test suite]: #creating-the-test-suite
[domain lookup failure setup]: #domain-lookup-failure-setup
[domain lookup failure specs]: #domain-lookup-failure-specs
[following on from this guide]: #following-on-from-this-guide
[instantiating the domain resolver]: #instantiating-the-domain-resolver
[introduction]: #introduction
[isolation from global functions]: #isolation-from-global-functions
[project setup]: #project-setup
[running the test suite]: #running-the-test-suite
[testing cache hits]: #testing-cache-hits
[testing cache misses]: #testing-cache-misses
[testing cache write failures]: #testing-cache-write-failures
[testing domain lookup failures]: #testing-domain-lookup-failures
[the domain resolver]: #the-domain-resolver
[top-level test setup and teardown]: #top-level-test-setup-and-teardown

<!-- External references -->

[`called()`]: .#spy.called
[`calledwith()`]: .#spy.calledWith
[`expect()`]: https://kahlan.github.io/docs/dsl#expectations
[`gethostbyname()`]: http://php.net/gethostbyname
[`mock()`]: .#facade.mock
[`never()`]: .#spy.never
[`on()`]: .#facade.on
[`require`]: https://getcomposer.org/doc/03-cli.md#require
[`restoreglobalfunctions()`]: .#facade.restoreGlobalFunctions
[`returns()`]: .#stub.returns
[`returnsargument()`]: .#stub.returnsArgument
[`stubglobal()`]: .#facade.stubGlobal
[composer]: https://getcomposer.org/
[kahlan configuration file]: https://kahlan.github.io/docs/config-file
[kahlan documentation]: https://kahlan.github.io/docs/
[kahlan]: https://kahlan.github.io/docs/
[mock handle]: .#mock-handles
[mock handles]: .#mock-handles
[mocks]: .#mocks
[phony documentation]: .
[phony for kahlan]: https://github.com/eloquent/phony-kahlan
[phony]: .
[psr-16]: http://php-fig.org/psr/psr-16/
[psr/simple-cache]: https://packagist.org/packages/psr/simple-cache
[spies]: .#spies
[stub]: .#stubs
[stubbing]: .#stubs
[stubs]: .#stubs
[test-driven development]: https://en.wikipedia.org/wiki/Test-driven_development
[the spy api]: .#the-spy-api
[the stub api]: .#the-stub-api
[the top-level api]: .#the-top-level-api
[understanding verification output]: .#understanding-verification-output
[verification]: .#verification
