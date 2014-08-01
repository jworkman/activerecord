<?php

namespace Jworkman\Activerecord\Interfaces;


use Illuminate\Support\Facades\App;

class Integer {

    public static function set( $value, $parameters )
    {

        if( !is_numeric( $value ) )
        {
            App::abort(500, "Object of type \"" . gettype($value) . "\" passed to integer filter. Must be numeric");
        }

        return (int)$value;

    }

    public static function get( $value, $parameters )
    {

        return self::set( $value, $parameters );

    }

}