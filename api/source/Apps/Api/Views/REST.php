<?php
namespace Api\Views;
use \Api\Controllers\Controller;
use \Api\Exception;
/**
 * Представление для работы с REST.
 * 
 * Само по себе данные не выводит, только заголовки.
 * Используется как родитель для остальных типов работающих через REST
 * или когда нужное представление определить не удалось, чтобы хоть как-то
 * уведомить клиента об ошибке.
 * 
 */
class REST extends View
{

    public function data($data)
    {
        header($_SERVER["SERVER_PROTOCOL"] . ' 200 OK');
    }
    
    public function error(Exception $error)
    {
        switch ( $error->getCode() )
        {
            case Exception::ROUTER_WRONG_PARAMS:
            case Exception::ROUTER_WRONG_FORMAT:
            case Exception::ROUTER_WRONG_VERSION:
            {
                header($_SERVER["SERVER_PROTOCOL"] . ' 400 Bad Request');
                break;
            }
            case Exception::ROUTER_WRONG_CONTROLLER:
            {
                header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
                break;
            }
            case Exception::ROUTER_WRONG_METHOD:
            {
                header($_SERVER["SERVER_PROTOCOL"] . ' 405 Method Not Allowed');
                if ( ($params = $error->getParams()) )
                {
                    $str = array();
                    $codes = array
                    (
                        Controller::CRUD_CREAT  => 'POST',
                        Controller::CRUD_READ   => 'GET',
                        Controller::CRUD_UPDATE => 'PUT',
                        Controller::CRUD_DELETE => 'DELETE',
                    );
                    foreach ( $params as $v )
                    {
                        $str[] = $codes[$v];
                    }
                    header('Allow: ' . implode(',', $str));
                }
                break;
            }
        }
    }
    
}
