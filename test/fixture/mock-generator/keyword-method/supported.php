<?php

$message = 'Requires the PHP7 amqp extension.';

return class_exists('AMQPQueue');
