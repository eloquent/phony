<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

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
