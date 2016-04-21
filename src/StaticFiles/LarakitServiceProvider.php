<?php namespace Larakit\StaticFiles;

use Illuminate\Support\ServiceProvider;

class LarakitServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    public function boot() {
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'larakit.laravel5-larakit-staticfiles');
        $this->mergeConfigFrom(__DIR__ . '/../config/js.php', 'larakit.laravel5-larakit-staticfiles.js');
        $this->mergeConfigFrom(__DIR__ . '/../config/css.php', 'larakit.laravel5-larakit-staticfiles.css');
        $this->publishes([
            __DIR__ . '/../config/base_url.php'  => config_path('/larakit/laravel5-larakit-staticfiles/base_url.php'),
            __DIR__ . '/../config/build_dir.php' => config_path('/larakit/laravel5-larakit-staticfiles/build_dir.php'),
            __DIR__ . '/../config/host.php'      => config_path('/larakit/laravel5-larakit-staticfiles/host.php'),
            __DIR__ . '/../config/version.php'   => config_path('/larakit/laravel5-larakit-staticfiles/version.php'),
            __DIR__ . '/../config/css.php'       => config_path('/larakit/laravel5-larakit-staticfiles/css.php'),
            __DIR__ . '/../config/js.php'        => config_path('/larakit/laravel5-larakit-staticfiles/js.php'),
        ], 'config');

        $this->commands([
            CommandDeploy::class,
        ]);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides() {
        return [];
    }

}
