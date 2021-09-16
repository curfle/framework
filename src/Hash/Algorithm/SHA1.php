<?php

namespace Curfle\Hash\Algorithm;

use Curfle\Agreements\Hash\HashAlgorithm as HashAlgorithm;

class SHA1 implements HashAlgorithm
{

    /**
     * Hashes a string with the SHA1 algorithm.
     *
     * @param string $string
     * @param bool $binary
     * @return string
     */
    public static function hash(string $string, bool $binary = false): string
    {
        return hash("sha1", $string, $binary);
    }
}