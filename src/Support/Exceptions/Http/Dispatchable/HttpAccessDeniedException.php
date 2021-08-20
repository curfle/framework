<?php

namespace Curfle\Support\Exceptions\Http\Dispatchable;

use Curfle\Support\Exceptions\Http\HttpDispatchableException;
use Exception;
use Throwable;

class HttpAccessDeniedException extends HttpDispatchableException
{
    public function __construct(string $message = "Access denied", int $code = 403, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}