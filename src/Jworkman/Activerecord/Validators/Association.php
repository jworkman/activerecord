<?php
namespace Jworkman\Activerecord\Validators;
use Illuminate\Support\Facades\DB;

class Association {

    const REQUIRES_MODEL = true;

    public static function validate( $value, $params = array(), &$model, $property )
    {

        return ($model->$property->count()) ? true : false;

    }

}