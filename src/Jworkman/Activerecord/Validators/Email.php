<?php
namespace Jworkman\Activerecord\Validators;

class Email {

    public static function validate( $value, $params = array() )
    {

        if( !Presence::validate($value, array()) ) { return true; }
        return (filter_var($value, FILTER_VALIDATE_EMAIL)) ? true : false;

    }

}