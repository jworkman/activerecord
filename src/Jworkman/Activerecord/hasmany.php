<?php
/**
 * Created by PhpStorm.
 * User: Justin Workman
 * Date: 5/27/14
 * Time: 7:14 PM
 */

namespace Jworkman\Activerecord;


class Relationship {

    private $_through;
    private $_model;
    public $relationship;

    public function __construct() {

    }


    public static function HasMany( $model ) {

        $relationship = new self();
        $relationship->relationship = 'has_many';
        return $relationship->model($model);

    }

    public static function HasAndBelongsToMany( $model ) {

        $relationship = new self();
        return $relationship->model($model);

    }

    public function model( $model ) {

        $this->_model = $model;
        return $this;

    }

    public function through( $through ) {

        $this->_through = $through;

    }


} 