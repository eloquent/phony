<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test\Enum;

interface TestInterfaceUsingEnums
{
    public static function staticMethodA(): TestBasicEnum;

    public static function staticMethodB(TestBasicEnum $case): TestBasicEnum;

    public static function staticMethodC(): TestBackedEnum;

    public static function staticMethodD(TestBackedEnum $case): TestBackedEnum;

    public function methodA(): TestBasicEnum;

    public function methodB(TestBasicEnum $case): TestBasicEnum;

    public function methodC(): TestBackedEnum;

    public function methodD(TestBackedEnum $case): TestBackedEnum;
}
