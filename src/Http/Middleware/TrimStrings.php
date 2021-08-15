<?php

namespace Curfle\Http\Middleware;

use Curfle\Console\Application;
use Curfle\Http\Middleware;
use Curfle\Http\Request;
use Curfle\Http\Response;

class TrimStrings extends Middleware
{
    public static function handle(Request $request)
    {
        foreach($request->inputs() as $name => $input){
            $request->addInput($name, trim($input));
        }
    }
}