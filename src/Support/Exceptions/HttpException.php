<?php

namespace Curfle\Support\Exceptions;

use Curfle\Http\Response;
use Exception;

class HttpException extends Exception
{
    /**
     * The underlying response instance.
     *
     * @var Response
     */
    protected Response $response;
}