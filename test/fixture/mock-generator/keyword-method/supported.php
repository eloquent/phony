<?php

$message = 'Requires the PHP7 amqp extension.';

return class_exists('AMQPQueue') && !version_compare(PHP_VERSION, '7.x', '<');
