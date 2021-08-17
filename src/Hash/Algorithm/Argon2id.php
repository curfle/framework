<?php

namespace Curfle\Hash\Algorithm;

use Curfle\Agreements\Hash\HashAlgorithm as HashAlgorithm;

class Argon2id extends PasswordHashAlgorithm implements HashAlgorithm
{

    /**
     * @inheritDoc
     */
    protected static function getHashAlgorithm(): string
    {
        return PASSWORD_ARGON2ID;
    }

    /**
     * @inheritDoc
     */
    protected static function getHashOptions(): array
    {
        return [
            "memory_cost" => config("hashing.argon.memory", 1024),
            "time_cost" => config("hashing.argon.time", 2),
            "threads" => config("hashing.argon.threads", 2)
        ];
    }
}