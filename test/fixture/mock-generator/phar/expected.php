<?php

class MockGeneratorPhar
extends \Phar
implements \Eloquent\Phony\Mock\Mock
{
    public function __construct()
    {
    }

    public function __destruct()
    {
        if (!$this->_handle) {
            parent::__destruct();

            return;
        }

        $this->_handle->spy('__destruct')->invokeWith([]);
    }

    public function addEmptyDir(
        string $directory
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $directory;
        }

        for ($i = 1; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::addEmptyDir(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function addFile(
        string $filename,
        ?string $localName = null
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $filename;
        }
        if ($¢argumentCount > 1) {
            $¢arguments[] = $localName;
        }

        for ($i = 2; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::addFile(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function addFromString(
        string $localName,
        string $contents
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $localName;
        }
        if ($¢argumentCount > 1) {
            $¢arguments[] = $contents;
        }

        for ($i = 2; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::addFromString(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function buildFromDirectory(
        string $directory,
        string $pattern = ''
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $directory;
        }
        if ($¢argumentCount > 1) {
            $¢arguments[] = $pattern;
        }

        for ($i = 2; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::buildFromDirectory(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function buildFromIterator(
        \Traversable $iterator,
        ?string $baseDirectory = null
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $iterator;
        }
        if ($¢argumentCount > 1) {
            $¢arguments[] = $baseDirectory;
        }

        for ($i = 2; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::buildFromIterator(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function compressFiles(
        int $compression
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $compression;
        }

        for ($i = 1; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::compressFiles(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function decompressFiles()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::decompressFiles(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function compress(
        int $compression,
        ?string $extension = null
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $compression;
        }
        if ($¢argumentCount > 1) {
            $¢arguments[] = $extension;
        }

        for ($i = 2; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::compress(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function decompress(
        ?string $extension = null
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $extension;
        }

        for ($i = 1; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::decompress(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function convertToExecutable(
        ?int $format = null,
        ?int $compression = null,
        ?string $extension = null
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $format;
        }
        if ($¢argumentCount > 1) {
            $¢arguments[] = $compression;
        }
        if ($¢argumentCount > 2) {
            $¢arguments[] = $extension;
        }

        for ($i = 3; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::convertToExecutable(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function convertToData(
        ?int $format = null,
        ?int $compression = null,
        ?string $extension = null
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $format;
        }
        if ($¢argumentCount > 1) {
            $¢arguments[] = $compression;
        }
        if ($¢argumentCount > 2) {
            $¢arguments[] = $extension;
        }

        for ($i = 3; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::convertToData(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function copy(
        string $to,
        string $from
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $to;
        }
        if ($¢argumentCount > 1) {
            $¢arguments[] = $from;
        }

        for ($i = 2; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::copy(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function count(
        int $mode = 0
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $mode;
        }

        for ($i = 1; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::count(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function delete(
        string $localName
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $localName;
        }

        for ($i = 1; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::delete(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function delMetadata()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::delMetadata(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function extractTo(
        string $directory,
        array|string|null $files = null,
        bool $overwrite = false
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $directory;
        }
        if ($¢argumentCount > 1) {
            $¢arguments[] = $files;
        }
        if ($¢argumentCount > 2) {
            $¢arguments[] = $overwrite;
        }

        for ($i = 3; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::extractTo(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function getAlias()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::getAlias(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function getPath()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::getPath(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function getMetadata(
        array $unserializeOptions = array (
)
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $unserializeOptions;
        }

        for ($i = 1; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::getMetadata(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function getModified()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::getModified(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function getSignature()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::getSignature(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function getStub()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::getStub(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function getVersion()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::getVersion(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function hasMetadata()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::hasMetadata(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function isBuffering()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::isBuffering(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function isCompressed()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::isCompressed(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function isFileFormat(
        int $format
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $format;
        }

        for ($i = 1; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::isFileFormat(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function isWritable()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::isWritable(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function offsetExists(
        $localName
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $localName;
        }

        for ($i = 1; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::offsetExists(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function offsetGet(
        $localName
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $localName;
        }

        for ($i = 1; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::offsetGet(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function offsetSet(
        $localName,
        $value
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $localName;
        }
        if ($¢argumentCount > 1) {
            $¢arguments[] = $value;
        }

        for ($i = 2; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::offsetSet(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function offsetUnset(
        $localName
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $localName;
        }

        for ($i = 1; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::offsetUnset(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function setAlias(
        string $alias
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $alias;
        }

        for ($i = 1; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::setAlias(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function setDefaultStub(
        ?string $index = null,
        ?string $webIndex = null
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $index;
        }
        if ($¢argumentCount > 1) {
            $¢arguments[] = $webIndex;
        }

        for ($i = 2; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::setDefaultStub(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function setMetadata(
        $metadata
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $metadata;
        }

        for ($i = 1; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::setMetadata(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function setSignatureAlgorithm(
        int $algo,
        ?string $privateKey = null
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $algo;
        }
        if ($¢argumentCount > 1) {
            $¢arguments[] = $privateKey;
        }

        for ($i = 2; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::setSignatureAlgorithm(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function setStub(
        $stub,
        int $length = null
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $stub;
        }
        if ($¢argumentCount > 1) {
            $¢arguments[] = $length;
        }

        for ($i = 2; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::setStub(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function startBuffering()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::startBuffering(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function stopBuffering()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::stopBuffering(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function hasChildren(
        bool $allowLinks = false
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $allowLinks;
        }

        for ($i = 1; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::hasChildren(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function getChildren()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::getChildren(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function getSubPath()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::getSubPath(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function getSubPathname()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::getSubPathname(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function rewind()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::rewind(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function key()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::key(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function current()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::current(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function getFlags()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::getFlags(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function setFlags(
        int $flags
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $flags;
        }

        for ($i = 1; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::setFlags(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function getFilename()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::getFilename(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function getExtension()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::getExtension(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function getBasename(
        string $suffix = ''
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $suffix;
        }

        for ($i = 1; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::getBasename(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function isDot()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::isDot(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function valid()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::valid(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function next()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::next(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function seek(
        int $offset
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $offset;
        }

        for ($i = 1; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::seek(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function __toString() : string
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::__toString(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function getPathname()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::getPathname(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function getPerms()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::getPerms(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function getInode()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::getInode(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function getSize()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::getSize(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function getOwner()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::getOwner(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function getGroup()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::getGroup(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function getATime()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::getATime(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function getMTime()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::getMTime(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function getCTime()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::getCTime(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function getType()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::getType(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function isReadable()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::isReadable(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function isExecutable()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::isExecutable(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function isFile()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::isFile(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function isDir()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::isDir(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function isLink()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::isLink(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function getLinkTarget()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::getLinkTarget(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function getRealPath()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::getRealPath(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function getFileInfo(
        ?string $class = null
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $class;
        }

        for ($i = 1; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::getFileInfo(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function getPathInfo(
        ?string $class = null
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $class;
        }

        for ($i = 1; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::getPathInfo(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function openFile(
        string $mode = 'r',
        bool $useIncludePath = false,
        $context = null
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $mode;
        }
        if ($¢argumentCount > 1) {
            $¢arguments[] = $useIncludePath;
        }
        if ($¢argumentCount > 2) {
            $¢arguments[] = $context;
        }

        for ($i = 3; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::openFile(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function setFileClass(
        string $class = 'SplFileObject'
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $class;
        }

        for ($i = 1; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::setFileClass(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function setInfoClass(
        string $class = 'SplFileInfo'
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $class;
        }

        for ($i = 1; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::setInfoClass(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function __debugInfo()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::__debugInfo(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    private static function _callParentStatic(
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        return parent::$name(...$arguments->all());
    }

    private function _callParent(
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        return parent::$name(...$arguments->all());
    }

    private function _callParentConstructor(
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        parent::__construct(...$arguments->all());
    }

    private static $_uncallableMethods = [];
    private static $_traitMethods = [];
    private static $_customMethods = [];
    private static $_staticHandle;
    private $_handle;
}
