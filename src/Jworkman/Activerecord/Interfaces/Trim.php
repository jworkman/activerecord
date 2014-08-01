<?php

namespace Jworkman\Activerecord\Interfaces;


use Illuminate\Support\Facades\App;

class Trim {

    public static function set( $value, $parameters )
    {

        if( gettype($value) == "string" ) {
            return trim( $value );
        }

        App::abort(500, "Object of type \"" . gettype($value) . "\" passed to trim filter. Must be type of string.");

    }

    public static function get( $value, $parameters )
    {

        return self::set( $value, $parameters );

    }

}