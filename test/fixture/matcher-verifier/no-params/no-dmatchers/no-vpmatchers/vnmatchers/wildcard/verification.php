<?php

$parameterNames = [];
$wildcard = $matcherFactory->wildcard(
    value: 3,
    minimumArguments: 2,
    maximumArguments: 3,
);
$matcherSets = [
    'order a' => [$wildcard, 'a' => 1, 'b' => 2],
    'order b' => [$wildcard, 'b' => 2, 'a' => 1],
];

$matchingCases = [
    'positional wildcards, order a' => [3, 3, 'a' => 1, 'b' => 2],
    'positional wildcards, order b' => [3, 3, 'b' => 2, 'a' => 1],
    'mixed wildcards, order a' => [3, 'a' => 1, 'b' => 2, 'z' => 3],
    'mixed wildcards, order b' => [3, 'b' => 2, 'a' => 1, 'y' => 3],
    'named wildcards, order a' => ['a' => 1, 'b' => 2, 'z' => 3, 'y' => 3],
    'named wildcards, order b' => ['b' => 2, 'a' => 1, 'y' => 3, 'z' => 3],
];
$nonMatchingCases = [
    'empty' => [],
    'positional wildcards, a missing' => [3, 3, 'b' => 2],
    'mixed wildcards, a missing' => [3, 'b' => 2, 'z' => 3],
    'named wildcards, a missing' => ['b' => 2, 'z' => 3, 'y' => 3],
    'positional wildcards, b missing' => [3, 3, 'a' => 1],
    'mixed wildcards, b missing' => [3, 'a' => 1, 'z' => 3],
    'named wildcards, b missing' => ['a' => 1, 'z' => 3, 'y' => 3],
    'positional wildcards, a non-match' => [3, 3, 'a' => -1, 'b' => 2],
    'mixed wildcards, a non-match' => [3, 'a' => -1, 'b' => 2, 'z' => 3],
    'named wildcards, a non-match' => ['a' => -1, 'b' => 2, 'z' => 3, 'y' => 3],
    'positional wildcards, b non-match' => [3, 3, 'a' => 1, 'b' => -1],
    'mixed wildcards, b non-match' => [3, 'a' => 1, 'b' => -1, 'z' => 3],
    'named wildcards, b non-match' => ['a' => 1, 'b' => -1, 'z' => 3, 'y' => 3],
    'positional wildcards, all non-match' => [-1, -1, 'a' => -1, 'b' => -1],
    'mixed wildcards, all non-match' => [-1, 'a' => -1, 'b' => -1, 'z' => -1],
    'named wildcards, all non-match' => ['a' => -1, 'b' => -1, 'z' => -1, 'y' => -1],
    'extra arguments, mixed' => [3, 3, 3, 'a' => 1, 'b' => 2, 'z' => 3, 'y' => 3],
    'extra arguments, named' => ['a' => 1, 'b' => 2, 'z' => 3, 'y' => 3, 'x' => 3, 'w' => 3, 'v' => 3],
];
