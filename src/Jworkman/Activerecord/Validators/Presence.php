<?php
namespace Jworkman\Activerecord\Validators;

class Presence {

    public static function validate( $value, $params = array() )
    {

        return ( isset($value) && !empty($value) && !is_null($value) ) ? true : false;

    }

}