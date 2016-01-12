<?php
namespace Models\Entities;

use Libs\DB\DB;



class Service extends Entity
{
    
    public $id;
    public $key;
    public $auth;  // @todo <-- использовать константы??? и надо ли оно вообще???
    
    public static function getByKey($key)
    {
        if ( !($row = DB::schema('services')->getByKey($key)->first()) )
        {
            return false;
        }
        $service = new self;
        $service->id   = $row->id;
        $service->key  = $row->key;
        $service->auth = $row->auth;
        return $service;
    }
    
    public static function getByUserId($userId)
    {
        $ret = array();
        $res = DB::schema('services')->getByUserId($userId);
        while ( $row = $res->fetch() )
        {
            $service = new self;
            $service->id   = $row->id;
            $service->key  = $row->key;
            $service->auth = $row->auth;
            $ret[] = $service;
        }
        return $ret? $ret: false;
    }
    
    
    /*public function bindServerData($userServiceKey, $data)
    {
        switch ($this->auth)
        {
            case 'oauth2':
            {
                DB::schema('service')->saveOauth2($userServiceKey, );
            }
        }
    }*/
    
    /*public function saveOAuth2($userServiceKey, $accessToken, $expiresIn, $refreshToken, $serverResponse)
    {
        DB::schema('services')->saveOauth2($userServiceKey, $accessToken, $expiresIn, $refreshToken, $serverResponse);
    }*/
    
    public function create()
    {
        return false;
    }
    
}
