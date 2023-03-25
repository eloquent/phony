<?php

$parameterNames = ['a', 'b', 'c'];
$wildcard = $matcherFactory->wildcard(
    value: 4,
    minimumArguments: 2,
    maximumArguments: 3,
);
$matcherSets = [
    'positional' => [1, 2, 3, $wildcard],
    'positional, ignored keys' => [3 => 1, 0 => 2, 2 => 3, 1 => $wildcard],
    'mixed' => [1, $wildcard, 'b' => 2, 'c' => 3],
    'named' => [$wildcard, 'a' => 1, 'b' => 2, 'c' => 3],
];

$matchingCases = [
    'positional, minimum wildcards' => [1, 2, 3, 4, 4],
    'positional, maximum wildcards' => [1, 2, 3, 4, 4, 4],
    'positional, ignored keys' => [4 => 1, 0 => 2, 2 => 3, 1 => 4, 3 => 4],
    'mixed, minimum positional wildcards' => [1, 'b' => 2, 'c' => 3, 'z' => 4, 'y' => 4],
    'mixed, maximum positional wildcards' => [1, 'b' => 2, 'c' => 3, 'z' => 4, 'y' => 4, 'x' => 4],
    'named, minimum positional wildcards' => ['a' => 1, 'b' => 2, 'c' => 3, 'z' => 4, 'y' => 4],
    'named, maximum positional wildcards' => ['a' => 1, 'b' => 2, 'c' => 3, 'z' => 4, 'y' => 4, 'x' => 4],
];
$nonMatchingCases = [
    'empty' => [],

    'positional, missing declared arg' => [1, 2],
    'mixed, missing declared arg' => [1, 'b' => 2],
    'named, missing declared arg' => ['a' => 1, 'b' => 2],

    'positional, no wildcards' => [1, 2, 3],
    'mixed, no wildcards' => [1, 'b' => 2, 'c' => 3],
    'named, no wildcards' => ['a' => 1, 'b' => 2, 'c' => 3],

    'positional, below minimum wildcards' => [1, 2, 3, 4],
    'mixed, below minimum wildcards' => [1, 'b' => 2, 'c' => 3, 'z' => 4],
    'named, below minimum wildcards' => ['a' => 1, 'b' => 2, 'c' => 3, 'z' => 4],

    'positional, above maximum wildcards' => [1, 2, 3, 4, 4, 4, 4, 4],
    'mixed, above maximum wildcards' => [1, 'b' => 2, 'c' => 3, 'z' => 4, 'y' => 4, 'x' => 4, 'w' => 4, 'v' => 4],
    'named, above maximum wildcards' => ['a' => 1, 'b' => 2, 'c' => 3, 'z' => 4, 'y' => 4, 'x' => 4, 'w' => 4, 'v' => 4],

    'positional, all non-match' => [-1, -1, -1, -1, -1, -1],
    'mixed, all non-match' => [-1, 'b' => -1, 'c' => -1, 'z' => -1, 'y' => -1, 'x' => -1],
    'named, all non-match' => ['a' => -1, 'b' => -1, 'c' => -1, 'z' => -1, 'y' => -1, 'x' => -1],

    'positional, leading declared non-match' => [-1, 2, 3, 4, 4],
    'mixed, leading declared non-match' => [-1, 'b' => 2, 'c' => 3, 'z' => 4, 'y' => 4],
    'named, leading declared non-match' => ['a' => -1, 'b' => 2, 'c' => 3, 'z' => 4, 'y' => 4],

    'positional, middle declared non-match' => [1, -1, 3, 4, 4],
    'mixed, middle declared non-match' => [1, 'b' => -1, 'c' => 3, 'z' => 4, 'y' => 4],
    'named, middle declared non-match' => ['a' => 1, 'b' => -1, 'c' => 3, 'z' => 4, 'y' => 4],

    'positional, trailing declared non-match' => [1, 2, -1, 4, 4],
    'mixed, trailing declared non-match' => [1, 'b' => 2, 'c' => -1, 'z' => 4, 'y' => 4],
    'named, trailing declared non-match' => ['a' => 1, 'b' => 2, 'c' => -1, 'z' => 4, 'y' => 4],

    'positional, leading wildcard non-match' => [1, 2, 3, -1, 4],
    'mixed, leading wildcard non-match' => [1, 'b' => 2, 'c' => 3, 'y' => -1, 'z' => 4],
    'named, leading wildcard non-match' => ['a' => 1, 'b' => 2, 'c' => 3, 'y' => -1, 'z' => 4],

    'positional, trailing wildcard non-match' => [1, 2, 3, 4, -1],
    'mixed, trailing wildcard non-match' => [1, 'b' => 2, 'c' => 3, 'y' => 4, 'z' => -1],
    'named, trailing wildcard non-match' => ['a' => 1, 'b' => 2, 'c' => 3, 'y' => 4, 'z' => -1],

    'positional, all non-match, extra arguments' => [-1, -1, -1, -1, -1, -1, -1, -1],
    'mixed, all non-match, extra arguments' => [-1, 'b' => -1, 'c' => -1, 'z' => -1, 'y' => -1, 'x' => -1, 'w' => -1, 'v' => -1],
    'named, all non-match, extra arguments' => ['a' => -1, 'b' => -1, 'c' => -1, 'z' => -1, 'y' => -1, 'x' => -1, 'w' => -1, 'v' => -1],
];
