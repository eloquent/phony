# Phony

*Mocks, stubs, and spies for PHP.*

[![Current version image][version-image]][current version]
[![Current build status image][build-image]][current build status]
[![Current coverage status image][coverage-image]][current coverage status]

[build-image]: http://img.shields.io/travis/eloquent/phony/develop.svg?style=flat-square "Current build status for the develop branch"
[coverage-image]: https://img.shields.io/codecov/c/github/eloquent/phony/develop.svg?style=flat-square "Current test coverage for the develop branch"
[current build status]: https://travis-ci.org/eloquent/phony
[current coverage status]: https://codecov.io/github/eloquent/phony
[current version]: https://packagist.org/packages/eloquent/phony
[version-image]: https://img.shields.io/packagist/v/eloquent/phony.svg?style=flat-square "This project uses semantic versioning"

## Installation and documentation

- Available as [Composer] package [eloquent/phony].
- Read the [documentation].

[composer]: http://getcomposer.org/
[eloquent/phony]: https://packagist.org/packages/eloquent/phony

## What is *Phony*?

*Phony* is a PHP library for creating [test doubles]. *Phony* has first-class
support for many modern PHP features such as [traits] and [generators], and
supports PHP 7 and [HHVM].

[generators]: http://php.net/language.generators.overview
[hhvm]: http://hhvm.com/
[test doubles]: https://en.wikipedia.org/wiki/Test_double
[traits]: http://php.net/traits

## Help

For help with a difficult testing scenario, questions regarding how to use
*Phony*, or to report issues with *Phony* itself, please open a [GitHub issue]
so that others may benefit from the outcome.

Alternatively, [@ezzatron] may be contacted directly via Twitter.

[@ezzatron]: https://twitter.com/ezzatron
[github issue]: https://github.com/eloquent/phony/issues

## Usage

For detailed usage, see the [documentation].

### Example test suites

See the [example] directory.

[example]: doc/example

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

[peridot]: http://peridot-php.github.io/

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

[pho]: https://github.com/danielstjules/pho

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

[phpunit]: https://phpunit.de/

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

[simpletest]: https://github.com/simpletest/simpletest

[documentation]: http://eloquent-software.com/phony/
