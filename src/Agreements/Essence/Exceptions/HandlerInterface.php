<?php

namespace Curfle\Agreements\Essence\Exceptions;

use Curfle\Http\Request;
use Curfle\Http\Response;
use Throwable;

interface HandlerInterface
{
    /**
     * Report or log an exception.
     *
     * @param  Throwable  $e
     * @return void
     *
     * @throws Throwable
     */
    public function report(Throwable $e);

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param Throwable $e
     * @return Response
     *
     * @throws Throwable
     */
    public function render(Request $request, Throwable $e): Response;
}