<?php
namespace Jworkman\Activerecord\Validators;
use Illuminate\Support\Facades\DB;

class Unique {

    const REQUIRES_MODEL = true;

    public static function validate( $value, $params = array(), &$model, $property )
    {

        if( !$model->isNew() ) { return true; }

        $modelName = get_class( $model );
        $chain = DB::table( $modelName::TABLE )->where( $property, '=', $value );

        foreach($params as $unique) {
            $chain = $chain->where( $unique, '=', $model->get( $unique ) );
        }

        return ( $chain->count() ) ? false : true;

    }

}