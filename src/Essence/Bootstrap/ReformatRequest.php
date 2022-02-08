<?php

namespace Curfle\Essence\Bootstrap;

use Curfle\Agreements\Essence\Bootstrap\BootstrapInterface;
use Curfle\Essence\Application;
use Curfle\Support\Env\Env;
use Curfle\Support\Exceptions\Misc\CircularDependencyException;
use Curfle\Support\Facades\App;
use Curfle\Support\Str;

class ReformatRequest implements BootstrapInterface
{

    /**
     * @inheritDoc
     * @throws CircularDependencyException
     */
    function bootstrap(Application $app)
    {
        // as curfle can be used inside subfolders a specific part of an url might not be wanted in the
        // request uri contained in the request. For example if the curfle project lives under /auth/ a
        // /auth/... suffix is always present but may not be wanted while resolving routes. For that reason
        // we need to remove this part of the request uri to use our routes properly.

        $appUrl = Env::get("APP_URL", "");

        // get the suffix from the APP_URL - e.g. "/auth" from "https://curfle.org/auth"
        $suffix = preg_replace(
            "/https?:\/\/(www\.)?([-a-zA-Z0-9@:%._+~#=]{1,256}\.[a-zA-Z0-9()]{1,63}\b|localhost|127\.0\.0\.1):?[0-9]{0,5}/",
            "",
            $appUrl
        );

        $request = App::resolve("request");
        $request->setUri(
            Str::replace($request->uri(), $suffix, "")
        );
    }
}