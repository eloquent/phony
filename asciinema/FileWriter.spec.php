<?php

namespace Filesystem;

use function Eloquent\Phony\mock;
use function Eloquent\Phony\restoreGlobalFunctions;
use function Eloquent\Phony\stubGlobal;
use Psr\Log\LoggerInterface;

describe('FileWriter', function () {
    beforeEach(function () {
        $this->filePutContents = stubGlobal('file_put_contents', __NAMESPACE__);
        $this->logger = mock(LoggerInterface::class);

        $this->writer = new FileWriter($this->logger->mock());
    });

    afterEach(function () {
        restoreGlobalFunctions();
    });

    it('writes files', function () {
        $this->writer->write('/path/to/file', 'Phony is amazing!');

        $this->filePutContents
            ->calledWith('/path/to/file', 'Phony is amazing!');
    });
});
