# Phony

*Mocks, stubs, and spies for PHP.*

[![Current version image][version-image]][current version]
[![Current build status image][build-image]][current build status]
[![Current Windows build status image][windows-build-image]][current windows build status]
[![Current coverage status image][coverage-image]][current coverage status]

[build-image]: https://img.shields.io/travis/eloquent/phony/master.svg?style=flat-square "Current build status for the master branch"
[coverage-image]: https://img.shields.io/codecov/c/github/eloquent/phony/master.svg?style=flat-square "Current test coverage for the master branch"
[current build status]: https://travis-ci.org/eloquent/phony
[current coverage status]: https://codecov.io/github/eloquent/phony
[current version]: https://packagist.org/packages/eloquent/phony
[current windows build status]: https://ci.appveyor.com/project/eloquent/phony
[version-image]: https://img.shields.io/packagist/v/eloquent/phony.svg?style=flat-square "This project uses semantic versioning"
[windows-build-image]: https://img.shields.io/appveyor/ci/eloquent/phony/master.svg?label=windows&style=flat-square "Current Windows build status for the master branch"

[![Example verification output][verification-output-image]][verification output]

[verification output]: https://asciinema.org/a/79430
[verification-output-image]: https://asciinema.org/a/79430.png "Example verification output"

## Installation and documentation

- Available as [Composer] package [eloquent/phony].
- Read the [documentation].

[composer]: http://getcomposer.org/
[eloquent/phony]: https://packagist.org/packages/eloquent/phony

## What is *Phony*?

*Phony* is a PHP library for creating [test doubles]. *Phony* supports a wide
range of PHP versions, from 5.3 through to 7, as well as [HHVM]. In addition,
*Phony* has excellent support for modern language features, such as
[generators].

[generators]: http://php.net/language.generators.overview
[test doubles]: https://en.wikipedia.org/wiki/Test_double

## Help

For help with a difficult testing scenario, questions regarding how to use
*Phony*, or to report issues with *Phony* itself, please open a [GitHub issue]
so that others may benefit from the outcome.

Alternatively, [@ezzatron] may be contacted directly via [Twitter].

[github issue]: https://github.com/eloquent/phony/issues
[twitter]: https://twitter.com/ezzatron

## Usage

For detailed usage, see the [documentation].

### Example test suites

See the [example] directory.

[example]: doc/example

### Standalone usage

```php
use function Eloquent\Phony\mock;

$handle = mock('ClassA');
$handle->methodA->with('argument')->returns('value');

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
        $handle->methodA->with('argument')->returns('value');

        $mock = $handle->mock();

        expect($mock->methodA('argument'))->to->equal('value');
        $handle->methodA->calledWith('argument');
    });
});
```

[peridot]: http://peridot-php.github.io/

### [Pho] usage

```php
use function Eloquent\Phony\Pho\mock;

describe('Phony', function () {
    it('integrates with Pho', function () {
        $handle = mock('ClassA');
        $handle->methodA->with('argument')->returns('value');

        $mock = $handle->mock();

        expect($mock->methodA('argument'))->toBe('value');
        $handle->methodA->calledWith('argument');
    });
});
```

[pho]: https://github.com/danielstjules/pho

### [PHPUnit] usage

```php
use Eloquent\Phony\Phpunit\Phony;

class PhonyTest extends PHPUnit_Framework_TestCase
{
    public function testIntegration()
    {
        $handle = Phony::mock('ClassA');
        $handle->methodA->with('argument')->returns('value');

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
        $handle->methodA->with('argument')->returns('value');

        $mock = $handle->mock();

        $this->assertSame($mock->methodA('argument'), 'value');
        $handle->methodA->calledWith('argument');
    }
}
```

## Why?

I'd like to answer this question a little less formally, by instead talking
about the history of *Phony*'s inception. Please forgive me if this section is
opinionated, or if I recall some particular detail of a framework incorrectly.
But if you want a TL;DR, basically:

- [Phake] doesn't support [traits], and once upon a time, *didn't* support
  [HHVM].
- [PHPUnit] and [Mockery] both implement [expect-run-verify] style mocks, which
  are evil.
- [Prophecy] doesn't support the features that I need.
- [SimpleTest] is ancient.
- I haven't heard of anything else that sounds promising.

The first mocking framework I used was *probably* [SimpleTest]'s. Unfortunately,
that's a long time ago now, and I didn't really understand mocking at the time.
And to top it off, I can't even remember how [SimpleTest]'s mocks functioned.
So let's skip ahead to the first mocking framework I really explored in depth,
which was [PHPUnit]'s.

When I first discovered [PHPUnit]'s mocks, they were revolutionary to me. They
allowed me to test things *really* thoroughly, in ways I didn't even know were
possible previously. Although [PHPUnit]'s mocking was likely a port of some
existing Java mocking system, it was the framework that first opened my eyes to
the real potential of test doubles.

Unfortunately it wasn't all sunshine and roses. [PHPUnit]'s mocks were difficult
to use, and especially, because of the [expect-run-verify] style interface, they
were difficult to *re*-use. There was no way to "un-expect" something in a
particular test, and when something failed, the true cause of the failure was
often difficult to determine.

