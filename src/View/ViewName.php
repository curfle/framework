<?php

namespace Curfle\View;

use Curfle\Essence\Application;
use Curfle\Support\Facades\App;

class ViewName
{
    private static Application $app;

    /**
     * Sets the application instance.
     *
     * @param Application $app
     */
    public static function setApplicationInstance(Application $app): void
    {
        self::$app = $app;
    }

    /**
     * Takes a view name and returns a full qualified path in the resources/views directory.
     *
     * @param string $name
     * @return string
     */
    public static function normalize(string $name): string
    {
        // replace "." with "/"
        $name = str_replace(".", "/", $name);

        // add ".php" extentension to file
        if(!str_contains($name, ".php"))
            $name .= ".php";

        // get full qualified path
        return static::$app->resourcePath("views/$name");
    }
}