<?php

namespace Jworkman\Activerecord\Interfaces;


use Illuminate\Support\Facades\App;

class Concat {

    public static function set( $value, $parameters )
    {

        if( count($parameters) < 1 ) {
            App::abort(500, "Filter of type Concat requires at least one parameter");
        }

        $returnStr = $value;

        foreach($parameters as $p) {
            $returnStr .= $p;
        }

        return $returnStr;

    }

    public static function get( $value, $parameters )
    {

        return $value;

    }

}