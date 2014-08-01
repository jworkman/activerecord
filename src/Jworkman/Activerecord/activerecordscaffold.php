<?php
namespace Jworkman\Activerecord;


use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ActiverecordScaffold extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'activerecord:scaffold';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a restful controller, and activerecord model.';

    private $terminalColors;

    public function __construct()
    {
        parent::__construct();
        $this->terminalColors = new TerminalColor();
    }

    public function trimAllItems( &$items )
    {

        for($i = 0, $j = count($items); $i < $j; $i++) {

            $items[$i] = trim($items[$i]);

        }

        return $items;

    }

    public function ucAllItems(&$items) {
        for($i = 0, $j = count($items); $i < $j; $i++) {

            $items[$i] = ucfirst($items[$i]);

        }

        return $items;
    }

    public function specialTypes( &$items )
    {
        for($i = 0, $j = count($items); $i < $j; $i++) {

            switch( strtolower($items[$i]) ) {
                case 'datetime':
                    $items[$i] = "DateTime";
                    break;

                case 'timestamp':
                    $items[$i] = "TimeStamp";
                    break;
                case 'lowercase':
                    $items[$i] = "LowerCase";
                    break;
                case 'uppercase':
                    $items[$i] = "UpperCase";
                    break;
            }

        }

        return $items;
    }

    public function getInterfaceStr()
    {

        $chunks = explode( ' ', $this->ask('Specify field:interfaces separated by space:') );
        $chunks = $this->trimAllItems( $chunks );
        $fields = array();

        for($i = 0, $j = count($chunks); $i < $j; $i++) {

            $fieldStr = "";
            $fieldSchema    = explode( ':', $chunks[$i] );
            $fieldSchema    = $this->trimAllItems( $fieldSchema );
            $fieldSchema    = $this->specialTypes( $fieldSchema );
            $fieldName      = array_shift($fieldSchema);
            $fieldSchema    = $this->ucAllItems($fieldSchema);
            $fieldStr       .= "'".$fieldName."' => '".implode(':', $fieldSchema)."',";
            array_push( $fields, $fieldStr );

        }

        return $fields;

    }


    public function fire()
    {


        $replacements = array(
            'Singular' => ucfirst(str_singular( $this->argument('name') )),
            'SingularLower' => strtolower(str_singular( $this->argument('name') )),
            'Raw' => $this->argument('name'),
            'Plural' => str_plural( $this->argument('name') ),
            'PluralLower' => strtolower(str_plural( $this->argument('name') )),
            'Interfaces' => implode( "\r\n\t\t", $this->getInterfaceStr() )
        );


        $this->comment("Generating a controller and model for " . $replacements['Singular'] . '...');

        $controllerType = $this->ask("What type of a controller would you like? [json,view,none]");

        $modelTmpl      = file_get_contents( __DIR__ . '/scaffolds/' . 'model' );
        $controllerTmpl = file_get_contents( __DIR__ . '/scaffolds/' . 'controller_'.trim(strtolower($controllerType)) );

        foreach($replacements as $key => $value) {
            $controllerTmpl = str_replace( '{{'.$key.'}}', $value, $controllerTmpl);
            $modelTmpl = str_replace( '{{'.$key.'}}', $value, $modelTmpl);
        }

        $this->comment("Generated model and controller. Writing to files...");

        // Write the scaffolds
        if( trim(strtolower($controllerType)) !== "none" || trim(strtolower($controllerType)) !== "none" ) {

            $this->writeFile( app_path() . "/controllers/" . $replacements['Singular'] . 'Controller.php', $controllerTmpl );

        }

        $this->writeFile( app_path() . "/models/" . $replacements['Singular'] . '.php', $modelTmpl );


        $this->comment( "Controller & model were successfully created..." );
        $routes = $this->ask("Would you like me to update your routes file? [y/n]", 'y');

        if( trim(strtolower($routes)) === "y" || trim(strtolower($routes)) === "yes" ) {

            $this->comment( "Adding RESTful resource route to configuration..." );
            $routesContent = file_get_contents( app_path() . '/routes.php' );
            $routesContent .= "\r\n" . "Route::resource('". $replacements['PluralLower'] ."', '". $replacements['Singular'] . 'Controller' ."');";
            file_put_contents( app_path() . '/routes.php', $routesContent );
            $this->comment( "Added resource route to configuration..." );

        }

    }


    public function writeFile( $path, $contents )
    {

        $errorStr = $this->terminalColors->getColoredString("Could Not Create: ", 'red');
        $successStr = $this->terminalColors->getColoredString("Created: ", 'green');

        if( file_exists( $path ) ) {
            $overwrite = $this->ask('Overwrite '.$path.'? [y/n]', 'y');
            if(!$overwrite || strtolower($overwrite) == 'n')
                return true;
        }

        if( file_put_contents( $path, $contents ) ) {
            $this->comment($successStr . $path);
        } else {
            $this->comment($errorStr . $path);
        }
    }


    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('name', InputArgument::REQUIRED, 'Name of model.'),
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