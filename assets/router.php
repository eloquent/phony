<?php

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (preg_match('/[^.]*[a-z]+[^.]*\./', basename($path))) {
    return false;
}

$htmlPath = '/' === substr($path, -1) ? "$path/index.html" : "$path.html";
$content = @file_get_contents($_SERVER['DOCUMENT_ROOT'] . $htmlPath);

if (false === $content) {
    return false;
}

echo $content;
