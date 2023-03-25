<?php

$parameterNames = ['a', 'b', 'c'];
$matcherSets = [
    'positional' => [1, 2, 3, 4, 5, 6],
    'positional, ignored keys' => [2 => 1, 4 => 2, 5 => 3, 0 => 4, 1 => 5, 3 => 6],
];

$matchingCases = [
    'positional' => [1, 2, 3, 4, 5, 6],
    'positional, ignored keys' => [2 => 1, 4 => 2, 5 => 3, 0 => 4, 1 => 5, 3 => 6],
];
$nonMatchingCases = [
    'empty' => [],
    'missing args' => [1, 2, 3, 4],
    'all non-match' => [-1, -1, -1, -1, -1, -1],
    'leading declared non-match' => [-1, 2, 3, 4, 5, 6],
    'middle declared non-match' => [1, -1, 3, 4, 5, 6],
    'trailing declared non-match' => [1, 2, -1, 4, 5, 6],
    'leading variadic non-match' => [1, 2, 3, -1, 5, 6],
    'middle variadic non-match' => [1, 2, 3, 4, -1, 6],
    'trailing variadic non-match' => [1, 2, 3, 4, 5, -1],
    'extra arguments' => [1, 2, 3, 4, 5, 6, 1, 2],
    'all non-match, extra arguments' => [-1, -1, -1, -1, -1, -1, -1, -1],
];
