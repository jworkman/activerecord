<?php

namespace Jworkman\Activerecord\Interfaces;


class Serialize {

    public static function set( $value )
    {

        return serialize( $value );

    }

    public static function get( $value )
    {

        return unserialize( $value );

    }

}