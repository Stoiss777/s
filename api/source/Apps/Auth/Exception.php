<?php
namespace Auth;
use Libs\Logger;


class Exception extends \Exception
{

    const WRONG_CONTROLLER      = 1000; // несуществующий контроллер
    const OAUTH2_WRONG_CODE     = 2000; // неправильный code или он не передан вообще
    const OAUTH2_WRONG_TOKEN    = 2001; // неправильный token или возникли проблемы с его получением
    const OAUTH2_WRONG_RESPONSE = 2002; // неправльный ответ с сервера (не все нужные значения получены)

    protected $_messages = array
    (
        self::WRONG_CONTROLLER      => 'Unknown controller passed.',
        self::OAUTH2_WRONG_CODE     => 'OAuth2: Wrong code.',
        self::OAUTH2_WRONG_TOKEN    => 'OAuth2: Wrong token.',
        self::OAUTH2_WRONG_RESPONSE => 'OAuth2: Wrong server response.',
    );
    
    
    protected $_params = array();
    
    
    public function __construct($code, array $params = array(), Exception $previous = null) 
    {
        $message = isset($this->_messages[$code])? $this->_messages[$code]: '';
        $this->_params = $params;
        parent::__construct($message, $code, $previous);
        Logger::exception($this);
    }
    
    public function getParams()
    {
        return $this->_params;
    }
    
    
}
