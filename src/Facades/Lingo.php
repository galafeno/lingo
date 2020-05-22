<?php

namespace Galafeno\Lingo\Facades;

class Lingo
{
    public static function __callStatic($name, $args)
    {
        return app()->make("App\\Lingos\\".ucfirst($name)."Lingo");
    }
}
