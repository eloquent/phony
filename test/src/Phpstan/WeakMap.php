<?php

if (class_exists('WeakMap')) {
    return;
}

final class WeakMap
{
    public function offsetExists(object $object): bool
    {
        return false;
    }

    public function offsetGet(object $object): mixed
    {
        return null;
    }

    public function offsetSet(object $object, mixed $value): void
    {
    }
}
