<?php

$parameterNames = ['a', 'b', 'c'];
$matcherSets = [
    'positional' => [1, 2, 3],
    'positional, ignored keys' => [2 => 1, 0 => 2, 1 => 3],
    'mixed, order a' => [1, 'b' => 2, 'c' => 3],
    'mixed, order b' => [1, 'c' => 3, 'b' => 2],
    'named, order a' => ['a' => 1, 'b' => 2, 'c' => 3],
    'named, order b' => ['c' => 3, 'a' => 1, 'b' => 2],
];

$matchingCases = [
    'positional' => [1, 2, 3],
    'positional, ignored keys' => [2 => 1, 0 => 2, 1 => 3],
    'mixed, order a' => [1, 'b' => 2, 'c' => 3],
    'mixed, order b' => [1, 'c' => 3, 'b' => 2],
    'named, order a' => ['a' => 1, 'b' => 2, 'c' => 3],
    'named, order b' => ['c' => 3, 'a' => 1, 'b' => 2],
];
$nonMatchingCases = [
    'empty' => [],

    'positional, missing arg' => [1, 2],
    'mixed, missing arg' => [1, 'b' => 2],
    'named, missing arg' => ['a' => 1, 'b' => 2],

    'positional, all non-match' => [-1, -1, -1],
    'mixed, all non-match' => [-1, 'b' => -1, 'c' => -1],
    'named, all non-match' => ['a' => -1, 'b' => -1, 'c' => -1],

    'positional, leading non-match' => [-1, 2, 3],
    'mixed, leading non-match' => [-1, 'b' => 2, 'c' => 3],
    'named, leading non-match' => ['a' => -1, 'b' => 2, 'c' => 3],

    'positional, middle non-match' => [1, -1, 3],
    'mixed, middle non-match' => [1, 'b' => -1, 'c' => 3],
    'named, middle non-match' => ['a' => 1, 'b' => -1, 'c' => 3],

    'positional, trailing non-match' => [1, 2, -1],
    'mixed, trailing non-match' => [1, 'b' => 2, 'c' => -1],
    'named, trailing non-match' => ['a' => 1, 'b' => 2, 'c' => -1],

    'positional, extra arguments' => [1, 2, 3, 1, 2],
    'mixed, extra arguments' => [1, 'b' => 2, 'c' => 3, 'z' => 1, 'y' => 2],
    'named, extra arguments' => ['a' => 1, 'b' => 2, 'c' => 3, 'z' => 1, 'y' => 2],

    'positional, all non-match, extra arguments' => [-1, -1, -1, -1, -1],
    'mixed, all non-match, extra arguments' => [-1, 'b' => -1, 'c' => -1, 'z' => -1, 'y' => -1],
    'named, all non-match, extra arguments' => ['a' => -1, 'b' => -1, 'c' => -1, 'z' => -1, 'y' => -1],
];
