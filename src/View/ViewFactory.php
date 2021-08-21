<?php

namespace Curfle\View;

use Curfle\Essence\Application;

class ViewFactory
{

    /**
     * The application instance.
     *
     * @var Application
     */
    private Application $app;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Creates a new view.
     * 
     * @param string $view
     * @param array $data
     * @return View
     */
    public function make(string $view, array $data = []): View
    {
        return new View($this->app, $view, $data);
    }
}