While searching for a better solution, I stumbled across [Phake], which was
inspired by Java's *extremely* popular [Mockito] mocking framework. [Phake] was,
and still is, an excellent mocking framework. Both [Mockito] and [Phake] eschew
the [expect-run-verify] pattern, to great benefit.

By treating stubbing and verification as separate concepts, [Phake] essentially
fixed all of the problems I had with [PHPUnit]'s mocks. Mocks could be re-used
easily, and when a test failed, the cause was (usually) clear. I was sold on the
evils of [expect-run-verify], and swore never to go back.

I believe it was around this time that I heard of [Mockery]. Although I was
fairly happy with [Phake], it *did* have a few little oddities, such as the way
it deals with by-reference arguments, and mocking of [traits] was not possible.
So I checked out [Mockery], but was immediately put off by its use of
expectations; which I felt would have been a huge step backwards.

In fairness, it's possible that [Mockery] supports other mocking methods, but
since the "primary" way it works seems to be based around [expect-run-verify],
I've never considered it a viable candidate, and have never used it.

At some point around this time I worked on a Node.js project, and explored a
handful of the most popular Node mocking frameworks, before settling on the
excellent [Sinon.JS]. Its focus on callback-based stubbing and spying, and its
extensive verification API would eventually influence *Phony* heavily.

It wasn't until [HHVM] became a viable option in the PHP world that I really had
to consider using something other than [Phake]. I had wanted for a while to
start experimenting with [HHVM] in my projects. Unfortunately [Phake] had issues
that prevented the test suite from even *running* under [HHVM], so it was
immediately a problem.

One of my co-workers had intended to work on [HHVM] support for [Phake], but
unfortunately it seemed at that time as though work on [Phake] had stagnated,
and it took over a month just to get a [PR] accepted that added [HHVM] as a test
target. To [Phake]'s credit, [HHVM] *is* now supported, and hindsight has taught
me that HHVM support is **hard**.

One project that showed promise was [Prophecy], an "opinionated" mocking
framework that arose from the [phpspec] testing framework. While I disliked its
use of abstract terms like "prophesize" and "reveal" for method names, the core
idea of a separate object instance that can be used to control the actual mock
worked really well. So well, in fact, that I would eventually end up using this
concept in *Phony*.

Importantly, [Prophecy] already supported [HHVM], and seemed like a great fit to
replace [Phake]; until I ran into the "opinionated" side of [Prophecy]'s nature.
One thing that [Prophecy] does *not* support is order verification. For example;
verifying that your code opens a file before writing to it. It seemed to me to
be a feature whose benefits are self-evident, but the [Prophecy] developers
unfortunately [did not agree].

So, fool that I am, I thought "How hard can it be?" and started work on *Phony*,
a mocking framework designed to combine the strengths of its predecessors, and
allow testing under [HHVM] without compromise.

New versions of PHP came along and introduced new language features, and *Phony*
adapted to meet the requirements of testing these features. I was also fortunate
enough to be part of a development team at my day job, who were willing to be
the test bed for *Phony*, and it received a lot of real-world usage that
contributed immensely to *Phony*'s stability and eventual feature set.

Of course it turned into a much longer journey than I first expected, and
*Phony* continues to be a challenging project to maintain. But for me, it's an
invaluable tool that I use almost every day, and I hope it can be the same for
you.

Thanks for listening.

> Erin ([@ezzatron])

[did not agree]: https://github.com/phpspec/prophecy/issues/130
[expect-run-verify]: http://monkeyisland.pl/2008/02/01/deathwish/
[mockery]: http://docs.mockery.io/
[pr]: https://github.com/mlively/Phake/pull/133

## Thanks

Special thanks to the following people:

- [@jmalloc], [@koden-km], [@parkertr], and [@darianbr] for their invaluable
  help as test subjects.
- [@szczepiq], and everyone who contributed to [Mockito].
- [@mlively], and everyone who contributed to [Phake].
- [@cjohansen], and everyone who contributed to [Sinon.JS].
- [@everzet], and everyone who contributed to [Prophecy].
- [@sebastianbergmann], and everyone who contributed to [PHPUnit].

[@cjohansen]: https://github.com/cjohansen
[@darianbr]: https://github.com/darianbr
[@everzet]: https://github.com/everzet
[@ezzatron]: https://github.com/ezzatron
[@jmalloc]: https://github.com/jmalloc
[@koden-km]: https://github.com/koden-km
[@mlively]: https://github.com/mlively
[@parkertr]: https://github.com/parkertr
[@sebastianbergmann]: https://github.com/sebastianbergmann
[@szczepiq]: https://github.com/szczepiq

## License

For the full copyright and license information, please view the [LICENSE file].

[license file]: LICENSE

<!-- References -->

[documentation]: http://eloquent-software.com/phony/
[hhvm]: http://hhvm.com/
[mockito]: http://mockito.org/
[phake]: http://phake.readthedocs.org/
[phpspec]: http://phpspec.readthedocs.org/
[phpunit]: https://phpunit.de/
[prophecy]: https://github.com/phpspec/prophecy
[simpletest]: https://github.com/simpletest/simpletest
[sinon.js]: http://sinonjs.org/
[traits]: http://php.net/traits
