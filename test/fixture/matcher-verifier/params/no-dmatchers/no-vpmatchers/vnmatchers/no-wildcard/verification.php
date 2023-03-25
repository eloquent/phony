<?php

$parameterNames = ['a', 'b'];
$matcherSets = [
    'order a' => ['c' => 1, 'd' => 2],
    'order b' => ['d' => 2, 'c' => 1],
];

$matchingCases = [
    'order a' => ['c' => 1, 'd' => 2],
    'order b' => ['d' => 2, 'c' => 1],
];
$nonMatchingCases = [
    'empty' => [],
    'c missing' => ['d' => 2],
    'd missing' => ['c' => 1],
    'c non-match' => ['c' => -1, 'd' => 2],
    'd non-match' => ['c' => 1, 'd' => -1],
    'all non-match' => ['c' => -1, 'd' => -1],
    'extra arguments, mixed' => [1, 2, 'c' => 1, 'd' => 2, 'z' => 1, 'y' => 2],
    'extra arguments, named' => ['a' => 1, 'b' => 2, 'c' => 1, 'd' => 2, 'z' => 1, 'y' => 2],
    'all non-match, extra arguments' =>  [1, 2, 'c' => -1, 'd' => -1, 'z' => 1, 'y' => 2],
];
