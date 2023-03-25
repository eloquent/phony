<?php

$parameterNames = ['a', 'b', 'c'];
$wildcard = $matcherFactory->wildcard(
    value: 1,
    minimumArguments: 2,
    maximumArguments: 6,
);
$matcherSets = [
    'positional' => [$wildcard],
];

$matchingCases = [
    'positional, minimum arguments' => [1, 1],
    'mixed, minimum declared arguments' => [1, 'b' => 1],
    'mixed, minimum declared + variadic arguments' => [1, 'z' => 1],
    'named, minimum declared arguments' => ['a' => 1, 'b' => 1],
    'named, minimum variadic arguments' => ['z' => 1, 'y' => 1],
    'named, minimum declared + variadic arguments' => ['b' => 1, 'z' => 1],
    'positional, in-between arguments' => [1, 1, 1, 1],
    'mixed, in-between arguments' => [1, 'b' => 1, 'c' => 1, 'z' => 1],
    'named, in-between declared + variadic arguments' => ['a' => 1, 'b' => 1, 'c' => 1, 'z' => 1],
    'named, in-between variadic arguments' => ['z' => 1, 'y' => 1, 'x' => 1, 'w' => 1],
    'positional, maximum arguments' => [1, 1, 1, 1, 1, 1],
    'mixed, maximum arguments' => [1, 'b' => 1, 'c' => 1, 'z' => 1, 'y' => 1, 'x' => 1],
    'named, maximum declared + variadic arguments' => ['a' => 1, 'b' => 1, 'c' => 1, 'z' => 1, 'y' => 1, 'x' => 1],
    'named, maximum variadic arguments' => ['z' => 1, 'y' => 1, 'x' => 1, 'w' => 1, 'v' => 1, 'u' => 1],
];
$nonMatchingCases = [
    'empty' => [],

    'positional, below minimum' => [1],
    'named, below minimum declared' => ['a' => 1],
    'named, below minimum variadic' => ['z' => 1],

    'positional, above maximum' => [1, 1, 1, 1, 1, 1, 1],
    'mixed, above maximum declared' => [1, 'b' => 1, 'c' => 1, 'z' => 1, 'y' => 1, 'x' => 1, 'w' => 1],
    'mixed, above maximum variadic' => [1, 1, 'z' => 1, 'y' => 1, 'x' => 1, 'w' => 1, 'v' => 1],
    'named, above maximum declared' => ['b' => 1, 'c' => 1, 'z' => 1, 'y' => 1, 'x' => 1, 'w' => 1, 'v' => 1],
    'named, above maximum variadic' => ['z' => 1, 'y' => 1, 'x' => 1, 'w' => 1, 'v' => 1, 'u' => 1, 't' => 1],

    'positional, all non-match' => [-1, -1, -1, -1, -1, -1],
    'mixed, all non-match declared' => [-1, 'b' => -1, 'c' => -1, 'z' => -1, 'y' => -1, 'x' => -1],
    'mixed, all non-match variadic' => [-1, -1, 'z' => -1, 'y' => -1, 'x' => -1, 'w' => -1],
    'named, all non-match declared' => ['b' => -1, 'c' => -1, 'z' => -1, 'y' => -1, 'x' => -1, 'w' => -1],
    'named, all non-match variadic' => ['z' => -1, 'y' => -1, 'x' => -1, 'w' => -1, 'v' => -1, 'u' => -1],

    'positional, leading non-match' => [-1, 1],
    'mixed, leading non-match declared' => [-1, 'b' => 1],
    'mixed, leading non-match variadic' => [-1, 'z' => 1],
    'named, leading non-match declared' => ['a' => -1, 'b' => 1],
    'named, leading non-match variadic' => ['z' => -1, 'y' => 1],

    'positional, trailing non-match' => [1, 1, 1, 1, 1, -1],
    'mixed, trailing non-match declared' => [1, 'b' => 1, 'c' => 1, 'z' => -1, 'y' => 1, 'x' => 1],
    'mixed, trailing non-match variadic' => [1, 1, 'z' => -1, 'y' => 1, 'x' => 1, 'w' => 1],
    'named, trailing non-match declared' => ['b' => 1, 'c' => 1, 'z' => -1, 'y' => 1, 'x' => 1, 'w' => 1],
    'named, trailing non-match variadic' => ['z' => -1, 'y' => 1, 'x' => 1, 'w' => 1, 'v' => 1, 'u' => 1],

    'positional, extra declared' => [-1, 1, 1],
    'mixed, extra declared' => [-1, 'b' => 1, 'c' => 1],
    'named, extra declared' => ['a' => -1, 'b' => 1, 'c' => 1],

    'positional, all non-match, extra arguments' => [-1, -1, -1, -1, -1, -1, -1, -1],
    'mixed, all non-match, extra arguments' => [-1, 'b' => -1, 'c' => -1, 'z' => -1, 'y' => -1, 'x' => -1, 'w' => -1, 'v' => -1],
    'named, all non-match, extra arguments' => ['a' => -1, 'b' => -1, 'c' => -1, 'z' => -1, 'y' => -1, 'x' => -1, 'w' => -1, 'v' => -1],
];
