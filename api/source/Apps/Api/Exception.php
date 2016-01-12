<?php
namespace Api;
use Libs\Logger;

require_once APP_DIR . '/App/Libs/Logger.php';


class Exception extends \Exception
{

    /**
     * 1000 - 1999 -> внутренние ошибки приложения
     * 
     */
    const APP_BAD_CRUD    = 1000;  // функция CRUD вернула false без объяснения причины
    const APP_WRONG_MODEL = 1001;  // запрашиваемой модели не существует
    /**
     * 2000 - 2999 -> ошибки роутинга
     * 
     */
    const ROUTER_WRONG_PARAMS     = 2000;  // неправильный параметы
    const ROUTER_WRONG_FORMAT     = 2001;  // неподдерживаемый формат выходных данных
    const ROUTER_WRONG_VERSION    = 2002;  // неподдерживаемый версия API
    const ROUTER_WRONG_CONTROLLER = 2003;  // нет контроллера для данной операции
    const ROUTER_WRONG_METHOD     = 2004;  // операция не поддерживает данный метод
    const ROUTER_WRONG_SPECIAL    = 2005;  // нет контроллера для реализации специального роутинга
    /**
     * 3000 - 3999 -> ошибки special controllers
     * 3000 - 3099 -> auth
     */
    const SPEC_OAUTH2_WRONG_CODE     = 3000; // неправильный code или он не передан вообще
    const SPEC_OAUTH2_WRONG_TOKEN    = 3001; // неправильный token или возникли проблемы с его получением
    const SPEC_OAUTH2_WRONG_RESPONSE = 3002; // неправльный ответ с сервера (не все нужные значения получены)


    protected $_messages = array
    (
        self::APP_BAD_CRUD    => 'Unsupported CRUD operation.',
        self::APP_WRONG_MODEL => 'Model is not found.',
        
        self::ROUTER_WRONG_PARAMS     => 'Bad request.',
        self::ROUTER_WRONG_FORMAT     => 'Unsupported data format.',
        self::ROUTER_WRONG_VERSION    => 'Unsupported API version.',
        self::ROUTER_WRONG_CONTROLLER => 'Unknown method passed.',
        self::ROUTER_WRONG_METHOD     => 'Unsupported method.',
        self::ROUTER_WRONG_SPECIAL    => 'Special controller is broken.',
        
        self::LIB_OAUTH2_WRONG_MODEL => 'Model is not found.',
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
