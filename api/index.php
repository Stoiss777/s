<?php

define('APPS_DIR',     __DIR__);
define('SOURCE_DIR',   APPS_DIR . '/source');
define('LIBS_DIR',     SOURCE_DIR . '/Libs');
define('MODELS_DIR',   SOURCE_DIR . '/Models');
define('BUILDERS_DIR', SOURCE_DIR . '/Builders');

require_once 'vendor/autoload.php';
//require_once MODELS_DIR . '/Settings.php';
//require_once MODELS_DIR . '/Model.php';
require_once SOURCE_DIR . '/Apps/Auth/Exception.php';
require_once SOURCE_DIR . '/Apps/Auth/Controllers/Controller.php';



// ___ AUTOLOAD MODELS AND LIBS ___________________________________________________________________
spl_autoload_register(function ($class) 
{
    if ( preg_match('/^Models\\\([\\w\\\]+)$/', $class, $o) )
    {
        include_once MODELS_DIR . '/' . str_replace('\\', '/', $o[1]) . '.php';
    }
    else if ( preg_match('/^Libs\\\([\\w\\\]+)$/', $class, $o) )
    {
        include_once LIBS_DIR . '/' . str_replace('\\', '/', $o[1]) . '.php';
    }
    else if ( preg_match('/^Builders\\\([\\w\\\]+)$/', $class, $o) )
    {
        include_once BUILDERS_DIR . '/' . str_replace('\\', '/', $o[1]) . '.php';
    }
});



Libs\KVDB\KVDB::initialize(function()
{
    return array
    (
        'timeout' => 5
    );
});



// ___ DEBUG FEATURES _____________________________________________________________________________
if ( isset($_GET['reset']) )
{
    $kvdb = new Libs\KVDB\KVDB();
    unset($kvdb->settings);
    
    $kvdb = new Libs\KVDB\KVDB('auth');
    unset($kvdb->settings);
    
    die('!');
}


// ___ DATABASE ___________________________________________________________________________________
Libs\DB\DB::initialize(function($configurator)
{
    $configurator->setConnection("host=192.168.7.1 user=jb password=death[fig]48 dbname=invitations");
});


// ___ AUTH APP FIRST _____________________________________________________________________________
if ( $_SERVER['HTTP_HOST'] == 'auth.invitations.stoiss.net' )
{
    
    try
    {
        $controller = Auth\Controllers\Controller::getInstance();
    }
    catch ( Auth\Exception $ex )
    {
        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    }
    
}

