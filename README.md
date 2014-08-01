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

	name:String:Capitalize size:Integer star_id:Integer

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









