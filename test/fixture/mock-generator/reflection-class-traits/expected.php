<?php

class MockGeneratorReflectionClassTraits
extends \ReflectionClass
implements \Eloquent\Phony\Mock\MockInterface
{
    public static function export(
        $a0,
        $a1 = null
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }
        if ($argumentCount > 1) {
            $arguments[] = $a1;
        }

        for ($i = 2; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = self::$_staticProxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function __construct()
    {
    }

    public function __toString()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function getName()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function isInternal()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function isUserDefined()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function isInstantiable()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function isCloneable()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function getFileName()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function getStartLine()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function getEndLine()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function getDocComment()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function getConstructor()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function hasMethod(
        $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }

        for ($i = 1; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function getMethod(
        $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }

        for ($i = 1; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function getMethods(
        $a0 = null
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }

        for ($i = 1; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function hasProperty(
        $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }

        for ($i = 1; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function getProperty(
        $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }

        for ($i = 1; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function getProperties(
        $a0 = null
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }

        for ($i = 1; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function hasConstant(
        $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }

        for ($i = 1; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function getConstants()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function getConstant(
        $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }

        for ($i = 1; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function getInterfaces()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function getInterfaceNames()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function isInterface()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function getTraits()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function getTraitNames()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function getTraitAliases()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function isTrait()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function isAbstract()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function isFinal()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function getModifiers()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function isInstance(
        $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }

        for ($i = 1; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function newInstance(
        $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }

        for ($i = 1; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function newInstanceWithoutConstructor()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function newInstanceArgs(
        array $a0 = null
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }

        for ($i = 1; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function getParentClass()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function isSubclassOf(
        $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }

        for ($i = 1; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function getStaticProperties()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function getStaticPropertyValue(
        $a0,
        $a1 = null
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }
        if ($argumentCount > 1) {
            $arguments[] = $a1;
        }

        for ($i = 2; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function setStaticPropertyValue(
        $a0,
        $a1
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }
        if ($argumentCount > 1) {
            $arguments[] = $a1;
        }

        for ($i = 2; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function getDefaultProperties()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function isIterateable()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function implementsInterface(
        $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }

        for ($i = 1; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function getExtension()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function getExtensionName()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function inNamespace()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function getNamespaceName()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function getShortName()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    private static function _callParentStatic(
        $name,
        \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments
    ) {
        return \call_user_func_array(
            array(__CLASS__, 'parent::' . $name),
            $arguments->all()
        );
    }

    private function _callParent(
        $name,
        \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments
    ) {
        return \call_user_func_array(
            array($this, 'parent::' . $name),
            $arguments->all()
        );
    }

    private function _callParentConstructor(
        \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments
    ) {
        \call_user_func_array(
            array($this, 'parent::__construct'),
            $arguments->all()
        );
    }

    private static $_uncallableMethods = array();
    private static $_traitMethods = array();
    private static $_customMethods = array();
    private static $_staticProxy;
    private $_proxy;
}
