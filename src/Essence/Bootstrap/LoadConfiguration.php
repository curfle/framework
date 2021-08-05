<?php

namespace Curfle\Essence\Bootstrap;

use Curfle\Agreements\Essence\Bootstrap\BootstrapInterface;
use Curfle\Config\Repository;
use Curfle\Essence\Application;
use Exception;

class LoadConfiguration implements BootstrapInterface
{

    /**
     * Load the configuration from the project.
     *
     * @inheritDoc
     * @throws Exception
     */
    function bootstrap(Application $app)
    {
        $items = [];

        // create config environment
        $app->instance('config', $config = new Repository($items));

        // load the project's configuration files
        $this->loadConfigurationFiles($app, $config);

        // detect the environment (e.g. production, development, etc.)
        $app->detectEnvironment(function () use ($config) {
            return $config->get('app.env', 'production');
        });

        // load the timezone
        date_default_timezone_set($config->get('app.timezone', 'UTC'));

        // set the encoding
        mb_internal_encoding('UTF-8');
    }

    /**
     * Load the configuration items from all the files.
     *
     * @param  Application  $app
     * @param  Repository  $repository
     * @return void
     *
     * @throws Exception
     */
    protected function loadConfigurationFiles(Application $app, Repository $repository)
    {
        $files = $this->getConfigurationFiles($app);

        if (! isset($files['app'])) {
            throw new Exception('Unable to load the "app.php" configuration file.');
        }

        foreach ($files as $key => $path) {
            $repository->set($key, require $path);
        }
    }

    /**
     * Get all the configuration files for the application.
     *
     * @param  Application  $app
     * @return array
     */
    protected function getConfigurationFiles(Application $app): array
    {
        $files = [];

        $configPath = realpath($app->configPath());

        foreach (glob($configPath.'/*.php') as $file) {
            $files[pathinfo($file, PATHINFO_FILENAME)] = $file;
        }

        ksort($files, SORT_NATURAL);

        return $files;
    }
}