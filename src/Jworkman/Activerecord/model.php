<?php
namespace Jworkman\Activerecord;
use Illuminate\Support\Facades\Config;

class Model {

    /**
     * Override this to the database table name that
     * represents this model. This defaults to the
     * model name.
     *
     * @var string
     */
    CONST TABLE = "";
    CONST TABLE_PK = "id";

    protected   $validates        = array();
    protected   $attr_accessible  = array();
    protected   $has_many         = array();
    protected   $belongs_to       = array();
    protected   $filters          = array();
    protected   $interfaces       = array();
    protected   $validators       = array();
    private     $interfacedData   = array();

    // This is where the SQL query will be built out over a chain of calls
    private $_queryCache = array( "", array() );

    public function __get( $name ) {

        if( isset($this->interfacedData[$name]) ) {
            return $this->get( $name );
        }

        return Library::parseRelationshipHooks( $this, $name, $this->has_many, $this->belongs_to );

    }

    public function __set( $name, $value )
    {
        return Library::setModelProperty( $this, $name, $value );
    }

    public function __call( $name, $arguments )
    {
        $class = get_class($this);
        switch($name) {
            case 'destroy':
                $pk = $class::TABLE_PK;
                return Library::destroy( $class::TABLE, $class::TABLE_PK, array( $this->$pk ) );
                break;
        }
    }

    public static function __callStatic( $name, $arguments )
    {

        $class = get_called_class();
        switch($name) {
            case 'destroy':
                $ids = array();
                if(gettype( $arguments[0] ) == "integer") {
                    foreach($arguments as $id) {
                        array_push($ids, $id);
                    }
                } else {
                    $ids = $arguments[0];
                }
                return Library::destroy( $class::TABLE, $class::TABLE_PK, $ids );
                break;
        }
    }

    public function inspect()
    {
        $inspect = $this->inspectRaw();
        Rickosborne\CFDump::dump( $inspect );
    }

    public function inspectRaw()
    {
        return Library::renderModelForInspection( $this );
    }

    public function set( $property, $value )
    {
        $this->interfacedData[ $property ] = $value; return $this;
    }

    public function get( $name )
    {
        if( !isset($this->interfacedData[$name]) ) { return null; }
        return Library::getModelProperty( $this->interfaces, $name, $this->interfacedData[$name] );
    }

    /**
     * Returns an existing model from the
     * database. Identified by the
     * $primary_key attribute in each model.
     *
     * @param $id - Primary key of the model
     * to query.
     *
     * @return \Jworkman\Activerecord\Model
     */
    public static function find( $id = null )
    {
        return Library::getModelFromPK( get_called_class(), $id );
    }
    public function toArray( $avoid = array() )
    {
        $obj = Library::renderModelForInspection( $this );
        return Library::buildArrayFromPropterties( $obj, $avoid );
    }
    public function toJSON( $avoid = array() )
    {
        return json_encode( $this->toArray( $avoid ) );
    }
    public function toXML( $avoid = array(), $root = '' )
    {
        return Library::arrayToXML( $this->toArray(),
            (($root) ? $root : str_singular( strtolower( get_class($this) ) ) )
        );
    }
    public static function first( $amount = 1, $by = null )
    {
        return Library::getFirstModelByName( get_called_class(), $amount, $by );
    }
    public static function last( $amount = 1, $by = null )
    {
        return Library::getLastModelByName( get_called_class(), $amount, $by );
    }
    public static function all()
    {
        return Library::getAllModels( get_called_class() );
    }
    public static function create( $attrs = array() )
    {
        return self::initFromAttributes( $attrs )->save();
    }
    public static function findOrCreate( $pk )
    {
        return Library::findOrCreateModel( get_called_class(), $pk  );
    }
    public static function findBySQL( $sql, $params = array() )
    {
        return Library::findModelsBySQL( get_called_class(), $sql, $params );
    }
    public static function where( $column, $operator, $value = null )
    {

        $modelName = get_called_class();
        $collection = new Collection( $modelName, \Illuminate\Support\Facades\DB::table( $modelName::TABLE ) );
        return $collection->where($column, $operator, $value);

    }
    public function update( $attrs = array() )
    {
        return Library::updateModelFromAttributes( $this, $attrs )->save();
    }
    public function save( $feedback = false )
    {
        return Library::saveModel( $this, $feedback );
    }
    public function assign( $model )
    {
        return Library::makeModelAssociation( $model, $this );
    }
    public function isValid()
    {
        return Library::checkModelValidity( $this );
    }
    public function isNew()
    {
        $className  = get_class($this);
        $pk         = $className::TABLE_PK;
        return ( $this->get( $pk ) ) ? false : true;
    }

    /**
     * Returns a new model (in memory) with
     * assigned values. The object does not
     * yet exist in the database until save
     * is called.
     *
     * @param $attrs - Key/Value pair of
     * properties that will be assigned to
     * the object when instantiated.
     *
     * @return void
     */
    public static function initFromAttributes( $attrs = array() )
    {
        return Library::getNewModelFromName( get_called_class(), $attrs );
    }


    public function _getHasMany() { return $this->has_many; }
    public function _getBelongsTo() { return $this->belongs_to; }
    public function _getAttrAccessible() { return $this->attr_accessible; }
    public function _getValidates() { return $this->validates; }
    public function _getInterfaces() { return $this->interfaces; }
    public function _getValidators() { return $this->validators; }

} 