<?php
/**
 * Created by PhpStorm.
 * User: Justin Workman
 * Date: 5/25/14
 * Time: 10:56 PM
 */

namespace Jworkman\Activerecord\Proxies;


use Illuminate\Support\Facades\DB;
use Whoops\Handler\Handler;

class Proxy {

    private $_sqlSegments   = array();
    private $_params        = array();
    public  $_chain;

    /*public function addSQLSegment( $segment, $params = array() )
    {

        array_push($this->_sqlSegments, $segment);
        foreach($params as $p) {
            array_push($this->_params, $p);
        }

        return $this;

    }

    public function executeSQL() {

        return DB::select( implode(' ', $this->_sqlSegments), $this->_params );

    }*/

    public function getSQLChain() { return $this->_chain; }

    /**
     * @return Proxy
     */
    public static function init()
    {
        $className = get_called_class();
        return new $className();
    }

    /**
     * @param mixed $childPK
     */
    public function childPK($childPK) { return $this; }

    /**
     * @param mixed $childTable
     */
    public function childTable($childTable) { return $this; }

    /**
     * @param mixed $parentPK
     */
    public function parentPK($parentPK) { return $this; }

    /**
     * @param mixed $parentTable
     */
    public function parentTable($parentTable) { return $this; }

    /**
     * @param mixed $through
     */
    public function through( $through ) { return $this; }

    /**
     * @param mixed $fk
     */
    public function fk( $fk ) { return $this; }

    /**
     * @param mixed $from
     */
    public function from( $from ) { return $this; }

    public function order($by) { return $this; }

}