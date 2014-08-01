<?php

namespace Jworkman\Activerecord\Interfaces;


class TimeStamp {

    public static function set( $value )
    {

        if( gettype( $value ) == "string" ) {
            return $value;
        }

        if( gettype( $value ) == "integer" ) {
            $date = new \DateTime( date( 'Y-m-d H:i:s', $value ) );
            return $date->format('Y-m-d H:i:s');
        }

        if( get_class($value) == "DateTime" ) {
            return $value->format( 'Y-m-d H:i:s' );
        }

        App::abort(500, "Object of type \"" . gettype($value) . "\" passed to TimeStamp filter. Must be type of string or DateTime.");

    }

    public static function get( $value )
    {

        if( gettype( $value ) == "string" ) {
            return new \DateTime( $value );
        }

        if( get_class($value) == "DateTime" ) {
            return $value;
        }

        if( gettype( $value ) == "integer" ) {
            return new \DateTime( date('Y-m-d H:i:s', $value) );
        }

        App::abort(500, "Object of type \"" . gettype($value) . "\" passed to TimeStamp filter. Must be type of string or DateTime.");

    }

}