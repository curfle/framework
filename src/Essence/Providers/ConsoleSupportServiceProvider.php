<?php

namespace Curfle\Essence\Providers;

use Curfle\Support\AggregateServiceProvider;

class ConsoleSupportServiceProvider extends AggregateServiceProvider
{
    /**
     * The provider class names.
     *
     * @var string[]
     */
    protected array $providers = [
        BuddyServiceProvider::class
    ];
}