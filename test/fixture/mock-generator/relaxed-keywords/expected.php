<?php

class MockGeneratorRelaxedKeywords
implements \Eloquent\Phony\Mock\Mock,
           \Eloquent\Phony\Test\TestInterfaceWithKeywordMethods
{
    public function return()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($¢i = 0; $¢i < $¢argumentCount; ++$¢i) {
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

    public function throw()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($¢i = 0; $¢i < $¢argumentCount; ++$¢i) {
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
  'return' => true,
);
    private static $_traitMethods = [];
    private static $_customMethods = [];
    private static $_staticHandle;
    private $_handle;
}
