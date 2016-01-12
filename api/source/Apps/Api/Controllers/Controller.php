<?php
namespace \Api\Controllers;
use \Api\Routers\Router;
use \Api\Views\View;
use \Api\Exception;


abstract class Controller
{

    /**
     * Константы для CRUD методов
     * 
     */
    const CRUD_CREAT  = 1;
    const CRUD_READ   = 2;
    const CRUD_UPDATE = 3;
    const CRUD_DELETE = 4;

    /**
     * Варианты доступных версий API
     * 
     */
    const VER_0 = 0;
    
    
    protected $params = array();
    protected $data;
    protected $view;
    
    // @todo __DIR__ везде использовать
    public static function getInstance(Router $router)
    {
        $name = $router->getController();
        $ver  = $router->getVersion();
        if ( is_readable(__DIR__ . "/v{$ver}/{$name}.php") )
        {
            require_once __DIR__ . "/v{$ver}/{$name}.php";
            //$name = '\\Api\\Controllers\\' . $name;
            return new $name(View::getInstance($router), $router->getParams());
        }
        else
        {
            throw new Exception(Exception::ROUTER_WRONG_CONTROLLER);
        }
    }
    
    public function __construct(View $view, array $params)
    {
        $this->view = $view;
        $this->params = $params;
    }
    
    /**
     * 
     * @param int $method
     * @return boolean
     * @throws AppException
     */
    public function action($method)
    {
        if ( !in_array($method, $this->allow()) )
        {
            throw new Exception(Exception::ROUTER_WRONG_METHOD, $this->allow());
        }
        switch ($method)
        {
            case self::CRUD_READ:
            {
                if ( $this->read() )
                {
                    try
                    {
                        $this->view->data($this->data);
                    }
                    catch (Exception $ex)
                    {
                        $this->view->error($ex);
                    }
                }
                else
                {
                    throw new Exception(Exception::APP_BAD_CRUD);
                }
                break;
            }
        }
    }
    
    /**
     * Возвращает список CRUD методов с которыми разрешенно работать клиенту.
     * ( константы self::CRUD_* )
     * 
     * @return array - массив с разрешенными методами
     */
    public abstract function allow();
    
    /**
     * Реализует метод READ ( константа self::CRUD_READ )
     * 
     * @return boolean - успех операции
     */
    protected abstract function read();
    
}

