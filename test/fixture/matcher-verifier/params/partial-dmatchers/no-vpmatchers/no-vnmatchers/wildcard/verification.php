<?php

$parameterNames = ['a', 'b', 'c', 'd', 'e'];
$wildcard = $matcherFactory->wildcard(
    value: 4,
    minimumArguments: 2,
    maximumArguments: 3,
);
$matcherSets = [
    'positional' => [1, 2, 3, $wildcard],
    'positional, ignored keys' => [3 => 1, 0 => 2, 1 => 3, 2 => $wildcard],
    'mixed, order a' => [1, $wildcard, 'b' => 2, 'c' => 3],
    'mixed, order b' => [1, $wildcard, 'c' => 3, 'b' => 2],
    'named, order a' => [$wildcard, 'a' => 1, 'b' => 2, 'c' => 3],
    'named, order b' => [$wildcard, 'c' => 3, 'a' => 1, 'b' => 2],
];

$matchingCases = [
    'positional, positional wildcards' => [1, 2, 3, 4, 4],
    'positional, positional wildcards, ignored keys' => [4 => 1, 3 => 2, 1 => 3, 0 => 4, 2 => 4],
    'positional, mixed wildcards' => [1, 2, 3, 4, 'z' => 4],
    'positional, named wildcards' => [1, 2, 3, 'z' => 4, 'y' => 4],
    'mixed, order a' => [1, 'b' => 2, 'c' => 3, 'z' => 4, 'y' => 4],
    'mixed, order b' => [1, 'c' => 3, 'b' => 2, 'y' => 4, 'z' => 4],
    'named, order a' => ['a' => 1, 'b' => 2, 'c' => 3, 'z' => 4, 'y' => 4],
    'named, order b' => ['c' => 3, 'a' => 1, 'b' => 2, 'y' => 4, 'z' => 4],
];
$nonMatchingCases = [
    'empty' => [],

    'positional, missing declared arg' => [1, 2, 'y' => 4, 'z' => 4],
    'mixed, missing declared arg' => [1, 'b' => 2, 'y' => 4, 'z' => 4],
    'named, missing declared arg' => ['a' => 1, 'b' => 2, 'y' => 4, 'z' => 4],

    'positional, all non-match' => [-1, -1, -1, -1, -1],
    'mixed, all non-match' => [-1, 'b' => -1, 'c' => -1, 'y' => -1, 'z' => -1],
    'named, all non-match' => ['a' => -1, 'b' => -1, 'c' => -1, 'y' => -1, 'z' => -1],

    'positional, leading declared non-match' => [-1, 2, 3, 'y' => 4, 'z' => 4],
    'mixed, leading declared non-match' => [-1, 'b' => 2, 'c' => 3, 'y' => 4, 'z' => 4],
    'named, leading declared non-match' => ['a' => -1, 'b' => 2, 'c' => 3, 'y' => 4, 'z' => 4],

    'positional, middle declared non-match' => [1, -1, 3, 'y' => 4, 'z' => 4],
    'mixed, middle declared non-match' => [1, 'b' => -1, 'c' => 3, 'y' => 4, 'z' => 4],
    'named, middle declared non-match' => ['a' => 1, 'b' => -1, 'c' => 3, 'y' => 4, 'z' => 4],

    'positional, trailing declared non-match' => [1, 2, -1, 'y' => 4, 'z' => 4],
    'mixed, trailing declared non-match' => [1, 'b' => 2, 'c' => -1, 'y' => 4, 'z' => 4],
    'named, trailing declared non-match' => ['a' => 1, 'b' => 2, 'c' => -1, 'y' => 4, 'z' => 4],

    'positional, extra arguments' => [1, 2, 3, 4, 4, 4, 4, 4],
    'mixed, extra arguments' => [1, 'b' => 2, 'c' => 3, 'z' => 4, 'y' => 4, 'x' => 4, 'w' => 4, 'v' => 4],
    'named, extra arguments' => ['a' => 1, 'b' => 2, 'c' => 3, 'z' => 4, 'y' => 4, 'x' => 4, 'w' => 4, 'v' => 4],

    'positional, all non-match, extra arguments' => [-1, -1, -1, -1, -1, -1, -1, -1],
    'mixed, all non-match, extra arguments' => [-1, 'b' => -1, 'c' => -1, 'z' => -1, 'y' => -1, 'x' => -1, 'w' => -1, 'v' => -1],
    'named, all non-match, extra arguments' => ['a' => -1, 'b' => -1, 'c' => -1, 'z' => -1, 'y' => -1, 'x' => -1, 'w' => -1, 'v' => -1],
];
