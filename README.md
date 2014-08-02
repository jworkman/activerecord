# Laravel Activerecord ORM

This is an Object Relational Mapper for Laravel 4. Inspired by Ruby on Rails Activerecord. You will find a lot of similarities, but a few differences in the way the two operate. 

## Basic Setup & Installation

Add the following to your composer.json file located at the root of your Laravel 4 installation.

	"jworkman/activerecord": "dev-master"


The run a composer update

	composer update

After installing the package you will need to add the service provider to your app.php configuration file located in app/config/app.php. Append the line in the 'providers' array.

	'Jworkman\Activerecord\ActiverecordServiceProvider',


If installed properly you should be able to run the artisain command to see a list of the avialable activerecord commands. 

	php artisain


## Creating a Model

You can easily create models, and scaffolds using the artisain activerecord generator tools. To create just an Activerecord model run the following.

	php artisain activerecord:model Planet

It will ask you for your interfaces. These are just abstract data types you would like to assign to your model. A model can have as many interfaces as you choose. Here is a list of interfaces avialable for you to use. 

	name:String:Capitalize size:Integer star_id:Integer created:TimeStamp

After it generates your model it will write it to app/models/<Model>.php. Open this in your text editor and edit the $attr_accessible property. List out all the fields that you would like mass assignment unlocked to. 

	$attr_accessible = [ 'name', 'size' ];

This allows the model to be updated using mass assignment instead of updating each field in your model one by one. DO NOT place any property in this list that you would not want your user to directly have access to.


Next you will have to generate a migration using the standard artisain migration tool. 

	php artisain migrate:make CreatePlanets

Then modify your migration and then run

	php artisain migrate

## Querying a Model


To load a model into your application by it's id run:

	$planet = Planet::find( 1 );

Loading a collection of models from a table. WARNING! This isn't the best method of querying models due to performance reasons. 

	$planets = Planet::all();

We will now need to loop through the planet collection. We can do this by calling the "each" property of a collection.

	foreach( $planets->each as $planet ) {
		// Some code
	}

Or a better way to do this is through a closure. Performance is better using a closure while looping through big collection sets.

	$planets->each( function( $planet ) {
		// Some code
	});

Model collections are just array objects in PHP. They have their own custom methods attached to them. Activerecord gives you access to a bunch of collection methods that help you with data sets. 

Lets say we would like to get the average size of all the planets in our databse. We could call the "average" method on the collection. The first parameter specifies what property we are getting the average from.

	$average_size = $planets->average( 'size' );

If you would like to get the maximum value of a colleciton set then you can invoke the "max" method of the collection object. The first parameter specifies what property we are getting the maximum value from.

	$max_size = $planets->max( 'size' );

The same goes for getting the minimum value except we invoke "min" instead. 

	$min_size = $planets->min( 'size' );


Another helper method of a collection is the "first" method. When this method is invoked it returns the first object in the set. You can also invoke the "first" method by just referencing the "first" property.

	$first_planet = $planets->first();
	// OR
	$first_planet = $planets->first;

The "last" method works the same way, but instead returns the last object in the collection.

	$last_planet = $planets->last();
	// OR
	$last_planet = $planets->last;


If you would like to query a model with a specific condition(s) you can call the "where" static function on the model itself. Please do not forget to call "get" at the end of your chain.

	$big_planets = Planets::where( 'size', '>', 400000 )->get();

The example above selects all the planets that have a size of 400000 or bigger. Notice how we have to run "get" at the end of our query. It tells Activerecord that we are done specifiying our conditions and to execute the query.

Say we want to add multiple conditions to our query. We can append another where call to our chain. 

	$big_mass_planets = Planets::where( 'size', '>', 400000 )->where('mass' '>' 400000)->get();

If we want to select all the planets where the size is large, or the mass is large then we would specify "orWhere" in our chain.

	$big_planets = Planet::where( 'size', '>', 400000 )->orWhere('mass' '>', 400000)->get();

We can also limit our returned result. If we do not want more than 10 records from the database we would specify "limit" at the end of our chain.

	$first_ten_big_planets = Planets::where('size', '>', 400000)->limit( 10 )->get();

