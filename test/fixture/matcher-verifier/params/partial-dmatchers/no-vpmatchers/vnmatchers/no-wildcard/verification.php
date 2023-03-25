<?php

$parameterNames = ['a', 'b', 'c', 'd', 'e'];
$matcherSets = [
    'positional' => [1, 2, 3, 'f' => 4, 'g' => 5],
    'positional, ignored keys' => [2 => 1, 0 => 2, 1 => 3, 'f' => 4, 'g' => 5],
    'mixed' => [1, 'b' => 2, 'c' => 3, 'f' => 4, 'g' => 5],
    'named, order a' => ['a' => 1, 'b' => 2, 'c' => 3, 'f' => 4, 'g' => 5],
    'named, order b' => ['f' => 4, 'g' => 5, 'b' => 2, 'a' => 1, 'c' => 3],
];

$matchingCases = [
    'positional' => [1, 2, 3, 'f' => 4, 'g' => 5],
    'positional, ignored keys' => [2 => 1, 0 => 2, 1 => 3, 'f' => 4, 'g' => 5],
    'mixed' => [1, 'b' => 2, 'c' => 3, 'f' => 4, 'g' => 5],
    'named, order a' => ['a' => 1, 'b' => 2, 'c' => 3, 'f' => 4, 'g' => 5],
    'named, order b' => ['f' => 4, 'g' => 5, 'b' => 2, 'a' => 1, 'c' => 3],
];
$nonMatchingCases = [
    'empty' => [],

    'positional, missing declared arg' => [1, 2, 'f' => 4, 'g' => 5],
    'mixed, missing declared arg' => [1, 'b' => 2, 'f' => 4, 'g' => 5],
    'named, missing declared arg' => ['a' => 1, 'b' => 2, 'f' => 4, 'g' => 5],

    'positional, missing variadic arg' => [1, 2, 3, 'g' => 5],
    'mixed, missing variadic arg' => [1, 'b' => 2, 'c' => 3, 'g' => 5],
    'named, missing variadic arg' => ['a' => 1, 'b' => 2, 'c' => 3, 'g' => 5],

    'positional, all non-match' => [-1, -1, -1, 'f' => -1, 'g' => -1],
    'mixed, all non-match' => [-1, 'b' => -1, 'c' => -1, 'f' => -1, 'g' => -1],
    'named, all non-match' => ['a' => -1, 'b' => -1, 'c' => -1, 'f' => -1, 'g' => -1],

    'positional, leading declared non-match' => [-1, 2, 3, 'f' => 4, 'g' => 5],
    'mixed, leading declared non-match' => [-1, 'b' => 2, 'c' => 3, 'f' => 4, 'g' => 5],
    'named, leading declared non-match' => ['a' => -1, 'b' => 2, 'c' => 3, 'f' => 4, 'g' => 5],

    'positional, middle declared non-match' => [1, -1, 3, 'f' => 4, 'g' => 5],
    'mixed, middle declared non-match' => [1, 'b' => -1, 'c' => 3, 'f' => 4, 'g' => 5],
    'named, middle declared non-match' => ['a' => 1, 'b' => -1, 'c' => 3, 'f' => 4, 'g' => 5],

    'positional, trailing declared non-match' => [1, 2, -1, 'f' => 4, 'g' => 5],
    'mixed, trailing declared non-match' => [1, 'b' => 2, 'c' => -1, 'f' => 4, 'g' => 5],
    'named, trailing declared non-match' => ['a' => 1, 'b' => 2, 'c' => -1, 'f' => 4, 'g' => 5],

    'positional, variadic f non-match' => [1, 2, 3, 'f' => -1, 'g' => 5],
    'mixed, variadic f non-match' => [1, 'b' => 2, 'c' => 3, 'f' => -1, 'g' => 5],
    'named, variadic f non-match' => ['a' => 1, 'b' => 2, 'c' => 3, 'f' => -1, 'g' => 5],

    'positional, variadic g non-match' => [1, 2, 3, 'f' => 4, 'g' => -1],
    'mixed, variadic g non-match' => [1, 'b' => 2, 'c' => 3, 'f' => 4, 'g' => -1],
    'named, variadic g non-match' => ['a' => 1, 'b' => 2, 'c' => 3, 'f' => 4, 'g' => -1],

    'positional, extra arguments' => [1, 2, 3, 4, 5, 'f' => 4, 'g' => 5],
    'mixed, extra arguments' => [1, 2, 3, 4, 5, 'f' => 4, 'g' => 5, 'z' => 1, 'y' => 2],
    'named, extra arguments' => ['a' => 1, 'b' => 2, 'c' => 3, 'f' => 4, 'g' => 5, 'z' => 1, 'y' => 2],

    'positional, all non-match, extra arguments' => [-1, -1, -1, 1, 2, 'f' => -1, 'g' => -1],
    'mixed, all non-match, extra arguments' => [-1, -1, -1, 1, 2, 'f' => -1, 'g' => -1, 'z' => 1, 'y' => 2],
    'named, all non-match, extra arguments' => ['a' => -1, 'b' => -1, 'c' => -1, 'f' => -1, 'g' => -1, 'z' => 1, 'y' => 2],
];
