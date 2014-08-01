<?php

namespace Jworkman\Activerecord\Interfaces;


class JSON {

    public static function set( $value )
    {

        if( gettype($value) == "string" ) {
            return $value;
        }
        return json_encode( $value );

    }

    public static function get( $value )
    {

        if( gettype($value) != "string" ) {
            return $value;
        }
        return json_decode( $value );

    }

}