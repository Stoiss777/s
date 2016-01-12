<?php
namespace Auth\Controllers\Callbacks;

use \Builders\Libs\OAuth2 as OAuth2Builder;
use \Libs\OAuth2\OAuth2 as OAuth2Lib;
use \Auth\Exception;
//use \Models\Settings;

require_once __DIR__ . '/Callback.php';



class OAuth2 extends Callback
{

    protected $lib;
    protected $data = array();

    
    protected function entry()
    {
        if ( !($this->lib = OAuth2Builder::getByKey($this->service->key)) )
        {
            $this->error(Authorization::ERR_SERVER_ERROR);
            return false;
        }
        try
        {
            if ( !($code = $this->lib->getCode($_GET)) )
            {
                throw new Exception(Exception::OAUTH2_WRONG_CODE, array
                (
                    'uri' => $_SERVER['REQUEST_URI'],
                    'err' => $this->lib->getError(),
                ));
            }
            if ( !$this->lib->requestToken($code) || !$this->lib->getToken() ) 
            {
                throw new Exception(Exception::OAUTH2_WRONG_TOKEN, array
                (
                    'uri'      => $this->lib->getTokenUri($code),
                    'err'      => $this->lib->getError(),
                    'response' => $this->lib->getResponseString(),
                ));
            }
            /*$this->data = array
            (
                'access_token'    => $this->lib->getToken(),
                'refresh_token'   => (($v = $this->lib->getRefreshToken()) === false)? null: $v,
                'expires_in'      => (($v = $this->lib->getExpiresIn()) === false)? null: $v,
                'server_response' => (($v = $this->lib->getResponseString()) === false)? null: $v
            );*/
            return true;
        } 
        catch ( Exception $ex ) 
        {
            switch ($this->lib->getError())
            {
                case OAuth2Lib::ERR_ACCESS_DENIED:
                case OAuth2Lib::ERR_UNATHORIZED_CLIENT:
                case OAuth2Lib::ERR_INVALID_CLIENT:
                {
                    $this->page1(self::ERR_ACCESS);
                }
                default:
                {
                    $this->page1(self::ERR_INTERNAL);
                }
            }
            return false;
        }
    }
    
    /*protected function save($userServiceKey)
    {
        if ( $user = parent::save($userServiceKey) )
        {
            $this->service->saveOAuth2
            (
                $userServiceKey, 
                $this->data['access_token'],
                $this->data['expires_in'],
                $this->data['refresh_token'],
                $this->data['server_response']
            );
            return $user;
        }
        return false;
    }*/
    
    protected function getUserId()
    {
        // @todo это потом дорабатывать
        return $this->lib->getUserId();
    }
    
    protected function getEmail()
    {
        // @todo это потом дорабатывать
        return $this->lib->getEmail();
    }
    
    protected function getEmailList()
    {
        return false;
    }

    protected function getPhone()
    {
        return $this->lib->getPhone();
    }
    
    protected function getPhoneList()
    {
        return false;
    }
    
}
