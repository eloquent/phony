<?php

$parameterNames = [];
$wildcard = $matcherFactory->wildcard(
    value: 1,
    minimumArguments: 3,
    maximumArguments: 6,
);
$matcherSets = [
    'wildcard' => [$wildcard],
];

$matchingCases = [
    'min args, positional' => [1, 1, 1],
    'min args, mixed' => [1, 'z' => 1, 'y' => 1],
    'min args, named' => ['z' => 1, 'y' => 1, 'x' => 1],
    'max args, positional' => [1, 1, 1, 1, 1, 1],
    'max args, mixed' => [1, 1, 1, 'z' => 1, 'y' => 1, 'x' => 1],
    'max args, named' => ['z' => 1, 'y' => 1, 'x' => 1, 'w' => 1, 'v' => 1, 'u' => 1],
];
$nonMatchingCases = [
    'empty' => [],
    'below min args, positional' => [1, 1],
    'below min args, mixed' => [1, 'z' => 1],
    'below min args, named' => ['z' => 1, 'y' => 1],
    'above max args, positional' => [1, 1, 1, 1, 1, 1, 1],
    'above max args, mixed' => [1, 1, 1, 'z' => 1, 'y' => 1, 'x' => 1, 'w' => 1],
    'above max args, named' => ['z' => 1, 'y' => 1, 'x' => 1, 'w' => 1, 'v' => 1, 'u' => 1, 't' => 1],
    'all non-match, positional' => [-1, -1, -1, -1, -1, -1],
    'all non-match, mixed' => [-1, -1, -1, 'z' => -1, 'y' => -1, 'x' => -1],
    'all non-match, named' => ['z' => -1, 'y' => -1, 'x' => -1, 'w' => -1, 'v' => -1, 'u' => -1],
    'all non-match, extra args, positional' => [-1, -1, -1, -1, -1, -1, -1, -1],
    'all non-match, extra args, mixed' => [-1, -1, -1, 'z' => -1, 'y' => -1, 'x' => -1, 'w' => -1, 'v' => -1],
    'all non-match, extra args, named' => ['z' => -1, 'y' => -1, 'x' => -1, 'w' => -1, 'v' => -1, 'u' => -1, 't' => -1, 's' => -1],
];
