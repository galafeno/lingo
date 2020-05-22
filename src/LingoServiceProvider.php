<?php

namespace Galafeno\Lingo;

use Galafeno\Lingo\Console\Commands\GenerateLingo;
use Illuminate\Support\ServiceProvider;

class LingoServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->commands([GenerateLingo::class]);
    }

    public function register()
    {
    }
}
