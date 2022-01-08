<?php

namespace Curfle\Hash;

use Curfle\Agreements\Hash\HashAlgorithm;
use Curfle\Hash\Algorithm\Argon2i;
use Curfle\Hash\Algorithm\Argon2id;
use Curfle\Hash\Algorithm\BCrypt;
use Curfle\Support\Exceptions\Hash\HashAlgorithmNotFoundException;
use Curfle\Support\Str;

class HashManager
{

    /**
     * @throws HashAlgorithmNotFoundException
     */
    public function algorithm(string $name = null) : HashAlgorithm
    {
        if($name === null)
            $name = config("hashing.driver");

        return match (Str::lower($name)) {
            "bcrypt" => new BCrypt(),
            "argon2i" => new Argon2i(),
            "argon2id" => new Argon2id(),
            default => throw new HashAlgorithmNotFoundException("The algorithm [$name] is not supported by curfle."),
        };
    }


    /**
     * Dynamically pass methods to the default mailer.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     * @throws HashAlgorithmNotFoundException
     */
    public function __call(string $method, array $parameters)
    {
        return $this->algorithm()::$method(...$parameters);
    }
}