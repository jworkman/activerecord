<?php
/**
 * Created by PhpStorm.
 * User: Justin Workman
 * Date: 5/25/14
 * Time: 10:56 PM
 */

namespace Jworkman\Activerecord\Proxies;

use Illuminate\Support\Facades\DB;

class HasManyProxy extends Proxy {

    private $_parentTable;
    private $_parentPK;
    private $_childTable;
    private $_childPK;
    private $_through;
    private $_from;
    private $_fk;
    private $_orderDirection;
    private $_orderBy;

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

    public function order($by) {

        $chunks = explode(' ', trim($by));
        $this->_orderBy = (isset($chunks[0]) && !empty($chunks[0])) ? $chunks[0] : "id";
        $this->_orderDirection = (isset($chunks[1]) && !empty($chunks[1])) ? $chunks[1] : "ASC";
        return $this;

    }

    public function getChain( $pk )
    {

        if( $this->_through ) {

            /*

                SELECT `Moons`.* FROM `Stars`
                JOIN `Planets` ON (`Planets`.`star_id` = `Stars`.`id`)
                JOIN `Moons` ON (`Moons`.`planet_id` = `Planets`.`id`)
                WHERE `Stars`.`id` = 1;

             */

            // If "through" was specified with a pivot model

            if( class_exists( ucfirst( str_singular( $this->_through ) ) ) ) {

                // Parent table
                $parentTableName    = $this->_parentTable;
                $parentFK           = strtolower( str_singular( $this->_parentTable ) ) . "_id";

                // Through table
                $middleModelName    = ucfirst( str_singular( $this->_through ) );
                $middleTableName    = (class_exists($middleModelName)) ? $middleModelName::TABLE : $this->_through;
                $middleTablePK      = (class_exists($middleModelName)) ? $middleModelName::TABLE_PK : $this->_through . "_id";
                $middleTableFK      = strtolower( str_singular( $middleTableName ) ) . "_id";

                // Child table
                $childModelName     = $this->_from;
                $childTableName     = $childModelName::TABLE;


                $this->_chain = DB::table( $parentTableName )
                        ->select( $childTableName.".*" )
                        ->join( $middleTableName, $middleTableName.'.'.$parentFK, '=', $parentTableName.'.'.$this->_parentPK )
                        ->join( $childTableName, $childTableName . '.' . $middleTableFK, '=', $middleTableName . '.' . $middleTablePK )
                        ->where( $parentTableName . '.' . $this->_parentPK, '=', $pk );

            } else {

                /*

                    SELECT Moons.* FROM Planets
                    JOIN MoonsPlanets ON (MoonsPlanets.planet_id = Planets.id)
                    JOIN Moons ON (Moons.id = MoonsPlanets.moon_id);
                    WHERE `Stars`.`id` = 1;

                 */

                // If "through" was not a model then we are talking about a manual pivot table name
                // Parent table
                $parentTableName    = $this->_parentTable;
                $parentFK           = strtolower( str_singular( $this->_parentTable ) ) . "_id";

                $middleTableName    = $this->_through;

                $childModelName     = $this->_from;
                $childTableName     = $childModelName::TABLE;
                $childFK            = str_singular( strtolower( $childTableName ) ) . "_id";

                $this->_chain   = DB::table( $parentTableName )
                    ->select( $childTableName.".*" )
                    ->join( $middleTableName, $middleTableName . '.' . $parentFK, '=', $parentTableName . '.' . $this->_parentPK  )
                    ->join( $childTableName, $childTableName . '.' . $childModelName::TABLE_PK, '=', $middleTableName . '.' . $childFK )
                    ->where( $parentTableName . '.' . $this->_parentPK, '=', $pk );



            }

        } else {

            $this->_chain = DB::table( $this->_childTable )
                ->where( $this->_fk, '=', $pk );

        }

        return $this->_chain->orderBy( $this->_orderBy, $this->_orderDirection );

    }


    public static function associate( $parent, $child, $config = array() ) {

        $parentClassName    = get_class($parent);
        $childClassName     = get_class($child);
        $fk                 = strtolower(str_singular($parentClassName)) . '_id';
        $childFK            = strtolower(str_singular($childClassName)) . '_id';
        $parentPK           = $parentClassName::TABLE_PK;
        $childPK            = $childClassName::TABLE_PK;

        if( isset($config['through']) ) {

            $parentFK = strtolower( str_singular( get_class( $parent ) ) ) . '_id';
            $childFK = strtolower( str_singular( get_class( $child ) ) ) . '_id';

            if( !isset($config['uniqueCheck']) || $config['uniqueCheck'] !== false ) {
                // First check if instance is already there
                $results = DB::table( $config['through'] )
                    ->where( $fk, '=', $parent->$parentPK )
                    ->where( $childFK, '=', $child->$childPK )
                    ->count();
            } else {
                $results = 0;
            }


            if($results > 0) {
                return true;
            } else {
                return DB::table( $config['through'] )->insert(
                    array( $fk => $parent->$parentPK, $childFK => $child->$childPK )
                );
            }

        }


        $child->$fk         = $parent->$parentPK;
        return              $child->save();

    }

}