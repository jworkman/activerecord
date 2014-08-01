<?php
/**
 * Created by PhpStorm.
 * User: Justin Workman
 * Date: 5/25/14
 * Time: 10:56 PM
 */

namespace Jworkman\Activerecord;


use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Jworkman\Activerecord\Proxies\BelongsToProxy;
use Jworkman\Activerecord\Proxies\HasManyProxy;
use Whoops\Handler\Handler;

class Library {

    public static function updateModelFromAttributes( $model, $attributes )
    {

        foreach($attributes as $key => $value)
        {
            if( !self::canMassAssign($model->_getAttrAccessible(), $key) ) {

                App::abort(401,
                    "Attempted to assign a value to an attribute of type locked. Attribute \""
                    . $key .
                    "\" is not defined in the attributes accessible list"
                );
            }

            $model = self::assignModelValue( $model, $key, $value );
        }

        return $model;

    }

    public static function saveModel( &$model, $feedback )
    {

        // Run validators
        if( !$model->isValid() )
        {
            return false;
        }


        // If there are any before save handlers
        if( method_exists( $model, 'beforeSave' ) )
            $model->beforeSave();

        $modelName  = get_class($model);
        $pkName     = $modelName::TABLE_PK;

        if( isset( $model->$pkName ) && !is_null($model->$pkName) && !empty($model->$pkName) ) {
            DB::table( $modelName::TABLE )->where( $pkName, $model->$pkName )->update( $model->toArray() );
        } else {
            $id = DB::table( $modelName::TABLE )->insertGetId( $model->toArray() );
            $model->$pkName = $id;
        }

        //If there are any after save handlers
        if( method_exists( $model, 'afterSave' ) )
            $model->afterSave();

        if( $feedback ) {
            return ( isset( $model->$pkName ) && !is_null($model->$pkName) && !empty($model->$pkName) ) ? true : false;
        }

        return $model;

    }

    public static function findOrCreateModel( $modelName, $pk )
    {

        if(!$pk)
            return self::getNewModelFromName( $modelName );

        $model = $modelName::find( $pk );

        if(!$model) {
            return self::getNewModelFromName( $modelName );
        }

        return $model;

    }

    public static function getNewModelFromName( $modelName, $assignAttrs = array() )
    {

        $model = new $modelName();

        if( count($assignAttrs) > 0 )
            return self::updateModelFromAttributes( $model, $assignAttrs );

        return $model;

    }

    public static function buildNewModel($modelName, $assignAttrs = array() )
    {

        $model = new $modelName();
        foreach($assignAttrs as $key => $value) {
            $model->$key = $value;
        }
        return $model;

    }

    public static function getSQLResults($sql, $params)
    {

        return DB::select( $sql, $params );

    }

    public static function getModelsFromPK($modelName, $pk)
    {

        $pkColumn = self::getPKOfModelName($modelName);

        if(count($pk) > 0) {
            $chain = DB::table( $modelName::TABLE )->where( $pkColumn, '=', $pk[0] );
        }

        for($i = 1, $j = count($pk); $i < $j; $i++) {
            $chain->orWhere( $pkColumn, '=', $pk[$i] );
        }

        return new Collection( $modelName, $chain );

    }

    public static function getModelFromPK( $modelName, $pk )
    {

        if(gettype($pk) == 'array') {
            return self::getModelsFromPK( $modelName, $pk );
        }

        $results = DB::table( $modelName::TABLE )->where( self::getPKOfModelName( $modelName ), $pk )->get();

        if(!$results || count($results) <= 0)
            return null;

        return self::buildNewModel(
            $modelName,
            $results[0]
        );

    }

    public static function examineCLIObject( $obj )
    {

        switch( strtolower( gettype( $obj ) ) ) {
            case "string":
                return TerminalColor::init()->getColoredString(" String(\"" . $obj . "\") ", 'white', 'cyan');
                break;
            case "integer":
            case "int":
                return TerminalColor::init()->getColoredString(" Integer(". (String)$obj .") ", 'white', 'cyan');
                break;
            case "object":
                return  TerminalColor::init()->getColoredString(self::dumpObjectCLI( $obj ), 'light_red');
                break;
            default:
                return  TerminalColor::init()->getColoredString(ucfirst(gettype($obj)), 'light_red');
                break;
        }

    }

