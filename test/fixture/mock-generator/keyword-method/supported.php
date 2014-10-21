<?php

$message = 'Requires the amqp extension.';

return class_exists('AMQPQueue');
