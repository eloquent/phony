<?php

class MockGeneratorParameterConstant
implements \Eloquent\Phony\Mock\Mock
{
    public function methodA(
        $first = 1
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $first;
        }

        for ($i = 1; $i < $¢argumentCount; ++$i) {
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
