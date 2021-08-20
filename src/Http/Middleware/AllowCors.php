<?php

namespace Curfle\Http\Middleware;

use Curfle\Console\Application;
use Curfle\Http\Middleware;
use Curfle\Http\Request;
use Curfle\Http\Response;

class AllowCors extends Middleware
{
    /**
     * Global response for the request.
     *
     * @var Response
     */
    private Response $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    public function handle(Request $request)
    {
        $this->response->setHeader(
            "Access-Control-Allow-Origin",
            config("cors.access_control_allow_origin", "*")
        );
    }
}