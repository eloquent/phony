<?php

$parameterNames = ['a', 'b', 'c', 'd', 'e'];
$wildcard = $matcherFactory->wildcard(
    value: 6,
    minimumArguments: 2,
    maximumArguments: 3,
);
$matcherSets = [
    'positional' => [1, 2, 3, $wildcard, 'f' => 4, 'g' => 5],
    'positional, ignored keys' => [2 => 1, 3 => 2, 1 => 3, 0 => $wildcard, 'f' => 4, 'g' => 5],
    'mixed' => [1, $wildcard, 'b' => 2, 'c' => 3, 'f' => 4, 'g' => 5],
    'named, order a' => [$wildcard, 'a' => 1, 'b' => 2, 'c' => 3, 'f' => 4, 'g' => 5],
    'named, order b' => [$wildcard, 'f' => 4, 'g' => 5, 'b' => 2, 'a' => 1, 'c' => 3],
];

$matchingCases = [
    'positional' => [1, 2, 3, 6, 6, 'f' => 4, 'g' => 5],
    'positional, ignored keys' => [3 => 1, 4 => 2, 1 => 3, 2 => 6, 0 => 6, 'f' => 4, 'g' => 5],
    'mixed' => [1, 'b' => 2, 'c' => 3, 'f' => 4, 'g' => 5, 'z' => 6, 'y' => 6],
    'named, order a' => ['a' => 1, 'b' => 2, 'c' => 3, 'f' => 4, 'g' => 5, 'z' => 6, 'y' => 6],
    'named, order b' => ['f' => 4, 'g' => 5, 'b' => 2, 'a' => 1, 'c' => 3, 'y' => 6, 'z' => 6],
];
$nonMatchingCases = [
    'empty' => [],

    'positional, missing declared arg' => [1, 2, 'f' => 4, 'g' => 5, 'z' => 6, 'y' => 6],
    'mixed, missing declared arg' => [1, 'b' => 2, 'f' => 4, 'g' => 5, 'z' => 6, 'y' => 6],
    'named, missing declared arg' => ['a' => 1, 'b' => 2, 'f' => 4, 'g' => 5, 'z' => 6, 'y' => 6],

    'positional, missing variadic arg' => [1, 2, 3, 'g' => 5, 'z' => 6, 'y' => 6],
    'mixed, missing variadic arg' => [1, 'b' => 2, 'c' => 3, 'g' => 5, 'z' => 6, 'y' => 6],
    'named, missing variadic arg' => ['a' => 1, 'b' => 2, 'c' => 3, 'g' => 5, 'z' => 6, 'y' => 6],

    'positional, missing wildcard arg' => [1, 2, 3, 'f' => 4, 'g' => 5, 'z' => 6],
    'mixed, missing wildcard arg' => [1, 'b' => 2, 'c' => 3, 'f' => 4, 'g' => 5, 'z' => 6],
    'named, missing wildcard arg' => ['a' => 1, 'b' => 2, 'c' => 3, 'f' => 4, 'g' => 5, 'z' => 6],

    'positional, all non-match' => [-1, -1, -1, 'f' => -1, 'g' => -1, 'y' => -1, 'z' => -1],
    'mixed, all non-match' => [-1, 'b' => -1, 'c' => -1, 'f' => -1, 'g' => -1, 'y' => -1, 'z' => -1],
    'named, all non-match' => ['a' => -1, 'b' => -1  , 'c' => -1, 'f' => -1, 'g' => -1, 'y' => -1, 'z' => -1],

    'positional, leading declared non-match' => [-1, 2, 3, 'f' => 4, 'g' => 5, 'z' => 6, 'y' => 6],
    'mixed, leading declared non-match' => [-1, 'b' => 2, 'c' => 3, 'f' => 4, 'g' => 5, 'z' => 6, 'y' => 6],
    'named, leading declared non-match' => ['a' => -1, 'b' => 2, 'c' => 3, 'f' => 4, 'g' => 5, 'z' => 6, 'y' => 6],

    'positional, middle declared non-match' => [1, -1, 3, 'f' => 4, 'g' => 5, 'z' => 6, 'y' => 6],
    'mixed, middle declared non-match' => [1, 'b' => -1, 'c' => 3, 'f' => 4, 'g' => 5, 'z' => 6, 'y' => 6],
    'named, middle declared non-match' => ['a' => 1, 'b' => -1, 'c' => 3, 'f' => 4, 'g' => 5, 'z' => 6, 'y' => 6],

    'positional, trailing declared non-match' => [1, 2, -1, 'f' => 4, 'g' => 5, 'z' => 6, 'y' => 6],
    'mixed, trailing declared non-match' => [1, 'b' => 2, 'c' => -1, 'f' => 4, 'g' => 5, 'z' => 6, 'y' => 6],
    'named, trailing declared non-match' => ['a' => 1, 'b' => 2, 'c' => -1, 'f' => 4, 'g' => 5, 'z' => 6, 'y' => 6],

    'positional, variadic f non-match' => [1, 2, 3, 'f' => -1, 'g' => 5, 'z' => 6, 'y' => 6],
    'mixed, variadic f non-match' => [1, 'b' => 2, 'c' => 3, 'f' => -1, 'g' => 5, 'z' => 6, 'y' => 6],
    'named, variadic f non-match' => ['a' => 1, 'b' => 2, 'c' => 3, 'f' => -1, 'g' => 5, 'z' => 6, 'y' => 6],

    'positional, variadic g non-match' => [1, 2, 3, 'f' => 4, 'g' => -1, 'z' => 6, 'y' => 6],
    'mixed, variadic g non-match' => [1, 'b' => 2, 'c' => 3, 'f' => 4, 'g' => -1, 'z' => 6, 'y' => 6],
    'named, variadic g non-match' => ['a' => 1, 'b' => 2, 'c' => 3, 'f' => 4, 'g' => -1, 'z' => 6, 'y' => 6],

    'positional, wildcard y non-match' => [1, 2, 3, 'f' => 4, 'g' => 5, 'z' => 6, 'y' => -1],
    'mixed, wildcard y non-match' => [1, 'b' => 2, 'c' => 3, 'f' => 4, 'g' => 5, 'z' => 6, 'y' => -1],
    'named, wildcard y non-match' => ['a' => 1, 'b' => 2, 'c' => 3, 'f' => 4, 'g' => 5, 'z' => 6, 'y' => -1],

    'positional, wildcard z non-match' => [1, 2, 3, 'f' => 4, 'g' => 5, 'z' => -1, 'y' => 6],
    'mixed, wildcard z non-match' => [1, 'b' => 2, 'c' => 3, 'f' => 4, 'g' => 5, 'z' => -1, 'y' => 6],
    'named, wildcard z non-match' => ['a' => 1, 'b' => 2, 'c' => 3, 'f' => 4, 'g' => 5, 'z' => -1, 'y' => 6],

    'positional, extra arguments' => [1, 2, 3, 4, 5, 'f' => 4, 'g' => 5, 'z' => 6, 'y' => 6, 'w' => 6],
    'mixed, extra arguments' => [1, 2, 3, 4, 5, 'f' => 4, 'g' => 5, 'z' => 6, 'y' => 6, 'x' => 6, 'w' => 6, 'v' => 6],
    'named, extra arguments' => ['a' => 1, 'b' => 2, 'c' => 3, 'f' => 4, 'g' => 5, 'z' => 6, 'y' => 6, 'x' => 6, 'w' => 6, 'v' => 6],

    'positional, all non-match, extra arguments' => [-1, -1, -1, -1, -1, 'f' => -1, 'g' => -1, 'z' => -1, 'y' => -1, 'w' => -1],
    'mixed, all non-match, extra arguments' => [-1, -1, -1, -1, -1, 'f' => -1, 'g' => -1, 'z' => -1, 'y' => -1, 'x' => -1, 'w' => -1, 'v' => -1],
    'named, all non-match, extra arguments' => ['a' => -1, 'b' => -1, 'c' => -1, 'f' => -1, 'g' => -1, 'z' => -1, 'y' => -1, 'x' => -1, 'w' => -1, 'v' => -1],
];
