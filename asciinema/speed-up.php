<?php

const RATIO = 0.5;

$json = file_get_contents(__DIR__ . '/phony.asciicast');
$data = json_decode($json);

foreach ($data->stdout as $index => $frame) {
    list($time, $content) = $frame;

    $data->stdout[$index][0] = $time * RATIO;
}

$json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
file_put_contents(__DIR__ . '/phony.asciicast', $json);
