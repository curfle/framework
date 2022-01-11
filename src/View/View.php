<?php

namespace Curfle\View;

use Curfle\Essence\Application;
use Curfle\Support\Str;

class View
{

    /**
     * The application instance.
     *
     * @var Application
     */
    private Application $app;

    /**
     * Path to the view file.
     *
     * @var string
     */
    private string $path;

    /**
     * Data passed to the view during rendering.
     *
     * @var array
     */
    private array $data;

    /**
     * @param Application $app
     * @param string $path
     * @param array $data
     */
    public function __construct(Application $app, string $path, array $data = [])
    {
        $this->app = $app;
        $this->path = $path;
        $this->data = $data;
    }


    /**
     * Renders a view and returns the result.
     *
     * @return string
     */
    public function render(): string
    {
        $filecontent = $this->app->make("files")->get(ViewName::normalize($this->path));

        // replace variables
        // TODO: add templating engine
        foreach ($this->data as $name => $value) {
            $filecontent = Str::replace($filecontent, "{{{$name}}}", $value);
            $filecontent = Str::replace($filecontent, "{{ {$name} }}", $value);
        }

        return $filecontent;
    }
}