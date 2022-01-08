<?php

namespace Curfle\Http\Middleware;

use Curfle\Http\Middleware;
use Curfle\Http\Request;
use Curfle\Http\Response;
use Curfle\Support\Exceptions\Http\StatusNotFoundException;

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

    /**
     * Handles the incoming requests and adds the according CORS headers to the response.
     * Preflight requests get handled, as all requests using the OPTIONS method receive a
     * response that is sent immediatly. After that the runtime is exited.
     *
     * @param Request $request
     * @throws StatusNotFoundException
     */
    public function handle(Request $request)
    {
        // Access-Control-Allow-Origin
        $this->response->setHeader(
            "Access-Control-Allow-Origin",
            config("cors.access_control_allow_origin", "*")
        );

        // Access-Control-Allow-Headers
        $this->response->setHeader(
            "Access-Control-Allow-Headers",
            config("cors.access_control_allow_headers", "*")
        );

        // handle preflight requests
        if($request->method() === "OPTIONS"){
            $this->response->sendHeaders();
            exit();
        }
    }
}