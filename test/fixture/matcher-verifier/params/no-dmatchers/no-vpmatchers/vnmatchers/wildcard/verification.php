<?php

$parameterNames = ['a', 'b'];
$wildcard = $matcherFactory->wildcard(
    value: 3,
    minimumArguments: 2,
    maximumArguments: 3,
);
$matcherSets = [
    'order a' => [$wildcard, 'c' => 1, 'd' => 2],
    'order b' => [$wildcard, 'd' => 2, 'c' => 1],
];

$matchingCases = [
    'positional wildcards, order a' => [3, 3, 'c' => 1, 'd' => 2],
    'positional wildcards, order b' => [3, 3, 'd' => 2, 'c' => 1],
    'mixed wildcards, order a' => [3, 'c' => 1, 'd' => 2, 'z' => 3],
    'mixed wildcards, order b' => [3, 'd' => 2, 'c' => 1, 'y' => 3],
    'named wildcards, order a' => ['c' => 1, 'd' => 2, 'z' => 3, 'y' => 3],
    'named wildcards, order b' => ['d' => 2, 'c' => 1, 'y' => 3, 'z' => 3],
];
$nonMatchingCases = [
    'empty' => [],
    'positional wildcards, c missing' => [3, 3, 'd' => 2],
    'mixed wildcards, c missing' => [3, 'd' => 2, 'z' => 3],
    'named wildcards, c missing' => ['d' => 2, 'z' => 3, 'y' => 3],
    'positional wildcards, d missing' => [3, 3, 'c' => 1],
    'mixed wildcards, d missing' => [3, 'c' => 1, 'z' => 3],
    'named wildcards, d missing' => ['c' => 1, 'z' => 3, 'y' => 3],
    'positional wildcards, c non-match' => [3, 3, 'c' => -1, 'd' => 2],
    'mixed wildcards, c non-match' => [3, 'c' => -1, 'd' => 2, 'z' => 3],
    'named wildcards, c non-match' => ['c' => -1, 'd' => 2, 'z' => 3, 'y' => 3],
    'positional wildcards, d non-match' => [3, 3, 'c' => 1, 'd' => -1],
    'mixed wildcards, d non-match' => [3, 'c' => 1, 'd' => -1, 'z' => 3],
    'named wildcards, d non-match' => ['c' => 1, 'd' => -1, 'z' => 3, 'y' => 3],
    'positional wildcards, all non-match' => [-1, -1, 'c' => -1, 'd' => -1],
    'mixed wildcards, all non-match' => [-1, 'c' => -1, 'd' => -1, 'z' => -1],
    'named wildcards, all non-match' => ['c' => -1, 'd' => -1, 'z' => -1, 'y' => -1],
    'extra arguments, mixed' => [3, 3, 3, 'c' => 1, 'd' => 2, 'z' => 3, 'y' => 3],
    'extra arguments, named' => ['a' => 1, 'b' => 2, 'c' => 1, 'd' => 2, 'z' => 3, 'y' => 3, 'x' => 3, 'w' => 3, 'v' => 3],
];
