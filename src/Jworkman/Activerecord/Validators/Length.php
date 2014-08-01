<?php
namespace Jworkman\Activerecord\Validators;

class Length {

    public static function validate( $value, $params = array() )
    {


        if(count($params) > 1 && strlen($value) > (int)$params[1]) {
            return false;
        }

        return ( strlen($value) < (int)$params[0] ) ? false : true;

    }

}