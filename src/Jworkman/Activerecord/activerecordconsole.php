<?php
namespace Jworkman\Activerecord;


use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ActiverecordConsole extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'activerecord:console';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Activerecord Console. Control your objects in your database via console.';


    public function __construct()
    {
        parent::__construct();
    }


	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{



        \Event::listen('illuminate.query', function($query, $bindings, $time, $name)
        {
            $data = compact('bindings', 'time', 'name');

            // Format binding data for sql insertion
            foreach ($bindings as $i => $binding)
            {
                if ($binding instanceof \DateTime)
                {
                    $bindings[$i] = $binding->format('\'Y-m-d H:i:s\'');
                }
                else if (is_string($binding))
                {
                    $bindings[$i] = "'$binding'";
                }
            }

            // Insert bindings into query
            $query = str_replace(array('%', '?'), array('%%', '%s'), $query);
            $query = vsprintf($query, $bindings);

            print_r( TerminalColor::init()->getColoredString($query, 'yellow')."\r\n" );
        });

        for($i = 0; $i < 1; $i = 0) {

            echo "\r\n";
            $input = $this->ask('>');
            $input = trim( $input );
            $input = str_replace( '.', '->', $input );

            try {

                $value = eval( 'return ' . $input . ';' );
                echo \Jworkman\Activerecord\Library::examineCLIObject( $value );

            } catch(\Exception $e) {

            }
        }

        //$this->fire();

	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			//array('name', InputArgument::REQUIRED, 'Name of model.'),
            //array('fields', InputArgument::IS_ARRAY, 'Fields'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			//array('table', null, InputOption::VALUE_OPTIONAL, 'Table name the model maps to.', null),
		);
	}

}
