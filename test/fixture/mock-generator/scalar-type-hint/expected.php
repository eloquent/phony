<?php

class MockGeneratorScalarTypeHint
implements \Eloquent\Phony\Mock\Mock,
           \Eloquent\Phony\Test\TestInterfaceWithScalarTypeHint
{
    public function method(
        int $a,
        float $b,
        string $c,
        bool $d
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $a;
        }
        if ($¢argumentCount > 1) {
            $¢arguments[] = $b;
        }
        if ($¢argumentCount > 2) {
            $¢arguments[] = $c;
        }
        if ($¢argumentCount > 3) {
            $¢arguments[] = $d;
        }

        for ($¢i = 4; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!$this->_handle) {
            $¢result = null;

            return $¢result;
        }

        $¢result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
    }

    public function customMethod(
        int $int
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $int;
        }

        for ($¢i = 1; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!$this->_handle) {
            $¢result = null;

            return $¢result;
        }

        $¢result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
    }

    private static $_uncallableMethods = array (
  'method' => true,
);
    private static $_traitMethods = [];
    private static $_customMethods = [];
    private static $_staticHandle;
    private $_handle;
}
