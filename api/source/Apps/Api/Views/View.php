<?php
namespace Api\Views;
use \Api\Routers\Router;


abstract class View
{
    
    /**
     * REST формат без выходных данных, используется
     * только в том случае, если тип возвращаемых данных не определен,
     * чтобы хоть как-то сообщить клиенту об ошибке.
     * 
     */
    const FORMAT_REST      = 1;
    /**
     * Формат JSON в рамках REST
     * 
     */
    const FORMAT_REST_JSON = 2;
    /**
     * Формат XML в рамках REST
     * 
     */
    const FORMAT_REST_XML  = 3;
    
    
    public static function getInstance(Router $router)
    {
        switch ( $router->getFormat() )
        {
            case self::FORMAT_REST:
            {
                require_once __DIR__ . '/REST.php';
                return new REST($sl);
            }
            case self::FORMAT_REST_JSON:
            {
                require_once __DIR__ . '/REST.php';
                require_once __DIR__ . '/REST/JSON.php';
                return new REST\JSON($sl);
            }
            case self::FORMAT_REST_XML:
            {
                require_once __DIR__ . '/REST.php';
                require_once __DIR__ . '/REST/XML.php';
                return new REST\XML($sl);
            }
        }
    }

    public abstract function data($data);
    public abstract function error(Exception $error);
    
}