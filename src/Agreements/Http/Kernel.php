<?php

namespace Curfle\Agreements\Http;

use Curfle\Essence\Application;
use Curfle\Http\Request;
use Curfle\Http\Response;

interface Kernel
{
    /**
     * Bootstrap the application for HTTP requests.
     *
     * @return void
     */
    public function bootstrap();

    /**
     * Handle an incoming HTTP request.
     *
     * @param Request $request
     * @return Response
     */
    public function handle(Request $request): Response;

    /**
     * Get the Curfle application instance.
     *
     * @return Application
     */
    public function getApplication(): Application;
}