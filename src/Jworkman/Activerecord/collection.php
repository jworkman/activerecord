<?php
namespace Jworkman\Activerecord;
use Jworkman\Activerecord\Rickosborne\CFDump;

class Collection extends \ArrayObject {

    private $_model;
    private $_ranSQL;
    private $_SQLChain;
    private $_modelsInit = false;

    public function __construct( $model, $sqlChain, $ranSQL = false ) {

        parent::__construct();
        $this->_SQLChain        = $sqlChain;
        $this->_model           = $model;
        $this->_ranSQL          = $ranSQL;

    }

    public function __get( $name ) {

        $this->runSQL();

        switch($name) {
            case 'each':
                $this->initModels();
                return $this;
                break;

            case 'first':
                return $this->first();
                break;

            case 'last':
                return $this->last();
                break;
        }

    }

    private function runSQL() {
        if(!$this->_ranSQL) {
            $this->_ranSQL = true;

            //Run the query and get the results
            $results = $this->_SQLChain->get();

            for($i = 0, $j = count($results); $i < $j; $i++)
            {
                $this[ $i ] = $results[ $i ];
            }

        }

        return true;
    }

    public function initModels() {

        $this->_modelsInit = true;
        for($i = 0, $j = count($this); $i < $j; $i++)
        {
            $this[ $i ] = Library::buildNewModel( $this->_model, $this[ $i ] );
        }

        return $this;

    }

    public function order( $by, $desc = false ) {

        $this->_SQLChain->orderBy( $by, (($desc) ? 'DESC' : 'ASC') );
        return $this;

    }

    public function where( $condition, $operator, $value = null ) {

        if($operator && !is_null($value)) {
            $this->_SQLChain->where( $condition, $operator, $value );
        } else {
            $this->_SQLChain->where( $condition, '=', $operator );
        }

        return $this;

    }

    public function each( $iterator ) {

        $this->runSQL();

        $model = null;

        for($i = 0, $j = count($this); $i < $j; $i++)
        {
            $model = Library::buildNewModel( $this->_model, $this[ $i ] );
            $iterator( $model );
            $model = null;
        }

        return $this;

    }

    public function limit( $limit, $offset = null ) {

        $this->_SQLChain->take( (int)$limit );

        if(!is_null($offset)) {
            $this->offset( $offset );
        }

        return $this;

    }

    public function offset( $offset ) {

        $this->_SQLChain->skip( (int)$offset );
        return $this;

    }

    public function toArray( $avoid = array() ) {
        $returnArray = array();
        $this->runSQL();
        for($i = 0, $j = count($this); $i < $j; $i++)
        {
            array_push(
                $returnArray,
                Library::buildNewModel( $this->_model, $this[ $i ] )
                    ->toArray( $avoid )
            );
        }

        return $returnArray;
    }

    public function toJSON( $avoid = array() ) {
        return json_encode( $this->toArray( $avoid ) );
    }

    public function toXML( $avoid = array(), $rootNode = 'items' ) {
        return Library::arrayToXML( $this->toArray( $avoid ), $rootNode );
    }

    public function first( $amount = null ) {

        $this->runSQL();

        if($amount) {

            $returnCollection = new Collection( $this->_model, $this->_SQLChain, true );
            for($i = 0, $j = count($this); ($i < $j && $i < $amount); $i++) {
                $returnCollection[$i] =  Library::buildNewModel( $this->_model, $this[ $i ] );
            }

            return $returnCollection;

        } else {
            return (count($this) > 0) ? Library::buildNewModel( $this->_model, $this[ 0 ] ) : null;
        }
    }

    public function last( $amount = null ) {

        $this->runSQL();

        if($amount) {

            $returnCollection = new Collection( $this->_model, $this->_SQLChain, true );
            $ii = 0;
            for($i = count($this) - 1; ($i >= 0 && $ii < $amount); $i--, $ii++) {
                $returnCollection[$i] =  Library::buildNewModel( $this->_model, $this[ $i ] );
            }

            return $returnCollection;

        } else {
            return (count($this) > 0) ? Library::buildNewModel( $this->_model, $this[ count($this) - 1 ] ) : null;
        }
    }

    public function sum( $column ) {

        $this->runSQL();

        $sum = 0;

        for($i = 0, $j = count($this); $i < $j; $i++)
        {
            $sum += (isset($this[$i]->$column) && $this[$i]->$column) ? $this[$i]->$column : 0;
        }

        return $sum;

    }

    public function max( $column ) {

        $this->runSQL();

        $max = 0;
        for($i = 0; $i < $j = count($this); $i++)
        {
            if( $this[$i]->$column > $max  && is_numeric($this[$i]->$column) )
            {
                $max = $this[$i]->$column;
            }
        }

        return $max;

    }


    public function min( $column ) {

        $this->runSQL();
        $min = $this->max( $column );

        for($i = 0; $i < $j = count($this); $i++)
        {

            if( $this[$i]->$column < $min && is_numeric($this[$i]->$column) )
                $min = $this[$i]->$column;

        }

        return $min;

    }


    public function average( $column ) {

        $this->runSQL();

        $sum = 0;
        $j = count($this);
        for($i = 0; $i < $j; $i++)
        {
            $sum += (isset($this[$i]->$column) && $this[$i]->$column  && is_numeric($this[$i]->$column)) ? $this[$i]->$column : 0;
        }

        return $sum / $j;

    }

    public function exists( $needle, $column )
    {

        $this->runSQL();

        for($i = 0, $j = count($this); $i < $j; $i++) {

            if(isset($this[$i]->$column) && $this[$i]->$column == $needle)
                return true;

        }

        return false;

    }

    public function orWhere( $column, $operator, $value = null )
    {
        if($operator && !is_null($value)) {
            $this->_SQLChain->orWhere( $condition, $operator, $value );
        } else {
            $this->_SQLChain->orWhere( $condition, '=', $operator );
        }

        return $this;
    }

    public function inspect()
    {

        $inspectArray = $this->inspectRaw();
        CFDump::dump( $inspectArray );
        return $this;

    }

    public function inspectRaw()
    {
        $this->runSQL();
        $this->initModels();

        $inspectArray = array();

        for($i = 0, $j = count($this); $i < $j; $i++) {
            array_push( $inspectArray, Library::renderModelForInspection( $this[$i] ) );
        }

        return $inspectArray;
    }

} 