<?php
namespace Jworkman\Activerecord\Validators;

class Numeric {

    public static function validate( $value, $params = array() )
    {

        return ( isset($value) && !is_null($value) && is_numeric($value) ) ? true : false;

    }

}