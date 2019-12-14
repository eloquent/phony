<?php

if (class_exists('ReflectionReference')) {
    return;
}

final class ReflectionReference
{
    /**
     * @param array<mixed> $array
     * @param int|string   $key
     */
    public static function fromArrayElement(array $array, $key): ?ReflectionReference
    {
    }

    /**
     * @return int|string
     */
    public function getId()
    {
    }
}
