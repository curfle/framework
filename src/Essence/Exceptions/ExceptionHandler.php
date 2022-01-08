<?php

namespace Curfle\Essence\Exceptions;

use Curfle\Agreements\Essence\Exceptions\HandlerInterface;
use Curfle\Http\Request;
use Curfle\Http\Response;
use Curfle\Support\Exceptions\Http\HttpDispatchableException;
use Throwable;

class ExceptionHandler implements HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function report(Throwable $e)
    {
        // TODO: Implement report() method.
    }

    /**
     * @inheritDoc
     */
    public function render(Request $request, Throwable $e): Response
    {
        return $this->prepareResponse($request, $e);
    }

    /**
     * Prepare a response for the given exception.
     *
     * @param Request $request
     * @param Throwable $e
     * @return Response
     */
    private function prepareResponse(Request $request, Throwable $e): Response
    {
        // if debug mode is enabled and exception is not HTTP-dispatchable, convert it into a rich response
        if (config('app.debug') && !$this->isDispatchableHttpException($e)) {
            return $this->convertExceptionToResponse($e);
        }

        // if the exception is not dispatchable but debug mode not enabled, throw an internal server error
        if (!$this->isDispatchableHttpException($e)) {
            $e = new HttpDispatchableException("Internal Server Error", 500);
        }

        // display the dispatchable HTTP exception
        return $this->convertHttpDispatchableExceptionToResponse($e);
    }

    /**
     * Returns whether the exception is a HttpException or not.
     *
     * @param Throwable $e
     * @return bool
     */
    private function isDispatchableHttpException(Throwable $e): bool
    {
        return $e instanceof HttpDispatchableException;
    }

    /**
     * Converts an exception into a response.
     *
     * @param Throwable $e
     * @return Response
     */
    private function convertExceptionToResponse(Throwable $e): Response
    {
        ob_start();
        echo "<pre>";
        var_dump($e);
        echo "</pre>";
        $errorStringified = ob_get_clean();

        return new Response(
            $errorStringified,
            500,
            [
                $_SERVER["SERVER_PROTOCOL"] . " " . Response::HTTP_INTERNAL_SERVER_ERROR . " Internal Server Error",
                Response::HTTP_INTERNAL_SERVER_ERROR
            ]
        );
    }

    /**
     * Converst an HttpDispatchableException into a response.
     *
     * @param HttpDispatchableException $e
     * @return Response
     */
    private function convertHttpDispatchableExceptionToResponse(HttpDispatchableException $e): Response
    {
        $statusText = "<h1>{$e->getCode()} | {$e->getMessage()}</h1>";
        return new Response(
            $statusText,
            $e->getCode(),
            [
                $_SERVER["SERVER_PROTOCOL"] . "{$e->getCode()} " . Response::$statusTexts[$e->getCode()],
                $e->getCode()
            ]
        );
    }
}