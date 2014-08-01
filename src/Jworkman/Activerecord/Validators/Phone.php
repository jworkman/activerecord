<?php
namespace Jworkman\Activerecord\Validators;

class Phone {

    public static function validate( $value, $params = array() )
    {

        if( !Presence::validate($value, array()) ) { return true; }
        $a = preg_replace("/[^0-9]/", '', $value);
        if (strlen($a) == 11) $a = preg_replace("/^1/", '',$a);
        return (strlen($a) == 10) ? true : false;

    }

}