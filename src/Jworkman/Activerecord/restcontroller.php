<?php
namespace Jworkman\Activerecord;

use Illuminate\Routing\Controller;

class RESTController extends Controller {

    public function __construct()
    {

    }

    public function store()
    {
        return $this->make();
    }

}