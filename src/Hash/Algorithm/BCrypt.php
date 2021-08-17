<?php

namespace Curfle\Hash\Algorithm;

use Curfle\Agreements\Hash\Algorithm as HashAlgorithm;

class BCrypt extends PasswordAlgorithm implements HashAlgorithm
{

    /**
     * @inheritDoc
     */
    protected static function getHashAlgorithm(): string
    {
        return PASSWORD_BCRYPT;
    }

    /**
     * @inheritDoc
     */
    protected static function getHashOptions(): array
    {
        return [
            "cost" => 12
        ];
    }
}