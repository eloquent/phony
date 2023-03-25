# `MatcherVerifier` test fixtures

## Glossary

- `params` - declared parameters
- `dmatchers` - matchers for declared parameters
- `vpmatchers` - variadic positional matchers
- `vnmatchers` - variadic named matchers
- `wildcard` - wildcard matcher

The `no-` prefix means there are none of the prefixed matcher type. The
`partial-` prefix is used only for `dmatchers`, meaning that only some prefix of
the declared parameters has associated matchers.

## Naming example

Take this fixture name as an example:

    params/no-dmatchers/no-vpmatchers/vnmatchers/no-wildcard

This fixture:

- Has declared parameters but no matchers for them
- Has no variadic positional matchers
- Has variadic named matchers
- Has no wildcard matcher

This might look like:

```php
$spy = spy(function ($a = null, $b = null, ...$args) {});
$spy->calledWith(c: 111, d: 222);
```

## Impossible permutations

- `no-params` + (`dmatchers`/`partial-dmatchers`) — When there are no declared
  parameters, it's not possible to have matchers for declared parameters.
- `params` + (`partial-dmatchers`/`no-dmatchers`) + `vpmatchers` — When there
  are declared parameters, and there are "omitted" declared parameter matchers,
  it's not possible to have variadic positional matchers.

## Declared parameters add complexity

When writing fixtures with declared parameter matchers, you need to include
matcher set variants that demonstrate that no matter whether the matchers are
provided positionally, named, or with a mixture of positional and named
matchers, the behavior is identical.

The same goes for the matching / non-matching cases. A user might provide
positional arguments, named arguments, or a mixture of positional and named
arguments.

For example, all of the following are perfectly valid calls and verifications:

```php
$spy = spy(function ($a, $b) {});

$spy(111, 222);
$spy(111, b: 222);
$spy(a: 111, b: 222);

$spy->calledWith(111, 222);
$spy->calledWith(111, b: 222);
$spy->calledWith(a: 111, b: 222);
```

## Positional matcher keys and positional argument keys are ignored

The indices of positional arguments and matchers are effectively ignored. This
matches the behavior of PHP itself:

```php
function x() {
    var_dump(func_get_args());
}

/**
 * Output:
 *
 * array(2) {
 *   [0]=>
 *   string(1) "a"
 *   [1]=>
 *   string(1) "b"
 * }
 */
call_user_func_array('x', [
  1 => 'a',
  0 => 'b',
]);
```
