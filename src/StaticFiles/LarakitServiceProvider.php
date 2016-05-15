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
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'larakit.lk-staticfiles');
        $this->mergeConfigFrom(__DIR__ . '/../config/js.php', 'larakit.lk-staticfiles.js');
        $this->mergeConfigFrom(__DIR__ . '/../config/css.php', 'larakit.lk-staticfiles.css');
        $this->publishes([
            __DIR__ . '/../config/base_url.php'  => config_path('/larakit/lk-staticfiles/base_url.php'),
            __DIR__ . '/../config/build_dir.php' => config_path('/larakit/lk-staticfiles/build_dir.php'),
            __DIR__ . '/../config/host.php'      => config_path('/larakit/lk-staticfiles/host.php'),
            __DIR__ . '/../config/css.php'       => config_path('/larakit/lk-staticfiles/css.php'),
            __DIR__ . '/../config/js.php'        => config_path('/larakit/lk-staticfiles/js.php'),
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
