<?php

namespace Curfle\Http\Middleware;

use Curfle\Console\Application;
use Curfle\Http\Middleware;
use Curfle\Http\Request;
use Curfle\Http\Response;
use Curfle\Support\Str;

class TrimStrings extends Middleware
{
    public function handle(Request $request)
    {
        foreach($request->inputs() as $name => $input){
            if(is_string($input))
                $request->addInput($name, Str::trim($input));
        }
    }
}