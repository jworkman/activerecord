<?php


return array(

    /*
	|--------------------------------------------------------------------------
	| Plugins
	|--------------------------------------------------------------------------
	|
	| Register all plugin packages here so that Activerecord can see them at
    | runtime.
    |
    | Each registered plugin is defined by the full Laravel 4 class path.
	|
	*/

    'plugins' => array(

        '\Jworkman\Activerecord\Plugins\Plugin'

    ),


    /*
	|--------------------------------------------------------------------------
	| Filters
	|--------------------------------------------------------------------------
	|
	| Filters helps your models interface with your database. They complete
    | extra logic to validate your model properties.
    |
    | Each registered filter is defined by the full Laravel 4 class path.
	|
	*/

    'validators' => array(

        'Email'         => '\Jworkman\Activerecord\Validators\Email',
        'Phone'         => '\Jworkman\Activerecord\Validators\Phone',
        'Unique'        => '\Jworkman\Activerecord\Validators\Unique',
        'Presence'      => '\Jworkman\Activerecord\Validators\Presence',
        'Absence'       => '\Jworkman\Activerecord\Validators\Absence',
        'Association'   => '\Jworkman\Activerecord\Validators\Association',
        'Length'        => '\Jworkman\Activerecord\Validators\Length',
        'Numeric'       => '\Jworkman\Activerecord\Validators\Numeric',
        'Custom'        => '\Jworkman\Activerecord\Validators\Custom',

    ),



    /*
	|--------------------------------------------------------------------------
	| Data Interfaces
	|--------------------------------------------------------------------------
	|
	| Data Interfaces help you to communicate with your model data to and from
    | the database.
    |
    | Each registered sanitizer is defined by the full Laravel 4 class path.
	|
	*/

    'interfaces' => array(

        'Serialize' => '\Jworkman\Activerecord\Interfaces\Serialize',
        'Delimiter' => '\Jworkman\Activerecord\Interfaces\Delimiter',
        'Trim'      => '\Jworkman\Activerecord\Interfaces\Trim',
        'Plural'    => '\Jworkman\Activerecord\Interfaces\Plural',
        'Singular'  => '\Jworkman\Activerecord\Interfaces\Singular',
        'Lower'     => '\Jworkman\Activerecord\Interfaces\Lower',
        'UpperCase' => '\Jworkman\Activerecord\Interfaces\UpperCase',
        'Capitalize'=> '\Jworkman\Activerecord\Interfaces\Capitalize',
        'Replace'   => '\Jworkman\Activerecord\Interfaces\Replace',
        'JSON'      => '\Jworkman\Activerecord\Interfaces\JSON',
        'TimeStamp' => '\Jworkman\Activerecord\Interfaces\TimeStamp',
        'Boolean'   => '\Jworkman\Activerecord\Interfaces\Boolean',
        'Integer'   => '\Jworkman\Activerecord\Interfaces\Integer',
        'String'    => '\Jworkman\Activerecord\Interfaces\String',
        'Concat'    => '\Jworkman\Activerecord\Interfaces\Concat',
        'Hidden'    => '\Jworkman\Activerecord\Interfaces\Hidden',
        'Encrypt'   => '\Jworkman\Activerecord\Interfaces\Encrypt',

    ),





);