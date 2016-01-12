<?php
namespace Api\Routers;
use \Api\Controllers\Controller;
use \Api\Views\View;
use \Api\Exception;


class REST extends Router
{
    
    private $_routes = array
    (
        '/setting'           => 'Setting',
        '/test'              => 'Test',
        '/settings/:id'      => 'Settings',
        '/users/:token'      => 'users',
        '/tickets/:type/:id' => 'tickets'
    );
    
    private $_controller;
    private $_method;
    private $_params = array();
    private $_format;
    private $_version;
    private $_error = 0;

    public function getController()
    {
        return $this->_controller;
    }
    
    public function getMethod()
    {
        return $this->_method;
    }
    
    public function getParams()
    {
        return $this->_params;
    }
    
    public function getVersion()
    {
        return $this->_version;
    }
    
    public function getFormat()
    {
        return $this->_format;
    }
    
    public function getError()
    {
        return $this->_error;
    }
    
    public function __construct()
    {
        // Определяем формат возвращаемых данных. Можно указать двумя способами:
        // 1. REST запросе, например /settings.json
        // 2. В заголовках запроса, в параметре Accept, например Accept: application/json
        // FORMAT_REST как формат, если формат никак не определить
        $this->_format = View::FORMAT_REST;
        if ( ($pos = strrpos($_GET['__'], '.')) !== false )
        {
            switch ( substr($_GET['__'], $pos) )
            {
                case '.json':
                {
                    $this->_format = View::FORMAT_REST_JSON;
                    break;
                }
                case '.xml':
                {
                    $this->_format = View::FORMAT_REST_XML;
                    break;
                }
                default:
                {
                    $this->_error = Exception::ROUTER_WRONG_FORMAT;
                    return;
                }
            }
        }
        else if ( isset($_SERVER['HTTP_ACCEPT']) )
        {
            $accepts = array_map('trim', explode(',', $_SERVER["HTTP_ACCEPT"]));
            foreach ( $accepts as $accept )
            {
                $elements = array_map('trim', explode(';', $accept));
                $format = '';
                switch ($elements[0])
                {
                    case 'application/json':
                    {
                        $format = View::FORMAT_REST_JSON;
                        break;
                    }
                    case 'application/xml':
                    {
                        $format = View::FORMAT_REST_XML;
                        break;
                    }
                }
                if ( $format )
                {
                    $this->_format = $format;
                    break;
                }
            }
            if ( !$format )
            {
                $this->_error = Exception::ROUTER_WRONG_FORMAT;
                return;
            }
        }
        if ( !preg_match('/^\/v([0-9]+)(.+?)(?:\.([a-z]+)|\/)?$/', $_GET['__'], $o) )
        {
            $this->_error = Exception::ROUTER_WRONG_PARAMS;
            return;
        }
        $path = $o[2];
        // версия API
        switch ( $o[1] )
        {
            case '0':
            {
                $this->_version = Controller::VER_0;
                break;
            }
            default:
            {
                $this->_error = Exception::ROUTER_WRONG_VERSION;
                return;
            }
        }
        // ищем подходящий роутинг
        foreach ( $this->_routes as $route => $controller )
        {
            $pattern = preg_replace('/\\\:[A-Za-z0-9]+/', '([_\-\=a-zA-Z0-9]+)', preg_quote($route, '/'));
            if ( preg_match("/^{$pattern}$/", $path, $values) )
            {
                $this->_controller = $controller;
                if ( count($values) > 1 )
                {
                    preg_match_all('/\:([A-Za-z0-9]+)/', $route, $keys);
                    for ( $i=0; $i<count($keys[1]); $i++ )
                    {
                        $this->_params[$keys[1][$i]] = $values[$i+1];
                    }
                }
                break;
            }
        }
        if ( !$this->_controller )
        {
            $this->_error = Exception::ROUTER_WRONG_CONTROLLER;
            return;
        }
        // определяем метод CRUD
        if ( $_SERVER['REQUEST_METHOD'] == 'GET' )
        {
            $this->_method = Controller::CRUD_READ;
        }
        if ( !$this->_method )
        {
            $this->_error = Exception::ROUTER_WRONG_METHOD;
            return;
        }
    }
    
}
