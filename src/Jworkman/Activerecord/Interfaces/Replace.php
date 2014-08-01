<?php

namespace Jworkman\Activerecord\Interfaces;


use Illuminate\Support\Facades\App;

class Replace {

    public static function set( $value, $parameters )
    {

        if( count($parameters) < 2 ) {
            App::abort(500, "Filter of type Replace requires two parameters");
        }

        if( gettype($value) == "string" ) {
            return str_replace( $parameters[0], $parameters[1], $value );
        }

        App::abort(500, "Object of type \"" . gettype($value) . "\" passed to replace filter. Must be type of string.");

    }

    public static function get( $value, $parameters )
    {

        return self::set( $value, $parameters );

    }

}