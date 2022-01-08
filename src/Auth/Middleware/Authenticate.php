<?php

namespace Curfle\Auth\Middleware;

use Curfle\Http\Middleware;
use Curfle\Http\Request;
use Curfle\Support\Exceptions\Http\Dispatchable\HttpAccessDeniedException;
use Curfle\Support\Facades\Auth;

class Authenticate extends Middleware
{

    /**
     * @inheritDoc
     * @throws HttpAccessDeniedException
     */
    function handle(Request $request)
    {
        // get guardian
        $name = $this->parameter(0);
        $guardian = Auth::guardian($name);

        // validate request with guardian
        if(!$guardian->validate($request))
            throw new HttpAccessDeniedException();
    }
}