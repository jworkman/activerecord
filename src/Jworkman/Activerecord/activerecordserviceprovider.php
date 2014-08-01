<?php
namespace Jworkman\Activerecord;

use Illuminate\Support\ServiceProvider;

class ActiverecordServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('jworkman/activerecord');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['activerecord:console'] = $this->app->share(function(){
            return new ActiverecordConsole();
        });

        $this->app['activerecord:model'] = $this->app->share(function(){
            return new ActiverecordModel();
        });

        $this->app['activerecord:scaffold'] = $this->app->share(function(){
            return new ActiverecordScaffold();
        });

        $this->commands(
            array('activerecord:console', 'activerecord:scaffold', 'activerecord:model')
        );
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
