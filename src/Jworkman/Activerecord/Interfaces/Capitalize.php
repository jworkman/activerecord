<?php

namespace Jworkman\Activerecord\Interfaces;


use Illuminate\Support\Facades\App;

class Capitalize {

    public static function set( $value, $parameters )
    {

        if( gettype($value) == "string" ) {
            if( isset($parameters[0]) && $parameters[0] === "true" ) {
                return ucwords( $value );
            }
            return ucfirst( $value );
        }

        App::abort(500, "Object of type \"" . gettype($value) . "\" passed to capitalize filter. Must be type of string.");

    }

    public static function get( $value, $parameters )
    {

        return self::set( $value, $parameters );

    }

}