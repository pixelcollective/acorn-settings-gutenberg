<?php

namespace TinyPixel\Settings\Providers;

use \TinyPixel\Settings\Gutenberg;
use \Illuminate\Support\Collection;

use function \Roots\config_path;
use \Roots\Acorn\ServiceProvider;

/**
 * Gutenberg Service Provider
 *
 * @author  Kelly Mears <kelly@tinypixel.dev>
 * @license MIT
 * @since   1.0.0
 */
class GutenbergServiceProvider extends ServiceProvider
{
    /**
      * Register any application services.
      *
      * @return void
      */
    public function register()
    {
        $this->app->singleton('wordpress.gutenberg', function () {
            return new Gutenberg($this->app);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $config = __DIR__ . '/../config/wordpress/gutenberg.php';

        $this->publishes([$config => config_path('wordpress/gutenberg.php')]);

        $this->app->make('wordpress.gutenberg')->init(Collection::make(
            $this->app['config']->get('wordpress.gutenberg')
        ));
    }
}