    public static function dumpObjectCLI( $obj )
    {

        $returnStr = '';

        if( $obj instanceof \Jworkman\Activerecord\Model ) {

            foreach( $obj->toArray() as $key => $value ) {
                $returnStr .=  "\r\n\t" . $key . ' = ' . $value;
            }

            return $returnStr;

        } elseif( $obj instanceof \Jworkman\Activerecord\Collection ) {

            foreach($obj->each as $model) {
                $returnStr .= self::dumpObjectCLI( $model );
            }
            return '- ' . count( $obj->each ) . " records in collection.\r\n" . $returnStr;

        }

        return  TerminalColor::init()->getColoredString( ucfirst(get_class($obj)), 'light_red' );

    }

    public static function getPKOfModelName($modelName)
    {

        return ( defined( $modelName."::PRIMARY_KEY" ) ) ? $modelName::PRIMARY_KEY : "id";

    }

    public static function canMassAssign( $accessible, $attribute )
    {

        return ( in_array($attribute, $accessible) ) ? true : false;

    }

    public static function assignModelValue( &$model, $attribute, $value )
    {

        if( !self::modelPassesValidation( $model->_getValidates(), $attribute, $value ) )
            throw new ActiverecordException("Invalid value attempted to be assigned to " . $attribute);

        $model->set($attribute, $value);
        return $model;

    }

    public static function modelPassesValidation( $rules, $attribute, $value )
    {

        if(!array_key_exists($attribute, $rules)) { return true; }

        return true;

    }

    public static function getLastModelByName( $modelName, $amount = 1, $by = null )
    {


        if(is_null($by))
            $by = self::getPKOfModelName( $modelName );


        if($amount > 1) {
            return new Collection( $modelName, DB::table( $modelName::TABLE )->orderBy( $by, 'DESC' )->take( $amount ) );
        }

        $results = DB::table( $modelName::TABLE )->orderBy( $by, 'DESC' )->take( 1 )->get();

        if(!$results || count($results) <= 0)
            return null;

        return self::buildNewModel(
            $modelName,
            $results[0]
        );

    }

    public static function getFirstModelByName( $modelName, $amount = 1, $by = null )
    {

        if(is_null($by))
            $by = self::getPKOfModelName( $modelName );

        $results = DB::table( $modelName::TABLE )->orderBy( $by, 'ASC' )->take( $amount )->get();

        if(!$results || count($results) <= 0)
            return null;

        return self::buildNewModel(
            $modelName,
            $results[0]
        );

    }


    public static function getAllModels( $modelName )
    {
        return new \Jworkman\Activerecord\Collection( $modelName, DB::table( $modelName::TABLE ) );
    }

    public static function getHasManyRelationship( $parentModel, $key, $config = array() )
    {

        $parentModelName        = get_class( $parentModel );
        $parentTable            = $parentModelName::TABLE;
        $parentPK               = $parentModelName::TABLE_PK;
        $config['from']         = (isset($config['from'])) ? $config['from'] : ucfirst( $key );
        $config['through']      = (isset($config['through'])) ? $config['through'] : "";
        $config['foreign_key']  = (isset($config['foreign_key'])) ? $config['foreign_key'] : strtolower( str_singular( $parentTable ) ) . "_id";
        $childModelName         = $config['from'];
        $childTable             = $childModelName::TABLE;
        $childPK                = $childModelName::TABLE_PK;
        $config['order']        = (isset($config['order'])) ? $childTable . '.' . $config['order'] : $childTable . '.' . $childPK;


        return new \Jworkman\Activerecord\Collection(

            $config['from'],

            \Jworkman\Activerecord\Proxies\HasManyProxy::init()
                ->parentTable( $parentTable )
                ->parentPK( $parentPK )
                ->childTable( $childTable )
                ->childPK( $childPK )
                ->through( $config['through'] )
                ->from( $childModelName )
                ->fk( $config['foreign_key'] )
                ->order( $config['order'] )
                ->getChain( $parentModel->$parentPK )

        );

    }

    public static function buildArrayFromPropterties( &$obj, $avoid = array() )
    {

        $returnArray = array();

        $reflect = new \ReflectionObject( $obj );

        foreach($reflect->getProperties( \ReflectionProperty::IS_PUBLIC ) as $property) {

            $p = $property->getName();

            if(in_array( $p, $avoid )) { continue; }

            $returnArray[ $p ] = $obj->$p;

        }

        return $returnArray;

    }

