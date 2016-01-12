<?php
namespace Libs\OAuth2;


class VK extends OAuth2
{

    protected $versionApi = '5.34';
    protected $tokenHttpMethod = 'GET';
    
    
    public function getService()
    {
        return self::SERV_VK;
    }
    
    public function getCodeUri()
    {
        return parent::getCodeUri() . '&v=' . $this->versionApi;
    }
    
    public function getUserId()
    {
        return isset($this->response->user_id)? $this->response->user_id: false;
    }

    public function getEmail()
    {
        return isset($this->response->email)? $this->response->email: false;
    }
    
}