Pagination also prove useful in today's society so we will need to specify an "offset" call to the end of our chain in order to start the limit at a specific index.

	$big_planets = Planets::where('size', '>', 400000)->limit( 10 )->offset( 10 )->get();

If we want our results ordered by the database we will specify "order" at the end of our collection chain.

	$large_planets_first = Planets::where('size', '>', 400000)->order( 'size', 'DESC' );

Everything has a death at some point. So when you want to remove records from your database we can remove it by calling "destroy" on the model itself. It returns the number of rows effected by the action.

	Planet::find( 1 )->destroy();

If we would like to remove an entire collection we just call "destroy" on the collection object itself. WARNING!!! This can be dangerous if not used properly. This will remove all the records in the collection. The following would destroy all models with the size grater than 400000.

	Planet::where('size', '>', 400000)->destroy();

You can also specify an array of ids to destroy manually. 

	Planet::destroy([ 1, 2, 3, 4 ]);
	// OR specify parameters
	Planet::destroy( 1, 2, 3, 4 );

Sometimes its useful to update a model quickly with a form submition. Here we can update a model with an associative array and automaticly save it. You will need to specify $attr_accessible for this call to fill in the values of your model. 

	Planet::find( 1 )->update( $_POST['new_planet_data'] );

## Converting Models

Typicly with APIs and other software packages you will deal with less abstract, and more basic data types that PHP offers. Most packages will not understand what an Activerecord model, or collection is. So instead we will have to give them a more basic data type to use. 

We can turn JUST the data of our model into an array by calling the "toArray" method of a model. 

	Planet::find( 1 )->toArray();

The above builds an array from the model and returns it. Lets say you would like to turn a model into an array, but not certain fields of that model. We can specify a list of fields to avoid as the first parameter. The below example exports all the fields in the model EXCEPT the 'id' field. 

	Planet::find( 1 )->toArray([ 'id' ]);

We can also export a model directly to JSON format. This is useful for creating APIs with our models. 

	Planet::find( 1 )->toJSON();

The same goes for "toJSON" when it comes to avoiding specific fields from exporting. 

	Planet::find( 1 )->toJSON([ 'id' ]);

We can also run the same two methods on entire data collections. The following shows how to run it on a collection.

	Planet::all()->toArray();
	Planet::all()->toJSON();

	// OR to exclude fields

	Planet::all()->toArray([ 'id' ]);
	Planet::all()->toJSON([ 'id' ]);

There is also a "toXML" method on both models, and collections. The same syntax is used for that. However XML is no longer used as the primary language between web applications. You should be using JSON instead.


## Interfaces

Interfaces help you interact more effectively between your model and database. They can be basicly categorized as data typing your model fields. 

When you specify a field as a Boolean the "Boolean" interface handles all data transactions between the actual field value in the model, and the field value in the database. 

An example of this is a boolean field. The field should be treated as "true", or "false." But in the databse a "1", or a "0." Interfaces help take care of all that for you. The "TimeStamp" interface stores as a string in the database, but when referenced in the ORM its a DateTime object.

	Planet::find(1)->created = new DateTime();
	// Gets stored in the database as a string

	Planet::find(1)->created->format("M/d/Y");
	// The property "created" is a date time object when fetched


#### Boolean

*Definition Syntax*

	protected $interfaces = [
		'name' => 'Boolean'
	];

Treats the field as a "true", or "false" boolean. Then stores the value as a 1, or 0 in the database.

	// Stores in DB as 1
	Planet::find( 1 )->active = true;

	// Evaluated as true
	Planet::find( 1 )->active;

#### Capitalize

*Definition Syntax*

	protected $interfaces = [
		'name' => 'Capitalize'
	];

Upercases the first letter of the expected string type. 

	$planet = Planet::find( 1 );

	$planet->name = "earth";

	echo $planet->name; // Outputs => "Earth"


#### Concat

*Definition Syntax*

	protected $interfaces = [
		'name' => 'Concat(<String to Concatinate>)'
	];

