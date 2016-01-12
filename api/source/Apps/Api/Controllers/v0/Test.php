<?php
namespace App\Controllers;
use App\Libs\DB;
use App\Libs\DB\Exception as DBException;
use App\Libs\DB\Result as QueryResult;
use App\Libs\DB\Row as Row;
use App\Libs\Logger;
use App\Models\Setting;

class Test extends \App\Controller
{
    
    public function allow()
    {
        return array(self::CRUD_READ);
    }
          
    
    protected function read()
    {

        //var_dump( Setting::access('private')->get('services.vk.auth.type') );
        //die;
        
        $oauth2 = \App\Libs\OAuth2::getInstance(\App\Libs\OAuth2::SERV_VK);
        Setting::configurator($oauth2);
        
        return true;
        
    }
    
}