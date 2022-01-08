<?php

namespace Curfle\Auth\Guardians;

use Curfle\Auth\JWT\JWT;
use Curfle\Http\Request;
use Curfle\Support\Exceptions\Auth\IncorrectJWTFormatException;
use Curfle\Support\Exceptions\Misc\SecretNotPresentException;
use Curfle\Support\Str;

class JWTGuardian extends Guardian
{
    /**
     * @inheritDoc
     */
    protected array $supported = [
        Guardian::DRIVER_BEARER
    ];

    /**
     * @inheritDoc
     * @throws SecretNotPresentException|IncorrectJWTFormatException
     */
    public function validate(Request $request): bool
    {
        $success = null;

        // validate against bearer authentication
        if ($success === null && $this->supports(self::DRIVER_BEARER)) {
            $token = $request->header("Authorization");
            if ($token !== null) {
                $token = Str::replace($token, "Bearer ", "");
                $success = JWT::valid($token);

                // add authenticated user if JWT is valid
                if ($success)
                    $this->login(JWT::decode($token)["sub"] ?? null);
            }
        }

        return $success ?? false;
    }
}