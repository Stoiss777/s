<?php
namespace Api\Views\REST;
use \Api\Controllers\Controller;
use \Api\Exception;
/**
 * Представление для работы с JSON через REST
 * 
 */
class JSON extends \Api\Views\REST
{
    
    public function data($data)
    {
        parent::data($data);
        header("Content-Type: application/json");
        header('Access-Control-Allow-Origin: *'); // @todo Этот момент обязательно продумать!!!
        echo json_encode($data);
        /*echo json_encode(array
        (
            'success' => 1,
            'data'    => $data
        ));*/
    }
    
    public function error(Exception $error)
    {
        parent::error($error);
        header("Content-Type: application/json");
        $data = array
        (
            'success' => 0,
            'error'   => array
            (
                'code'    => $error->getCode(),
                'message' => $error->getMessage()
            )
        );
        if ( ($error->getCode() == Exception::ROUTER_WRONG_METHOD) && ($params = $error->getParams()) )
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
            $data['error']['allow'] = implode(',', $str);
        }
        echo json_encode($data);
    }
    
}
