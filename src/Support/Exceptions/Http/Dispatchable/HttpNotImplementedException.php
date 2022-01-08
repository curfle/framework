<?php

namespace Curfle\Support\Exceptions\Http\Dispatchable;

use Curfle\Support\Exceptions\Http\HttpDispatchableException;
use Throwable;

class HttpNotImplementedException extends HttpDispatchableException
{
    public function __construct(string $message = "Not Implemented", int $code = 501, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}