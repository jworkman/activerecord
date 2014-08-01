<?php

namespace Jworkman\Activerecord\Interfaces;


use Illuminate\Support\Facades\App;

class UpperCase {

    public static function set( $value, $parameters )
    {

        if( gettype($value) == "string" ) {
            return strtoupper( $value );
        }

        App::abort(500, "Object of type \"" . gettype($value) . "\" passed to upper case filter. Must be type of string.");

    }

    public static function get( $value, $parameters )
    {

        return ( gettype($value) == "string" ) ? strtoupper($value) : $value;

    }

}