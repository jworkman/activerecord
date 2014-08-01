<?php

namespace Jworkman\Activerecord\Interfaces;


use Illuminate\Support\Facades\App;

class Boolean {

    public static function set( $value, $parameters )
    {

        return ( $value ) ? 1 : 0;

    }

    public static function get( $value, $parameters )
    {

        return ( $value ) ? true : false;

    }

}