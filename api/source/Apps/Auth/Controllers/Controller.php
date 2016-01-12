<?php
namespace Auth\Controllers;
use Auth\Exception;

require_once __DIR__ . '/../Views/View.php';



abstract class Controller 
{
    
    
    static $routes = array
    (
        // a main entry point, users see this page when they want to register or login
        array
        (
            'route'  => '',
            'file'   => 'Index.php',
            'class'  => 'Index',
            'params' => null
        ),
        // ВКонтакте
        array
        (
            'route'  => '/callback/oauth2/vk',
            'file'   => 'Callbacks/OAuth2.php',
            'class'  => 'Callbacks\\OAuth2',
            'params' => 'vk'
        ),
        // Одноклассники
        array
        (
            'route'  => '/callback/oauth2/ok',
            'file'   => 'Callbacks/OAuth2.php',
            'class'  => 'Callbacks\\OAuth2',
            'params' => 'ok'
        )
    );
    
    
    public static function getInstance()
    {
        foreach ( self::$routes as $value )
        {
            if ( preg_match('/^' . preg_quote($value['route'], '/') . '\/?$/', $_GET['__']) )
            {
                if ( is_readable(__DIR__ . '/' . $value['file']) )
                {
                    require_once __DIR__ . '/' . $value['file'];
                    $class = __NAMESPACE__ . '\\' . $value['class'];
                    if ( is_null($value['params']) )
                    {
                        return new $class;
                    }
                    else
                    {
                        return new $class($value['params']);
                    }
                }
            }
        }
        throw new Exception(Exception::WRONG_CONTROLLER);
    }
    
    
}
