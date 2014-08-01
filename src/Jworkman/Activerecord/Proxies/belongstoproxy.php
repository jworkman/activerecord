<?php
/**
 * Created by PhpStorm.
 * User: Justin Workman
 * Date: 5/25/14
 * Time: 10:56 PM
 */

namespace Jworkman\Activerecord\Proxies;

use Illuminate\Support\Facades\DB;

class BelongsToProxy extends Proxy {

    private $_parentTable;
    private $_parentPK;
    private $_childTable;
    private $_childPK;
    private $_through;
    private $_from;
    private $_fk;

    /**
     * @param mixed $childPK
     */
    public function childPK($childPK)
    {
        $this->_childPK = $childPK; return $this;
    }

    /**
     * @param mixed $childTable
     */
    public function childTable($childTable)
    {
        $this->_childTable = $childTable; return $this;
    }

    /**
     * @param mixed $parentPK
     */
    public function parentPK($parentPK)
    {
        $this->_parentPK = $parentPK; return $this;
    }

    /**
     * @param mixed $parentTable
     */
    public function parentTable($parentTable)
    {
        $this->_parentTable = $parentTable; return $this;
    }

    /**
     * @param mixed $from
     */
    public function from($from)
    {
        $this->_from = $from; return $this;
    }

    /**
     * @param mixed $through
     */
    public function through( $through )
    {
        $this->_through = $through; return $this;
    }

    public function fk($fk) {
        $this->_fk = $fk; return $this;
    }


    public function getChain( $pk )
    {

        if( $this->_through == $this->_from ) {

            $this->_chain = DB::table( $this->_parentTable )->where( $this->_fk, '=', $pk )->take(1);

        } else {

            $childFK = strtolower( $this->_childTable ) . "_id";
            $this->_chain = DB::table( $this->_parentTable )
                ->join( $this->_through, $this->_through . "." . $childFK, '=', $this->_childTable . "." . $this->_childPK )
                ->select($this->_parentTable . '.*')
                ->where( $this->_through . "." . $childFK, '=', $pk )->take(1);

        }

        return $this->_chain;

    }

    public static function associate( $parent, $child, $config = array() ) {

        if( isset($config['through']) ) {

            var_dump($config);
            exit;

        }

        $parentClassName    = get_class($parent);
        $fk                 = strtolower(str_singular($parentClassName)) . '_id';
        $parentPK           = $parentClassName::TABLE_PK;
        $child->$fk         = $parent->$parentPK;
        return              $child->save();

    }

}