Concatinates the value with the value specified by the first paramter in interface.
	
	// Interface set to 'Concat(My)'
	
	$planet = Planet::find( 1 );

	$planet->name = "earth";

	echo $planet->name; // Outputs => "Myearth"

#### Delimiter

*Definition Syntax*

	protected $interfaces = [
		'list' => 'Delimiter(<Delimiter>)'
	];

Stores the string value while treating the value as an array delimited by the first parameter in interface. 


	// Interface set to 'Delimiter'
	
	$planet = Planet::find( 1 );

	$planet->list = "item1,item2,item3";

	print_r($planet->list); // Outputs => array( 'item1', 'item2', 'item3' )


#### Encrypt

*Definition Syntax*

	protected $interfaces = [
		'encrypted' => 'Encrypt'
	];

Encrypts a string that is assigned to the model. The data is not preserved so once assigned to the model the string is permanently encrypted. This method uses the Laravel encrypt function.


	$planet = Planet::find( 1 );

	$planet->encrypted = "my encrypted string";

	echo $planet->encrypted // Outputs => ## some long hash value ##



#### Integer

*Definition Syntax*

	protected $interfaces = [
		'size' => 'Integer'
	];

Treats the stored, and interfaced property as an integer. 


	$planet = Planet::find( 1 );

	$planet->size = "50000";

	var_dump($planet->size) // Outputs => (int)50000


#### JSON

*Definition Syntax*

	protected $interfaces = [
		'data' => 'JSON'
	];

Stores the value of the property as JSON, and treats the property as an associative array in php. 


	$planet = Planet::find( 1 );

	// Stores in DB as JSON string
	$planet->data = array( "myProperty" => "value" );


	var_dump($planet->data) // Outputs => (array)[ "myProperty" => "value" ]


#### LowerCase

*Definition Syntax*

	protected $interfaces = [
		'name' => 'LowerCase'
	];

Stores and treats the value as an all lowercase string. All letters are lowercase in the string. 

	$planet = Planet::find( 1 );

	$planet->name = "MY EARTH";

	echo $planet->name; // Outputs => "my earth"




#### Plural

*Definition Syntax*

	protected $interfaces = [
		'name' => 'Plural'
	];

Stores and treats the value as a plural string.

	$planet = Planet::find( 1 );

	$planet->name = "Earth";

	echo $planet->name; // Outputs => "Earths"


#### Singular

*Definition Syntax*

	protected $interfaces = [
		'name' => 'Singular'
	];

Stores and treats the value as a singular string.

	$planet = Planet::find( 1 );

	$planet->name = "Earths";

	echo $planet->name; // Outputs => "Earth"


#### Replace

*Definition Syntax*

	protected $interfaces = [
		'name' => 'Replace(<Needle>, <Replace>)'
	];

Stores and treats the value as a plural string.


	// Interface set to 'Replace(Earth, Mars)'

	$planet = Planet::find( 1 );

	$planet->name = "Earth";

	echo $planet->name; // Outputs => "Mars"



#### Serialize

*Definition Syntax*

	protected $interfaces = [
		'data' => 'Serialize'
	];

Stores an object as a serialized object in the database. Treats the object as the unserialized version in php. Operates simular to JSON interface, but without using JSON. 

	$planet = Planet::find( 1 );

	$planet->data = array( "myProperty" => "value" );

	var_dump($planet->data) // Outputs => (array)[ "myProperty" => "value" ]




#### String

*Definition Syntax*

	protected $interfaces = [
		'size' => 'String'
	];

Treats the stored, and interfaced property as a string. 


	$planet = Planet::find( 1 );

	$planet->size = 50000;

	var_dump($planet->size) // Outputs => (string) "50000"



#### TimeStamp

*Definition Syntax*

	protected $interfaces = [
		'created' => 'TimeStamp'
	];

Stores the object as a string timestamp in database, and treats the value as a DateTime object in php.

	$planet = Planet::find( 1 );

	echo $planet->created->format( 'Y/m/D' )  // Outputs the date in a string

	// Stores as a MySQL TimeStamp string
	$planet->created = new DateTime();


#### Trim

