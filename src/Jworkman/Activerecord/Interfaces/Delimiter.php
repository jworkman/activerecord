<?php

namespace Jworkman\Activerecord\Interfaces;


use Illuminate\Support\Facades\App;

class Delimiter {

    public static function set( $value, $parameters )
    {

        switch( strtolower( gettype($value) ) ) {

            case "string":
                return $value;
                break;

            case "array":
                return implode(((isset($parameters[0])) ? $parameters[0] : ','), $value);
                break;

        }

        App::abort( 500, 'Interface of type "Delimiter" was handed an incorect value of type "' + gettype($value) + '"' );

    }

    public static function get( $value, $parameters )
    {

        switch( strtolower( gettype($value) ) ) {

            case "string":
                return explode(((isset($parameters[0])) ? $parameters[0] : ','), $value);
                break;

            case "array":
                return $value;
                break;

        }

        App::abort( 500, 'Interface of type "Delimiter" was handed an incorect value of type "' + gettype($value) + '"' );

    }

}