    public static function getBelongsToRelationship( $childModel, $key, $config )
    {

        $config['from']         = (isset($config['from'])) ? $config['from'] : ucfirst( $key );
        $config['through']      = (isset($config['through'])) ? $config['through'] : $config['from'];
        $parentModelName        = $config['from'];
        $parentPK               = $parentModelName::TABLE_PK;
        $config['foreign_key']  = (isset($config['foreign_key'])) ? $config['foreign_key'] : $parentPK;
        $fk                     = $config['foreign_key'];

        return self::getModelFromPK( $parentModelName, $childModel->$fk );

    }

    public static function parseRelationshipHooks( $model, $name, $has, $belongs )
    {

        // Aliases take priority
        foreach($has as $key => $value) {

            if( isset( $value['as'] ) && $value['as'] == $name )
                return Library::getHasManyRelationship( $model, str_singular( $key ), $value );

        }


        foreach($belongs as $key => $value) {

            if( isset( $value['as'] ) && $value['as'] == $name )
                return Library::getBelongsToRelationship( $model, str_singular( $key ), $belongs );

        }


        $name = ucfirst($name);

        if( in_array( $name, $has ) )
            return Library::getHasManyRelationship( $model, str_singular($name) );

        if( array_key_exists( $name, $has ) && !isset( $has[$name]['as'] ) )
            return Library::getHasManyRelationship( $model, str_singular($name), $has[ ucfirst($name) ] );

        if( in_array( $name, $belongs ) )
            return Library::getBelongsToRelationship( $model, str_singular($name), $belongs );

        if( array_key_exists( $name, $belongs ) && !isset( $belongs[$name]['as'] )  )
            return Library::getBelongsToRelationship( $model, str_singular($name), $belongs );


        return null;

    }


    public static function makeModelAssociation( $modelOne, $modelTwo )
    {


        $name       = ucfirst( get_class($modelOne) );
        $has        = $modelTwo->_getHasMany();
        $belongs    = $modelTwo->_getBelongsTo();

        foreach($has as $key => $value) {

            if( $key == str_plural( $name ) ) {
                return HasManyProxy::associate( $modelTwo, $modelOne, $value );
            }

        }


        foreach($belongs as $key => $value) {

            if( $key == str_singular( $name ) ) {
                return BelongsToProxy::associate( $modelOne, $modelTwo, $value );
            }

        }

        if( in_array( str_plural($name), $has ) )
            return HasManyProxy::associate( $modelTwo, $modelOne );

        if( array_key_exists( str_plural($name), $has ) && !isset( $has[ str_plural($name) ]['as'] ) )
            return HasManyProxy::associate( $modelTwo, $modelOne );

        if( in_array( $name, $belongs ) )
            return BelongsToProxy::associate( $modelOne, $modelTwo );

        if( array_key_exists( $name, $belongs ) && !isset( $belongs[$name]['as'] )  )
            return BelongsToProxy::associate( $modelOne, $modelTwo );

    }

    public static function arrayToXML( $array, $rootNode = '' )
    {

        $xml = '';

        foreach( $array as $key => $value ) {

            $v = ( gettype( $value ) === 'array' && count( $value ) > 0 ) ? self::arrayToXML($value) : $value;
            $k = ( is_int($key) ) ? $rootNode : $key;

            if($k) {
                $xml .= '<'.$k.'>' . $v . '</'.$k.'>';
            }

        }

        return $xml;

    }

    public static function findModelsBySQL( $modelName, $sql, $params = array() )
    {

        $returnCollection = new \Jworkman\Activerecord\Collection( $modelName, DB::table( $modelName::TABLE ), true );
        $results = DB::select( $sql, $params );
        for($i = 0, $j = count($results); $i < $j; $i++)
        {
            $returnCollection[$i] = $results[$i];
        }
        $returnCollection->initModels();
        return $returnCollection;

    }

    public static function getAllModelsWhere( $modelName, $column, $operator = '=', $value = null )
    {

        $sqlChain = DB::table( $modelName::TABLE )->where( $column, $operator, $value );
        return new \Jworkman\Activerecord\Collection( $modelName, $sqlChain );

    }