*Definition Syntax*

	protected $interfaces = [
		'name' => 'Trim'
	];

Stores the string version of the value assigned without extra whitespace.

	$planet = Planet::find( 1 )

	$planet->name = " Earth ";

	echo $planet->name; // Outputs "Earth"



#### UpperCase

*Definition Syntax*

	protected $interfaces = [
		'name' => 'UpperCase'
	];

Stores and treats the string with all letters as capital letters

	$planet = Planet::find( 1 )

	$planet->name = "earth";

	echo $planet->name; // Outputs "EARTH"


## Mass Assignment

Activerecord models have two methods that allow the model to be quickly updated, or created with an associative array. Both "create", and "update" are methods that can do that for you. However you must specify in your model which fields can be quickly updated. This is for security reasons. 

Below shows how a model field will not be populated if it wasn't specified in the $attr_accessible array in the model.

	$planet = Planet::create( [ 'name' => 'Earth' ] );

	$planet->name // Outputs null

Now to fix this we will add the "name" field to our $attr_accessible array. Open up your model file and add the protect $attr_accessible property to your class. Then add each field you want to be mass assigned.

	protected $attr_accessible = [ 'name' ];

Now if we repeat the same exact code again we will get a different result for the "name" field. 

	$planet = Planet::create( [ 'name' => 'Earth' ] );

	$planet->name // Outputs "Earth"


## Relationships & Associations

The "R" in ORM stands for relational. One of the points of an ORM is to handle your database talbe relationships for you without writing more SQL by hand. 


### Belongs To

Lets say I generated a new model called Moon. On the new model I specify a planet_id field. This field will contain the id of the planet that the moon belongs to. We would specify a "belongs_to" relationship in the Moon model.

	protected $belongs_to = [ 'Planet' ];

Always remember that you specify the model names in this array as their singular versions. This is because of the context of the relationship. A moon belongs to a Planet (not Planets). 

Now that we have the association setup we can now access those relationships via the Moon model in PHP. Lets say we already have a moon in the database.

	$moon = Moon::find( 1 );

	$planet = $moon->planet;

### Has Many

Since the moon belongs to a planet we could there for say that a planet CAN have many moons. For this type of relationship we specify a "has_many" relationship. Place the following line in your model file.

	protected $has_many = [ 'Moons' ];

Remeber that in a has many relationship the models must be specified by thier plural versions according to context. The same goes for it's access in php. 

Now that we specified the realtionship in our model we can reference it in PHP by doing the following. Lets just say we have a Planet in our database already stored.

	$planet = Planet::find( 1 );

	$moons  = $planet->moons;


### Many To Many

Sometimes a model can have many and belong to many other models. For this we would have to create a middle table that contains both table primary keys. Therefore we can tie the relationship between to models. 

We define it in both our models. Lets say we have two different models called Group and User. A User can have many groups while a group can have many users. 

We need to create a table in our database called GroupsUsers. Inside that table we need to define two fields "user_id", and "group_id." 

Once the table is complete we need to specify the relationship in both of our models. They both go in the $has_many relationship. 

	// User.php
	protected $has_many = [ 'Groups' ];

	// Group.php
	protected $has_many = [ 'Users' ];

But wait! That didn't work. Thats because we need to tell Activerecord where our middle table is in our database. We can do this by using the "through" directive in our relationship. 

	// User.php
	protected $has_many = [ 
		'Groups' => [ 'trough' => 'GroupsUsers' ] 
	];

	// Group.php
	protected $has_many = [ 
		'Users' => [ 'through' => 'GroupsUsers' ] 
	];





### Aliasing Relationships

You can also alias the relationship that is referenced in PHP. This prevents name clashes with any fields that you have in your database table. Lets say I want to access the planet by calling "thePlanet" property instead of "planet." We will need to specify it in our relationship property.

	protected $belongs_to = [ 'Planet' => [ 'as' => 'thePlanet' ] ];

We just moved the namespace alias to "thePlanet." Now in PHP we can reference it just like the following.

	$moon = Moon::find( 1 );

	$planet = $moon->thePlanet;


























