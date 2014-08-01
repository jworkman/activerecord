<?php

namespace Jworkman\Activerecord\Interfaces;


use Illuminate\Support\Facades\App;

class Encrypt {

    public static function set( $value, $parameters )
    {

        return \Hash::make( $value );

    }

    public static function get( $value, $parameters )
    {

        return $value;

    }

}