<?php

try {
    $function = new ReflectionFunction(function (object $a) {});
    $parameters = $function->getParameters();
    $isObjectTypeHintSupported = null === $parameters[0]->getClass();
} catch (ReflectionException $e) {
    $isObjectTypeHintSupported = false;
}

if (!$isObjectTypeHintSupported && !class_exists('object')) {
    eval('class object {}');
}
