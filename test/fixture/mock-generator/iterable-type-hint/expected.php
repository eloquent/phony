<?php

class MockGeneratorIterableTypeHint
implements \Eloquent\Phony\Mock\Mock
{
    public function methodA(
        iterable $first,
        ?iterable $second = null
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $first;
        }
        if ($¢argumentCount > 1) {
            $¢arguments[] = $second;
        }

        for ($i = 2; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = null;

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    private static $_uncallableMethods = [];
    private static $_traitMethods = [];
    private static $_customMethods = [];
    private static $_staticHandle;
    private $_handle;
}
