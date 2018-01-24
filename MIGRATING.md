# Migration guide

## Migrating from `2.x` to `3.x`

- [The `3.x` release only supports PHP 7.1 or later]
- [Utilization of void and nullable type hints]
- [Utilization of relaxed keywords]

[the `3.x` release only supports php 7.1 or later]: #the-3x-release-only-supports-php-71-or-later
[utilization of relaxed keywords]: #utilization-of-relaxed-keywords
[utilization of void and nullable type hints]: #utilization-of-void-and-nullable-type-hints

### The `3.x` release only supports PHP 7.1 or later

The *Phony* `3.x` release drops support for PHP 7.0. If you only need to support
PHP 7.1 or later, then it is recommended that you upgrade to *Phony* `3.x`. If
you still need to support PHP 7.0, then you are free to continue using the `2.x`
version of *Phony*.

### Utilization of void and nullable type hints

Where possible, the entire *Phony* `3.x` API now takes advantage of
[nullable types] and [void functions].

In terms of usage, this only affects [`setUseColor()`], which had an
undocumented default value of `null` for its only argument. This function now
requires an explicit `null` value if you wish to set color usage based upon the
current environment.

[`setUseColor()`]: http://eloquent-software.com/phony/3.0/#facade.setUseColor
[nullable types]: http://php.net/manual/migration71.new-features.php#migration71.new-features.nullable-types
[void functions]: http://php.net/manual/migration71.new-features.php#migration71.new-features.void-functions

### Utilization of relaxed keywords

The `handle->clazz()` method was renamed to [`handle->class()`], since PHP 7 now
allows `class` as a method name.

[`handle->class()`]: http://eloquent-software.com/phony/3.0/#handle.class

## Migrating from `1.x` to `2.x`

- [The `2.x` release only supports PHP 7]
- [More type hints, less squishy types]
- [Dynamic order verification functions removed]
- [Improved "self" value behavior for function-level stubs]

[the `2.x` release only supports php 7]: #the-2x-release-only-supports-php-7
[more type hints, less squishy types]: #more-type-hints-less-squishy-types
[dynamic order verification functions removed]: #dynamic-order-verification-functions-removed
[improved "self" value behavior for function-level stubs]: #improved-self-value-behavior-for-function-level-stubs

### The `2.x` release only supports PHP 7

The *Phony* `2.x` release is primarily about dropping support for PHP 5 and
HHVM. If you only need to support PHP 7, then it is recommended that you upgrade
to *Phony* `2.x`. If you still need to support PHP 5, then you are free to
continue using the `1.x` version of *Phony*.

### More type hints, less squishy types

Where possible, the entire *Phony* `2.x` API has introduced scalar type hints.
If your tests use [strict typing], and you are passing an incorrect type to
*Phony*, an error will now be thrown.

[strict typing]: http://php.net/functions.arguments#functions.arguments.type-declaration.strict

In addition; some values that were previously represented as a scalar value OR
`null`, have been changed to use a scalar value only:

- [Mock labels] and [spy labels] now use an empty string instead of `null` to
  represent "no label". Affects:
    - [`$handle->setLabel()`]
    - [`$handle->label()`]
    - [`$spy->setLabel()`]
    - [`$spy->label()`]
- Places where a "no maximum" amount is represented now use negative integers
  instead of `null`. Affects:
    - [`$spy->between()`]
    - [`$call->between()`]
    - [`$verification->between()`]
    - [`wildcard()`]
    - [`$wildcard->maximumArguments()`]

[mock labels]: http://eloquent-software.com/phony/2.0/#labeling-mocks
[spy labels]: http://eloquent-software.com/phony/2.0/#labeling-spies
[`$handle->setLabel()`]: http://eloquent-software.com/phony/2.0/#handle.setLabel
[`$handle->label()`]: http://eloquent-software.com/phony/2.0/#handle.label
[`$spy->setLabel()`]: http://eloquent-software.com/phony/2.0/#spy.setLabel
[`$spy->label()`]: http://eloquent-software.com/phony/2.0/#spy.label
[`$spy->between()`]: http://eloquent-software.com/phony/2.0/#spy.between
[`$call->between()`]: http://eloquent-software.com/phony/2.0/#call.between
[`$verification->between()`]: http://eloquent-software.com/phony/2.0/#verification.between
[`wildcard()`]: http://eloquent-software.com/phony/2.0/#facade.wildcard
[`$wildcard->maximumArguments()`]: http://eloquent-software.com/phony/2.0/#wildcard.maximumArguments

### Dynamic order verification functions removed

The following functions were removed from the top-level API because they have
been made redundant:

- [`inOrderSequence()`]
- [`checkInOrderSequence()`]
- [`anyOrderSequence()`]
- [`checkAnyOrderSequence()`]

[`inOrderSequence()`]: http://eloquent-software.com/phony/1.0/#facade.inOrderSequence
[`checkInOrderSequence()`]: http://eloquent-software.com/phony/1.0/#facade.checkInOrderSequence
[`anyOrderSequence()`]: http://eloquent-software.com/phony/1.0/#facade.anyOrderSequence
[`checkAnyOrderSequence()`]: http://eloquent-software.com/phony/1.0/#facade.checkAnyOrderSequence

In order to perform dynamic order verification under *Phony* `2.x`, simply use
the `...` operator:

```php
$events = [$spyA->called(), $spyB->called()];

inOrder(...$events);
inOrderSequence(...$events);
anyOrder(...$events);
anyOrderSequence(...$events);
```

### Improved "self" value behavior for function-level stubs

By default, a function-level stub's ["self" value] is now set to the stub
itself, rather than the callback wrapped by the stub. This was changed to
improve the functionality of stubs using [magic "self" values] in combination
with recursion.

When the "self" value is set to the wrapped callback, recursion requires passing
`$phonySelf` as the first argument when calling back into `$phonySelf`. But with
the "self" value set to the stub itself, this is no longer necessary, and
recursive functions become simpler:

```php
$factorial = stub(
    function ($phonySelf, $n) {
        if (0 === $n) {
            return 1;
        }

        // with the "self" value set to the stub itself (2.x default):
        return $n * $phonySelf($n - 1);

        // with the "self" value set to the wrapped callback (1.x default):
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

Stubs associated with a mock are not affected, and will continue to have their
"self" value default to the mock instance.

["self" value]: http://eloquent-software.com/phony/2.0/#stub-self-values
[magic "self" values]: http://eloquent-software.com/phony/2.0/#magic-self-values
