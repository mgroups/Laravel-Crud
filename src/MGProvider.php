<?php

namespace MGroups\MGcrud;

use Illuminate\Support\ServiceProvider;
use MGroups\MGcrud\cmds\MGCommand;

class MGProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MGCommand::class
            ]);
        }
    }
}