    public static function getInterfacesFromString( $interfaceStr, $config )
    {

        // Colons delimit interfaces while commas delimit interface parameters
        $interfaces = explode(":", $interfaceStr);

        for($i = 0, $j = count($interfaces); $i < $j; $i++)
        {

            $interfaces[$i] = str_replace( "(", ",", $interfaces[$i] );
            $interfaces[$i] = str_replace( ")", "", $interfaces[$i] );
            $interfaces[$i] = explode( ",", $interfaces[$i] );

            for($ii = 0, $jj = count($interfaces[$i]); $ii < $jj; $ii++)
            {
                $interfaces[$i][$ii] = trim( $interfaces[$i][$ii] );
            }

            if( isset( $config[$interfaces[$i][0]] ) ) {
                $interfaces[$i][0] = $config[ $interfaces[$i][0] ];
            } else {
                App::abort(500,
                    "Filter of type \"" . $interfaces[$i][0] . "\" not defined"
                );
            }

        }

        return $interfaces;

    }

    public static function setModelProperty( $model, $property, $value )
    {

        $config = Config::get('activerecord::activerecord.interfaces');

        // First check to see if there are any data interfaces on this model
        $interfaceMap = $model->_getInterfaces();

        if( isset( $interfaceMap[$property] ) ) {

            $propertyInterfaces = self::getInterfacesFromString( $interfaceMap[$property], $config );

            for($i = 0, $j = count($propertyInterfaces); $i < $j; $i++)
            {
                $interfaceClass = array_shift( $propertyInterfaces[$i] );
                $value = $interfaceClass::set( $value, $propertyInterfaces[$i] );
                $model->set( $property, $value );
            }

        } else {
            $model->$property = $value;
        }

        return $model;

    }

    public static function getModelProperty( $interfaceMap, $property, $value )
    {

        $config = Config::get('activerecord::activerecord.interfaces');

        if( isset( $interfaceMap[$property] ) ) {

            $propertyInterfaces = self::getInterfacesFromString( $interfaceMap[$property], $config );

            for($i = 0, $j = count($propertyInterfaces); $i < $j; $i++)
            {
                $interfaceClass = array_shift( $propertyInterfaces[$i] );
                $value = $interfaceClass::get( $value, $propertyInterfaces[$i] );
            }

        }

        return $value;

    }

    public static function getValidatorsFromString( $validatorStr, $config )
    {

        $validators = explode(":", $validatorStr);

        for($i = 0, $j = count($validators); $i < $j; $i++)
        {

            $validators[$i] = str_replace( "(", ",", $validators[$i] );
            $validators[$i] = str_replace( ")", "", $validators[$i] );
            $validators[$i] = explode( ",", $validators[$i] );

            for($ii = 0, $jj = count($validators[$i]); $ii < $jj; $ii++)
            {
                $validators[$i][$ii] = trim( $validators[$i][$ii] );
            }

            if( isset( $config[$validators[$i][0]] ) ) {
                $validators[$i][0] = $config[ $validators[$i][0] ];
            } else {
                App::abort(500,
                    "Validator of type \"" . $validators[$i][0] . "\" not defined"
                );
            }

        }

        return $validators;

    }

    public static function checkModelValidity( &$model )
    {

        // First get validators
        $validatorMap   = $model->_getValidators();
        $errors         = array();

        $config = Config::get('activerecord::activerecord.validators');

        foreach( $validatorMap as $property => $validatorStr ) {

            $propertyValidators = self::getValidatorsFromString( $validatorStr, $config );

            foreach($propertyValidators as $propertyValidator)
            {

                $propertyValidatorClass = array_shift($propertyValidator);

                if( defined( $propertyValidatorClass.'::REQUIRES_MODEL' ) ) {
                    if(!$propertyValidatorClass::validate( $model->get($property), $propertyValidator, $model, $property )) {
                        return false;
                    }
                } else {
                    if(!$propertyValidatorClass::validate( $model->get($property), $propertyValidator )) {
                        return false;
                    }
                }


            }

        }

        return true;

    }


    public static function renderModelForInspection( &$model )
    {

        $inspectObj = new \stdClass();
        $interfacedProperties = $model->_getInterfaces();


        foreach($interfacedProperties as $key => $value)
        {
            $inspectObj->$key = $model->get( $key );
        }

        $publicProperties = self::buildArrayFromPropterties( $model );

        foreach($publicProperties as $key => $value)
        {
            $inspectObj->$key = $value;
        }

        return $inspectObj;

    }

    public static function destroy( $table, $pk, $ids )
    {

        $placeHolders = array();
        for($i = 0, $j = count($ids); $i < $j; $i++) {
            array_push($placeHolders, '?');
        }


        return DB::delete( 'DELETE FROM '.$table.' WHERE ' . $pk . ' IN (' . implode( ',', $placeHolders ) . ')', $ids );

    }

} 