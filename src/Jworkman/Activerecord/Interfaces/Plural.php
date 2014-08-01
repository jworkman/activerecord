<?php

namespace Jworkman\Activerecord\Interfaces;


use Illuminate\Support\Facades\App;

class Plural {

    public static function set( $value, $parameters )
    {

        if( gettype($value) == "string" ) {
            return str_plural( $value );
        }

        App::abort(500, "Object of type \"" . gettype($value) . "\" passed to plural filter. Must be type of string.");

    }

    public static function get( $value, $parameters )
    {

        return self::set( $value, $parameters );

    }

}