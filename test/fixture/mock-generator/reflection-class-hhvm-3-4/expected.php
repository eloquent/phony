<?php

class MockGeneratorReflectionClassHhvm3_4
extends \ReflectionClass
implements \Eloquent\Phony\Mock\MockInterface
{
    public static function export(
        $a0,
        $a1 = false
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) $arguments[] = $a0;
        if ($argumentCount > 1) $arguments[] = $a1;

        for ($i = 2; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return self::$_staticProxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function __construct()
    {
    }

    public function serialize()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function unserialize(
        $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) $arguments[] = $a0;

        for ($i = 1; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function __toString()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function getName()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function inNamespace()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function getNamespaceName()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function getShortName()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function isInternal()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function isUserDefined()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function isInstantiable()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function isCloneable()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function getMethod(
        $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) $arguments[] = $a0;

        for ($i = 1; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function hasMethod(
        $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) $arguments[] = $a0;

        for ($i = 1; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function getMethods(
        $a0 = null
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) $arguments[] = $a0;

        for ($i = 1; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function hasConstant(
        $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) $arguments[] = $a0;

        for ($i = 1; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function getConstant(
        $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) $arguments[] = $a0;

        for ($i = 1; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function getConstants()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function getInterfaceNames()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function getInterfaces()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function getRequirementNames()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function getRequirements()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function getTraitNames()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function getTraitAliases()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function getTraits()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function isInterface()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function isAbstract()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function isFinal()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function isTrait()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function getModifiers()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function isInstance(
        $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) $arguments[] = $a0;

        for ($i = 1; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function getConstructor()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function newInstance(
        $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) $arguments[] = $a0;

        for ($i = 1; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function newInstanceArgs(
        $a0 = array (
  )
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) $arguments[] = $a0;

        for ($i = 1; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function newInstanceWithoutConstructor()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function getProperty(
        $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) $arguments[] = $a0;

        for ($i = 1; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function hasProperty(
        $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) $arguments[] = $a0;

        for ($i = 1; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function getProperties(
        $a0 = 65535
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) $arguments[] = $a0;

        for ($i = 1; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function getStaticProperties()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function getStaticPropertyValue(
        $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) $arguments[] = $a0;

        for ($i = 1; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function setStaticPropertyValue(
        $a0,
        $a1
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) $arguments[] = $a0;
        if ($argumentCount > 1) $arguments[] = $a1;

        for ($i = 2; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function getDefaultProperties()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function getExtension()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function getExtensionName()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function isIterateable()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function implementsInterface(
        $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) $arguments[] = $a0;

        for ($i = 1; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function getParentClass()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function isSubclassOf(
        $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) $arguments[] = $a0;

        for ($i = 1; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function getFileName()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function getStartLine()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function getEndLine()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function getDocComment()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function getAttribute(
        $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) $arguments[] = $a0;

        for ($i = 1; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function getAttributes()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function getAttributeRecursive(
        $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) $arguments[] = $a0;

        for ($i = 1; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function getAttributesRecursive()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
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

    private static $_uncallableMethods = array();
    private static $_traitMethods = array();
    private static $_customMethods = array();
    private static $_staticProxy;
    private $_proxy;
}
