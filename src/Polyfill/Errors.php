<?php

namespace Eloquent\Phony\Polyfill;

class Errors
{
    /**
     * Clear the last runtime error.
     *
     * Prevents feature detection errors from leaking out into shutdown handlers
     * that look at error_get_last().
     *
     * @return void
     */
    public static function errorClearLast()
    {
        // https://github.com/symfony/polyfill/blob/ba249100f5/src/Php70/Php70.php#L52-L61
        set_error_handler([__CLASS__, 'silentHandler']);
        @trigger_error('');
        restore_error_handler();
    }

    /**
     * @return false
     */
    public static function silentHandler()
    {
        return false;
    }
}
