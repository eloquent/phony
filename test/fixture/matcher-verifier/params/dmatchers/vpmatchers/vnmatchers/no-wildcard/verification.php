<?php

$parameterNames = ['a', 'b', 'c'];
$matcherSets = [
    'positional, order a' => [1, 2, 3, 4, 5, 6, 'd' => 7, 'e' => 8],
    'positional, order a, ignored keys' => [2 => 1, 4 => 2, 5 => 3, 0 => 4, 1 => 5, 3 => 6, 'd' => 7, 'e' => 8],
    'positional, order b' => [1, 2, 3, 4, 5, 6, 'e' => 8, 'd' => 7],
];

$matchingCases = [
    'positional, order a' => [1, 2, 3, 4, 5, 6, 'd' => 7, 'e' => 8],
    'positional, order a, ignored keys' => [2 => 1, 4 => 2, 5 => 3, 0 => 4, 1 => 5, 3 => 6, 'd' => 7, 'e' => 8],
    'positional, order b' => [1, 2, 3, 4, 5, 6, 'e' => 8, 'd' => 7],
];
$nonMatchingCases = [
    'empty' => [],
    'missing declared arg' => [1, 2, 'd' => 4, 'e' => 5],
    'missing variadic positional arg' => [1, 2, 3, 4, 5, 'd' => 7, 'e' => 8],
    'missing variadic named arg' => [1, 2, 3, 4, 5, 6, 'e' => 8],
    'all non-match' => [-1, -1, -1, -1, -1, -1, 'd' => -1, 'e' => -1],
    'leading declared non-match' => [-1, 2, 3, 4, 5, 6, 'd' => 7, 'e' => 8],
    'middle declared non-match' => [1, -1, 3, 4, 5, 6, 'd' => 7, 'e' => 8],
    'trailing declared non-match' => [1, 2, -1, 4, 5, 6, 'd' => 7, 'e' => 8],
    'leading variadic positional non-match' => [1, 2, 3, -1, 5, 6, 'd' => 7, 'e' => 8],
    'middle variadic positional non-match' => [1, 2, 3, 4, -1, 6, 'd' => 7, 'e' => 8],
    'trailing variadic positional non-match' => [1, 2, 3, 4, 5, -1, 'd' => 7, 'e' => 8],
    'variadic named d non-match' => [1, 2, 3, 4, 5, 6, 'd' => -1, 'e' => 8],
    'variadic named e non-match' => [1, 2, 3, 4, 5, 6, 'd' => 7, 'e' => -1],
    'extra arguments' => [1, 2, 3, 4, 5, 6, 1, 2, 'd' => 7, 'e' => 8, 'z' => 1, 'y' => 2],
    'all non-match, extra arguments' => [-1, -1, -1, -1, -1, -1, -1, -1, 'd' => -1, 'e' => -1, 'z' => -1, 'y' => -1],
];
