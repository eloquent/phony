<?php

class Spy
{
    public function __construct($callback)
    {
        $this->callback = $callback;
        $this->calls = array();
    }

    public function __invoke()
    {
        $arguments = func_get_args();

        $returned = null;
        $thrown = null;
        try {
            $returned = call_user_func_array($this->callback, $arguments);
        } catch (Exception $thrown) {}

        $generatorThings = array();
        $generatorSpy = $this->generatorSpy($returned, $generatorThings);

        $this->calls[] = array($arguments, $returned, $thrown, &$generatorThings);

        if ($thrown) {
            throw $thrown;
        }

        return $generatorSpy;
    }

    private function generatorSpy(Generator $generator, &$generatorThings)
    {
        $send = false;

        while (true) {
            $sentValue = null;
            $sentException = null;
            $thrownException = null;
            $yieldedKey = null;
            $yieldedValue = null;

            try {
                if ($send) {
                    if ($sentException) {
                        $generator->throw($sentException);
                    } else {
                        $generator->send($sentValue);
                    }
                }

                if (!$generator->valid()) {
                    // record clean end ?
                    return;
                }
            } catch (Exception $thrownException) {
                // record failed end
                $generatorThings[] = array('exception', $thrownException);

                return;

            }

            $yieldedKey = $generator->key();
            $yieldedValue = $generator->current();
            // record yielded key & value

            $generatorThings[] = array('yield', $yieldedKey, $yieldedValue);

            try {
                $sentValue = (yield $yieldedKey => $yieldedValue);
                // record sent value
                $generatorThings[] = array('sentValue', $sentValue);
            } catch (Exception $sentException) {
                $generatorThings[] = array('sentException', $sentException);
                // record sent exception
            }

            $send = true;
        }
    }

    public $callback;
    public $calls;
}

$callback = function () {
    yield 0;
    yield 1;
    yield 2;
    try {
        yield 3;
    } catch (Exception $e) {}
    throw new Exception('inside');
};
$spy = new Spy($callback);

$generator = $spy('argumentA', 'argumentB');

try {
    while ($generator->valid()) {
        if ($generator->key() > 2) {
            $generator->throw(new Exception('outside'));
        } else {
            $generator->send($generator->key() * 10);
        }
    }
} catch (Exception $e) {}

printf("Called with %s\n", var_export($spy->calls[0][0], true));
printf("Returned %s\n", var_export($spy->calls[0][1], true));
echo "Generated:\n";

foreach ($spy->calls[0][3] as $generatorThing) {
    switch ($generatorThing[0]) {
        case 'exception':
            printf("    - exception %s(%s)\n", get_class($generatorThing[1]), var_export($generatorThing[1]->getMessage(), true));

            break;

        case 'yield':
            printf("    - yield %s %s\n", var_export($generatorThing[1], true), var_export($generatorThing[2], true));

            break;

        case 'sentValue':
            printf("    - sentValue %s\n", var_export($generatorThing[1], true));

            break;


        case 'sentException':
            printf("    - sentException %s(%s)\n", get_class($generatorThing[1]), var_export($generatorThing[1]->getMessage(), true));

            break;
    }
}
