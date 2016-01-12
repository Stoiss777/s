<?php
namespace Api\Controllers;
use \Api\Exception;
use \Models\Setting;
use \stdClass;


class Setting extends Controller
{
    
    public function allow()
    {
        return array(self::CRUD_READ);
    }

    
    protected function read()
    {
        if ( !isset($_GET['key']) )
        {
            throw new Exception(Exception::ROUTER_WRONG_PARAMS);
        }
        $key = $_GET['key'];
        $this->data = new stdClass;
        $setting = Setting::access('public')->get($key);
        if ( $key[strlen($key)-1] == '*' )
        {
            foreach ( $setting as $k => $v )
            {
                if ( is_scalar($v) )
                {
                    $this->data->$k = $v;
                }
                else
                {
                    $this->data->$k = json_encode($v);
                }
            }
        }
        else
        {
            $this->data->$key = $setting;
        }
        return true;
    }
    
}