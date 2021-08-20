<?php

namespace Curfle\Auth\Middleware;

use Curfle\Agreements\Auth\Guardian;
use Curfle\Console\Application;
use Curfle\Container\Container;
use Curfle\Http\Middleware;
use Curfle\Http\Request;
use Curfle\Support\Exceptions\Auth\GuardianNotFoundException;
use Curfle\Support\Exceptions\Auth\ProvidedGuardianNotGuardianInstance;
use Curfle\Support\Exceptions\Http\Dispatchable\HttpAccessDeniedException;
use Curfle\Support\Exceptions\Misc\BindingResolutionException;
use Curfle\Support\Exceptions\Misc\CircularDependencyException;
use Curfle\Support\Facades\Auth;
use ReflectionException;

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