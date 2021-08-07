<?php

namespace Curfle\Essence\Exceptions;

use Curfle\Agreements\Essence\Exceptions\HandlerInterface;
use Curfle\Http\Request;
use Curfle\Http\Response;
use Curfle\Support\Exceptions\Http\HttpException;
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
        if (!$this->isHttpException($e) && config('app.debug')) {
            return $this->convertExceptionToResponse($e);
        }

        if (!$this->isHttpException($e)) {
            $e = new HttpException($e->getMessage(), 500);
        }

        return $this->convertHttpExceptionToResponse($e);
    }

    /**
     * Returns whether the exception is a HttpException or not.
     *
     * @param Throwable $e
     * @return bool
     */
    private function isHttpException(Throwable $e): bool
    {
        return $e instanceof HttpException;
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
            $e->getCode(),
            [
                $_SERVER["SERVER_PROTOCOL"] . " " . Response::HTTP_INTERNAL_SERVER_ERROR . " Internal Server Error",
                Response::HTTP_INTERNAL_SERVER_ERROR
            ]
        );
    }

    /**
     * Converst a HTTP exception into a response.
     *
     * @param Throwable $e
     * @return Response
     */
    private function convertHttpExceptionToResponse(Throwable $e): Response
    {
        $status = $e->getCode();
        $statusText = "<h1>$status | " . Response::$statusTexts[$status] . "</h1>";
        return new Response(
            $statusText,
            $status,
            [
                $_SERVER["SERVER_PROTOCOL"] . " $status $statusText",
                $status
            ]
        );
    }
}