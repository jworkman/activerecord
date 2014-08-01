<?php

namespace Jworkman\Activerecord\Interfaces;


use Illuminate\Support\Facades\App;

class LowerCase {

    public static function set( $value, $parameters )
    {

        if( gettype($value) == "string" ) {
            return strtolower( $value );
        }

        App::abort(500, "Object of type \"" . gettype($value) . "\" passed to lower case filter. Must be type of string.");

    }

    public static function get( $value, $parameters )
    {

        return ( $value ) ? true : false;

    }

}