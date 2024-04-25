<?php

namespace App\Providers;

use App\Support\OpenAI;
use App\Support\PushDeer;
use Illuminate\Container\Container;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Traits\Conditionable;

class ExtendServiceProvider extends ServiceProvider
{
    use Conditionable {
        Conditionable::when as whenever;
    }

    /**
     * All of the container bindings that should be registered.
     *
     * @var array<string, string>
     */
    public array $bindings = [];

    /**
     * All of the container singletons that should be registered.
     *
     * @var array<array-key, string>
     */
    public array $singletons = [];

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->registerOpenAI();
        $this->registerPushDeer();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Get the events that trigger this service provider to register.
     *
     * @return string[]
     */
    public function when()
    {
        return [];
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return [
            OpenAI::class, 'openai',
            PushDeer::class, 'pushdeer',
        ];
    }

    protected function registerOpenAI(): void
    {
        $this->app->singleton(OpenAI::class, static fn (Application $application): \App\Support\OpenAI => new OpenAI($application['config']['services.openai']));
        $this->app->alias(OpenAI::class, 'openai');
    }

    protected function registerPushDeer(): void
    {
        $this->app->singleton(PushDeer::class, static fn (Application $application): \App\Support\PushDeer => new PushDeer($application['config']['services.pushdeer']));
        $this->app->alias(PushDeer::class, 'pushdeer');
    }
}
