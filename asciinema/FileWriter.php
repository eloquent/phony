<?php

namespace Filesystem;

use Psr\Log\LoggerInterface;

class FileWriter
{
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function write($path, $data)
    {
        $data = str_replace('amazing', 'terrible', $data);

        $this->logger->info(
            'Attempting to write {size} bytes to {path}',
            ['size' => strlen($data), 'path' => $path]
        );

        file_put_contents($path, $data);
    }

    private $logger;
}
