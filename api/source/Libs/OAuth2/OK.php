<?php
namespace Libs\OAuth2;


class OK extends OAuth2
{

    protected $tokenHttpMethod = 'POST';
    
    
    public function getService()
    {
        return self::SERV_OK;
    }
    
    public function getCodeUri()
    {
        return $this->getCodeUrl()
            . '?client_id=' . $this->getClientId()
            . '&scope=' . implode(';', $this->getScope())
            . '&redirect_uri=' . urlencode($this->getRedirectUri())
            . '&response_type=code' . (($state = $this->getState())? '&state=' . $state: '');
    }
    
    /*
    public function getUserId()
    {
        return isset($this->response->user_id)? $this->response->user_id: false;
    }

    public function getEmail()
    {
        return isset($this->response->email)? $this->response->email: false;
    }
    */
    
}
