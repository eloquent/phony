<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Hash;

/**
 * Generates hashes from arbitrary values.
 *
 * @internal
 */
class HashGenerator implements HashGeneratorInterface
{
    /**
     * Get the static instance of this generator.
     *
     * @return HashGeneratorInterface The static generator.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Generate a hash from an arbitrary value.
     *
     * @param mixed $value The value.
     *
     * @return string The hash.
     */
    public function hash($value)
    {
        ob_start();
        var_dump($value);
        $output = ob_get_contents();
        ob_end_clean();

        return md5($output);
    }

    private static $instance;
}
