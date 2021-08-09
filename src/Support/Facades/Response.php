<?php

namespace Curfle\Support\Facades;

/**
 * @method static \Curfle\Http\Response setContent(string|array $content)
 * @method static \Curfle\Http\Response setStatusCode(int $status)
 * @method static \Curfle\Http\Response setProtocolVersion(int|float $version)
 * @method static \Curfle\Http\Response setHeader(string $name, string $value)
 * @method static \Curfle\Http\Response setCookie(string $name, string $value)
 * @method static \Curfle\Http\Response send()
 * @method static \Curfle\Http\Response sendHeaders()
 * @method static \Curfle\Http\Response sendContent()
 *
 * @see \Curfle\Http\Response
 */
class Response extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'response';
    }
}