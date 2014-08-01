<?php
namespace Jworkman\Activerecord\Validators;

use Illuminate\Support\Facades\App;

class Custom {

    const REQUIRES_MODEL = true;

    public static function validate( $value, $params = array(), $model, $property = null )
    {

        if(count($params) == 0) {
            App::abort(500, "At least one parameter is required for custom validator.");
        }

        $customMethod = $params[0];

        if( !method_exists( $model, $customMethod ) ) {
            App::abort(500, "Custom validator method \"".$customMethod."\" not defined in model.");
        }

        return $model->$customMethod();

    }

}