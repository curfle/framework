<?php

namespace Curfle\Http\Middleware;

use Curfle\Console\Application;
use Curfle\Http\Middleware;
use Curfle\Http\Request;
use Curfle\Http\Response;

class AllowCors extends Middleware
{
    public static function handle(Response $response)
    {
        $response->setHeader(
            "Access-Control-Allow-Origin",
            config("cors.access_control_allow_origin", "*")
        );
    }
}