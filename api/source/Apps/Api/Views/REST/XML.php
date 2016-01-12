<?php
namespace Api\Views\REST;
use \Api\Exception;
/**
 * Представление для работы с XML через REST
 * 
 */
class XML extends \Api\Views\REST
{

    private function _arrayToXML(array $data)
    {
        $xml = new \SimpleXMLElement('<root/>');
        array_walk_recursive($data, array ($xml, 'addChild'));
        return $xml->asXML();
    }
    
    public function error(Exception $error)
    {
        parent::error($error);
        header("Content-Type: application/xml");
        echo $this->_arrayToXML(array
        (
            'error' => array
            (
                'code'    => $error->getCode(),
                'message' => $error->getMessage()
            )
        ));
    }
    
    
}
