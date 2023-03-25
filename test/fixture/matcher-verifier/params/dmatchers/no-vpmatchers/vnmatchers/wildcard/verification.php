<?php

$parameterNames = ['a', 'b', 'c'];
$wildcard = $matcherFactory->wildcard(
    value: 6,
    minimumArguments: 2,
    maximumArguments: 3,
);
$matcherSets = [
    'positional' => [1, 2, 3, $wildcard, 'd' => 4, 'e' => 5],
    'positional, ignored keys' => [3 => 1, 0 => 2, 1 => 3, 2 => $wildcard, 'd' => 4, 'e' => 5],
    'mixed' => [1, $wildcard, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5],
    'named, order a' => [$wildcard, 'a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5],
    'named, order b' => [$wildcard, 'd' => 4, 'e' => 5, 'b' => 2, 'a' => 1, 'c' => 3],
];

$matchingCases = [
    'positional' => [1, 2, 3, 6, 6, 'd' => 4, 'e' => 5],
    'positional, ignored keys' => [2 => 1, 0 => 2, 1 => 3, 4 => 6, 3 => 6, 'd' => 4, 'e' => 5],
    'mixed' => [1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5, 'z' => 6, 'y' => 6],
    'named, order a' => ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5, 'z' => 6, 'y' => 6],
    'named, order b' => ['d' => 4, 'e' => 5, 'b' => 2, 'a' => 1, 'c' => 3, 'y' => 6, 'z' => 6],
];
$nonMatchingCases = [
    'empty' => [],

    'positional, missing declared arg' => [1, 2, 'd' => 4, 'e' => 5, 'z' => 6, 'y' => 6],
    'mixed, missing declared arg' => [1, 'b' => 2, 'd' => 4, 'e' => 5, 'z' => 6, 'y' => 6],
    'named, missing declared arg' => ['a' => 1, 'b' => 2, 'd' => 4, 'e' => 5, 'z' => 6, 'y' => 6],

    'positional, missing variadic arg' => [1, 2, 3, 6, 'e' => 5, 'z' => 6],
    'mixed, missing variadic arg' => [1, 'b' => 2, 'c' => 3, 'e' => 5, 'z' => 6, 'y' => 6],
    'named, missing variadic arg' => ['a' => 1, 'b' => 2, 'c' => 3, 'e' => 5, 'z' => 6, 'y' => 6],

    'positional, all non-match' => [-1, -1, -1, -1, 'd' => -1, 'e' => -1, 'z' => -1],
    'mixed, all non-match' => [-1, 'b' => -1, 'c' => -1, 'd' => -1, 'e' => -1, 'z' => -1, 'y' => -1],
    'named, all non-match' => ['a' => -1, 'b' => -1, 'c' => -1, 'd' => -1, 'e' => -1, 'z' => -1, 'y' => -1],

    'positional, leading declared non-match' => [-1, 2, 3, 6, 'd' => 4, 'e' => 5, 'z' => 6],
    'mixed, leading declared non-match' => [-1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5, 'z' => 6, 'y' => 6],
    'named, leading declared non-match' => ['a' => -1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5, 'z' => 6, 'y' => 6],

    'positional, middle declared non-match' => [1, -1, 3, 6, 'd' => 4, 'e' => 5, 'z' => 6],
    'mixed, middle declared non-match' => [1, 'b' => -1, 'c' => 3, 'd' => 4, 'e' => 5, 'z' => 6, 'y' => 6],
    'named, middle declared non-match' => ['a' => 1, 'b' => -1, 'c' => 3, 'd' => 4, 'e' => 5, 'z' => 6, 'y' => 6],

    'positional, trailing declared non-match' => [1, 2, -1, 6, 'd' => 4, 'e' => 5, 'z' => 6],
    'mixed, trailing declared non-match' => [1, 'b' => 2, 'c' => -1, 'd' => 4, 'e' => 5, 'z' => 6, 'y' => 6],
    'named, trailing declared non-match' => ['a' => 1, 'b' => 2, 'c' => -1, 'd' => 4, 'e' => 5, 'z' => 6, 'y' => 6],

    'positional, variadic d non-match' => [1, 2, 3, 6, 'd' => -1, 'e' => 5, 'z' => 6],
    'mixed, variadic d non-match' => [1, 'b' => 2, 'c' => 3, 'd' => -1, 'e' => 5, 'z' => 6, 'y' => 6],
    'named, variadic d non-match' => ['a' => 1, 'b' => 2, 'c' => 3, 'd' => -1, 'e' => 5, 'z' => 6, 'y' => 6],

    'positional, variadic e non-match' => [1, 2, 3, 6, 'd' => 4, 'e' => -1, 'z' => 6],
    'mixed, variadic e non-match' => [1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => -1, 'z' => 6, 'y' => 6],
    'named, variadic e non-match' => ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => -1, 'z' => 6, 'y' => 6],

    'positional, leading wildcard non-match' => [1, 2, 3, -1, 'd' => 4, 'e' => 5, 'z' => 6],
    'mixed, leading wildcard non-match' => [1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5, 'z' => 6, 'y' => -1],
    'named, leading wildcard non-match' => ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5, 'z' => 6, 'y' => -1],

    'positional, trailing wildcard non-match' => [1, 2, 3, 6, 'd' => 4, 'e' => 5, 'z' => -1],
    'mixed, trailing wildcard non-match' => [1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5, 'z' => -1, 'y' => 6],
    'named, trailing wildcard non-match' => ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5, 'z' => -1, 'y' => 6],

    'positional, extra arguments' => [1, 2, 3, 6, 6, 6, 6, 6, 'd' => 4, 'e' => 5],
    'mixed, extra arguments' => [1, 2, 3, 6, 'd' => 4, 'e' => 5, 'z' => 6, 'y' => 6, 'x' => 6, 'w' => 6],
    'named, extra arguments' => ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5, 'z' => 6, 'y' => 6, 'x' => 6, 'w' => 6, 'v' => 6],

    'positional, all non-match, extra arguments' => [-1, -1, -1, -1, -1, -1, -1, -1, 'd' => -1, 'e' => -1],
    'mixed, all non-match, extra arguments' => [-1, -1, -1, -1, 'd' => -1, 'e' => -1, 'z' => -1, 'y' => -1, 'x' => -1, 'w' => -1],
    'named, all non-match, extra arguments' => ['a' => -1, 'b' => -1, 'c' => -1, 'd' => -1, 'e' => -1, 'z' => -1, 'y' => -1, 'x' => -1, 'w' => -1, 'v' => -1],
];
