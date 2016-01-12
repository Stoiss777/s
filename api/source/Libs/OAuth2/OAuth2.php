<?php
namespace Libs\OAuth2;


abstract class OAuth2 
{
    
    /**
     * If error is not one of the below errors.
     * 
     */
    const ERR_UNKNOWN = 1;
    
    /**
     * Error = server_error
     * 
     * The authorization server encountered an unexpected
     * condition that prevented it from fulfilling the request.
     * (This error code is needed because a 500 Internal Server
     * Error HTTP status code cannot be returned to the client
     * via an HTTP redirect.)
     *
     * This error also informs when client does not connect to 
     * an authorization server.
     */
    const ERR_SERVER_ERROR = 2;
    
    /**
     * Error = temporary_unavailable
     * 
     * The authorization server is currently unable to handle
     * the request due to a temporary overloading or maintenance
     * of the server. (This error code is needed because a 503
     * Service Unavailable HTTP status code cannot be returned
     * to the client via an HTTP redirect.)
     */
    const ERR_TEMORARY_UNAVAILABLE = 3;
    
    /**
     * This error is not in RFC. It shows that response from server
     * is missing a required parameter, includes an invalid parameter 
     * value, includes a parameter more than once, or is otherwise malformed.
     * 
     */
    const ERR_INVALID_RESPONSE = 4;
    
    /**
     * Error = invalid_request
     * 
     * The request is missing a required parameter, includes an
     * invalid parameter value, includes a parameter more than
     * once, or is otherwise malformed.
     */
    const ERR_INVALID_REQUEST = 5;
    
    /**
     * Error = invalid_client
     * 
     * Client authentication failed (e.g., unknown client, no
     * client authentication included, or unsupported
     * authentication method)
     */
    const ERR_INVALID_CLIENT = 6;
    
    /**
     * Error = invalid_grant
     * 
     * The provided authorization grant (e.g., authorization
     * code, resource owner credentials) or refresh token is
     * invalid, expired, revoked, does not match the redirection
     * URI used in the authorization request, or was issued to
     * another client.
     */
    const ERR_INVALID_GRANT = 7;
    
    /**
     * Error = invalid_scope
     * 
     * The requested scope is invalid, unknown, or malformed.
     */
    const ERR_INVALID_SCOPE = 8;

    /**
     * Error = access_denied
     * 
     * The resource owner or authorization server denied the request.
     */
    const ERR_ACCESS_DENIED = 9;
    
    /**
     * Error = unauthorized_client
     * 
     * The client is not authorized to request an authorization
     * code using this method.
     */
    const ERR_UNATHORIZED_CLIENT = 10;
    
    /**
     * Error = unsupported_grant_type
     *
     * The authorization grant type is not supported by the
     * authorization server.
     */
    const ERR_UNSUPPORTED_GRANT_TYPE = 11;
    
    /**
     * Error = unsupported_response_type
     * 
     * The authorization server does not support obtaining an
     * authorization code using this method.
     */
    const ERR_UNSUPPORTED_RESPONSE_TYPE = 12;
    

    
    /**
     * Сервис ВКонтакте (https://vk.com)
     * 
     */
    const SERV_VK = 1;
    
    /**
     * Сервис Одноклассники (http://ok.ru/)
     * 
     */
    const SERV_OK = 2;

    /**
     * Статический массив с данными для подгрузки нужных классов сервисов.
     * (класс выполняет роль абстрактной фабрики)
     * 
     * key - уникальный "ключ" в виде строки, однозначно определяющий сервис
     * class - имя класса наследника который нужно подгрузить для реализации сервиса
     * 
     * @var array
     */
    public static $classes = array
    (
        self::SERV_VK => array( 'key' => 'vk', 'class' => 'VK' ),
        self::SERV_OK => array( 'key' => 'ok', 'class' => 'OK' )
    );
    
    
    /**
     * client_id 
     * @see https://tools.ietf.org/html/rfc6749
     * Определяется в наследнике.
     * 
     * @var string
     */
    protected $clientId;
    
    /**
     * client_secret
     * @see https://tools.ietf.org/html/rfc6749
     * Определяется в наследнике.
     * 
     * @var string
     */
    protected $clientSecret;
    
    /**
     * redirect_uri
     * @see https://tools.ietf.org/html/rfc6749
     * Определяется в наследнике.
     * 
     * @var string
     */
    protected $redirectUri;
    
    /**
     * scope
     * @see https://tools.ietf.org/html/rfc6749
     * Определяется в наследнике.
     * 
     * @var array
     */
    protected $scope;
    
    /**
     * state
     * @see https://tools.ietf.org/html/rfc6749
     * Определяется в наследнике.
     * 
     * @var string
     */
    protected $state;
    
    /**
     * URL для авторизации пользователя 
     * и получения кода для обмена на токен.
     * 
     * @var string
     */
    protected $codeUrl;
    
    /**
     * URL для запросов получения токена.
     * Именно URL, а не URI. URI формируется в self::getTokenUri().
     * 
     * @var string
     */
    protected $tokenUrl;
    
    /**
     * HTTP метод (GET или POST) с помощью которого 
     * делать запросы к self::$tokenUrl
     * 
     * @var string
     */
    protected $tokenHttpMethod;
    
    
    
    /**
     * Объект с ответом сервера после запроса токена
     * 
     * @var \stdClass
     */
    protected $response = null;
    
    /**
     * Строка с ответом сервера после запроса токена.
     * (plain text)
     * 
     * @var string
     */
    protected $responseString = false;
    
    /**
     * HTTP код ответа сервера после запроса токена.
     * 
     * @var integer
     */
    protected $responseHttpCode = false;
    
    
    
    /**
     * Номер последней ошибки (см. константы self::ERR_*)
     * 0 - нет ошибок     * 
     * @var integer
     */
    protected $error = 0;
    
    /**
     * Ошибка которую венул сервер oauth2 сервиса в виде строки.
     * Для более подробной информации @see https://tools.ietf.org/html/rfc6749#section-5.2
     * 
     * @var string
     */
    protected $errorString = '';
    
    /**
     * Описание ошибки из параметра error_description.
     * Для более подробной информации @see https://tools.ietf.org/html/rfc6749#section-5.2
     * 
     * @var string
     */
    protected $errorDescription = '';
    
    
    
    // _______________________ СТАТИЧЕСКИЕ МЕТОДЫ АБСТРАКТНОЙ ФАБРИКИ __________________________

    /**
     * Возвращает экземпляр класса выбранного наследника
     * 
     * @param string $service - номер сервиса (см. константы self::SERV_*)
     * @param array|null - если указан, то элементы массива (или свойства объекта) $settings станут 
     *                     свойствами созданного экзмемпляра. Удобно, если настройки сервисов нужно 
     *                     передавать откуда-то из вне.
     * @return self|boolean - экземпляр класса или false, если такого класса нет
     */
    public static function getInstance($service)
    {
        $name = self::$classes[$service]['class'];
        if ( is_readable(__DIR__ . '/' . $name . '.php') )
        {
            require_once __DIR__ . '/' . $name . '.php';
            $class = __NAMESPACE__ . '\\' . $name;
            $instance = new $class;
            return $instance;
        }
        return false;
    }
    
    /**
     * Возвращает номер сервиса (см. константы self::SERV_*) при указании
     * ключа сервиса (см. self::$classes['key'])
     * 
     * @param string $key      - "ключ" сервиса
     * @return integer|boolean - номер сервиса или false, если ничего не найдено
     */
    public static function getServiceByKey($key)
    {
        foreach ( self::$classes as $k => $v )
        {
            if ( $v['key'] == $key )
            {
                return $k;
            }
        }
        return false;
    }
    
    
    
    // ___________________________________ РАЗНОЕ ____________________________________________
    
    /**
     * Возвращает номер сервиса с которым работает экземпляр класса (см. константы SERV_*).
     * 
     */
    public abstract function getService();
    
    
    
    // ______________________________ СЕТТЕР НАСТРОЕК СЕРВИСА _________________________________
    
    /**
     * Устанавливает значение redirect_uri сервиса
     * @see https://tools.ietf.org/html/rfc6749
     * 
     * @param string - значение redirect_uri
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;
    }
    
    /**
     * Устанавливает значение client_id сервиса
     * @see https://tools.ietf.org/html/rfc6749
     * 
     * @param string - значение client_id
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }
    
    /**
     * Устанавливает значение client_secret сервиса
     * @see https://tools.ietf.org/html/rfc6749
     * 
     * @param string - значение client_secret
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;
    }
    
    /**
     * Устанавливает значение scope сервиса
     * @see https://tools.ietf.org/html/rfc6749
     * 
     * @param string - значение scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
    }
    
    /**
     * Устанавливает значение state сервиса
     * @see https://tools.ietf.org/html/rfc6749
     * 
     * @param string - значение state
     */
    public function setState($state)
    {
        $this->state = $state;
    }
    
    /**
     * Устанавливает значение code_url сервиса
     * @see https://tools.ietf.org/html/rfc6749
     * 
     * @param string - значение code_url
     */
    public function setCodeUrl($codeUrl)
    {
        $this->codeUrl = $codeUrl;
    }
    
    /**
     * Устанавливает значение token_url сервиса
     * @see https://tools.ietf.org/html/rfc6749
     * 
     * @param string - значение token_url
     */
    public function setTokenUrl($tokenUrl)
    {
        $this->tokenUrl = $tokenUrl;
    }
    
    
    // __________________________ ГЕТТЕРЫ НАСТРОЕК СЕРВИСА ____________________________
    
    /**
     * Возвращает значение redirect_uri сервиса
     * @see https://tools.ietf.org/html/rfc6749
     * 
     * @return string
     */
    protected function getRedirectUri()
    {
        return $this->redirectUri;
    }
    
    /**
     * Возвращает значение client_id сервиса
     * @see https://tools.ietf.org/html/rfc6749
     * 
     * @return string
     */
    protected function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Возвращает значение client_secret сервиса
     * @see https://tools.ietf.org/html/rfc6749
     * 
     * @return string
     */
    protected function getClientSecret()
    {
        return $this->clientSecret;
    }
    
    /**
     * Возвращает значение scope сервиса
     * @see https://tools.ietf.org/html/rfc6749
     * 
     * @return array
     */
    protected function getScope()
    {
        return $this->scope;
    }
    
    /**
     * Возвращает значение state сервиса
     * @see https://tools.ietf.org/html/rfc6749
     * 
     * @return string
     */
    protected function getState()
    {
        return $this->state;
    }
    
    /**
     * Возвращает URL для авторизации пользователя 
     * и получения кода для обмена на токен.
     * 
     * @return string
     */
    protected function getCodeUrl()
    {
        return $this->codeUrl;
    }
    
    /**
     * Возвращает URL для запросов получения токена.
     * Именно URL, а не URI. URI возвращает self::getCodeUri()
     * 
     * @return string
     */
    protected function getTokenUrl()
    {
        return $this->tokenUrl;
    }
    
    /**
     * Возвращает HTTP метод (GET или POST) с помощью которого 
     * делать запросы к self::$tokenUrl
     * 
     * @var string
     */
    protected function getTokenHttpMethod()
    {
        return $this->tokenHttpMethod;
    }
    
    
    // _______________________________  КОД ОБМЕНА НА ТОКЕН ___________________________________
    
    /**
     * Возвращает URI страницы авторизации пользователя на внешем сервисе, и с которой
     * произойдет редирект на URI содержащим код обмена.
     * 
     * @return string
     */
    public function getCodeUri()
    {
        return $this->getCodeUrl()
            . '?client_id=' . $this->getClientId()
            . '&scope=' . implode(',', $this->getScope())
            . '&redirect_uri=' . urlencode($this->getRedirectUri())
            . '&response_type=code' . (($state = $this->getState())? '&state=' . $state: '');
    }
    
    /**
     * Разбирает результат полученный редиректом после посещения пользователем страницы
     * self::getCodeUri().
     * 
     * @param array $params  - Массив с значениями редиректа (обычно $_GET).
     * @return boolean|array - Код обмена на токен или false в случае ошибки.
     *                         В случае ошибки смотри self::getError()
     */
    public function getCode(array $params)
    {
        if ( isset($params['code']) && ($params['code'] != '') )
        {
            return $params['code'];
        }
        $this->setError((object) $params);
        return false;
    }
    
    
    
    // _______________________________ ОБРАБОТКА ТОКЕНА ___________________________________
    
    /**
     * Возвращает URI запроса к ouath2 серверу по которому можно получить токен
     * 
     * @param  string $code - код обмена на токен
     * @return string       - uri запроса
     */
    public function getTokenUri($code)
    {
        return $this->getTokenUrl()
            . '?client_id=' . $this->getClientId()
            . '&client_secret=' . $this->getClientSecret()
            . '&code=' . $code
            . '&redirect_uri=' . urlencode($this->getRedirectUri())
            . '&grant_type=authorization_code';
    }
    
    /**
     * Выполняет запрос для получения токена
     * 
     * @param  string $code - код обмена на токен
     * @return boolean      - успех (ошибку можно посмотреть в self::getError())
     */
    public function requestToken($code)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->getTokenUri($code));
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if ( $this->getTokenHttpMethod() == 'POST' )
        { 
            curl_setopt($curl, CURLOPT_POST, true); 
        }
        if ( ($this->responseString = curl_exec($curl)) === false )
        {
            $this->error = self::ERR_SERVER_ERROR;
            return false;
        }
        $this->responseHttpCode  = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ( $this->response = json_decode($this->responseString) )
        {
            if ( $this->responseHttpCode == 200 && !(isset($this->response->error) && $this->response->error != '') )
            {
                if ( $this->checkResponseToken() )
                {
                    return true;
                }
                $this->error = self::ERR_INVALID_RESPONSE;
                return false;
            }
        }
        $this->setError($this->response);
        return false;
    }
    
    /**
     * Проверяет наличие и правильность всех параметров в ответе 
     * сервера после получения токена @see self::requestToken().
     * 
     * В это классе проверяет только наличие токена, для остальных
     * параметров нужно переопределить в наследниках.
     * 
     * @return boolen - успех
     */
    protected function checkResponseToken()
    {
        if ( isset($this->response->access_token) && $this->response->access_token != '' )
        {
            return true;
        }
        return false;
    }

    public function requestRefreshToken($refreshToken)
    {
        // code here
    }
    

    /**
     * Возвращает объект с ответом сервера после запроса токена
     * 
     * @return \stdClass - объект с ответом или null
     */
    public function getResponse()
    {
        return $this->response;
    }    

    /**
     * Строка строку с ответом сервера после запроса токена.
     * (plain text)
     * 
     * @return string - строка с ответом
     */
    public function getResponseString()
    {
        return $this->responseString;
    }

    /**
     * Возвращает HTTP код ответа сервера после запроса токена.
     * 
     * @var integer|boolean - код ответа или false, если запроса не было
     */
    public function getResponseHttpCode()
    {
        return $this->responseHttpCode;
    }
    
    /**
     * Возвращает токен после self::requestToken() или self::refreshToken()
     * 
     * @return string|boolean - строка с токеном или false, если токен не получен
     */
    public function getToken()
    {
        return isset($this->response->access_token)? $this->response->access_token: false;
    }
    
    /**
     * Возвращает ttl токена в секунда после self::requestToken() или self::refreshToken()
     * 
     * @return intger|boolean - количество секунд или false, если токен не получен.
     *                          0 - токен действует бессрочно
     */
    public function getExpiresIn()
    {
        if ( isset($this->response->access_token) )
        {
            return isset($this->response->expires_in)? $this->response->expires_in: 0;
        }
        return false;
    }
    
    /**
     * Возвращает токен обновления после self::requestToken() или self::refreshToken()
     * 
     * @return string|boolean - строка с токеном обновления или false, если такой токен не получен
     */
    public function getRefreshToken()
    {
        return isset($this->response->refresh_token)? $this->response->refresh_token: false;
    }

    /**
     * Возвращает ID пользователя в сервисе, если он возвращается вместе с токеном.
     * 
     * @return string|boolean - ID пользователя или false, если он неизвестен.
     */
    public function getUserId()
    {
        return false;
    }

    /**
     * Возвращает email пользователя в сервисе, если он возвращается вместе с токеном.
     * 
     * @return string|boolean - email пользователя или false, если он неизвестен.
     */
    public function getEmail()
    {
        return false;
    }

    /**
     * Возвращает номер телефона пользователя в сервисе, если он возвращается вместе с токеном.
     * 
     * Внимание! Номер должен передаваться в международном формате, с ведущим плюсом, без
     * пробелов, тире, скобок и пр., Например: +79881234567, +380441234567 и т.д.
     * 
     * @return string|boolean - номер телефона пользователя или false, если он неизвестен.
     */
    public function getPhone()
    {
        return false;
    }


    // ______________________  ОБРАБОТКА ОШИБОК ____________________________
    
    /**
     * Обрабатывает ошибки которые возвращает удаленный oauth2 сервис.
     * Ошибки обрабатываются согласно rfc @see https://tools.ietf.org/html/rfc6749
     * Для нестандартных сервисов метод можно переопределить.
     * 
     * @param \stdClass $response - содержание ответа сервера
     */
    protected function setError(\stdClass $response)
    {
        if ( !isset($response->error) || $response->error == '' )
        {
            $this->error = self::ERR_INVALID_RESPONSE;
            return;
        }
        switch ( strtolower($response->error) ) 
        {
            case 'invalid_request':
            {
                $this->error = self::ERR_INVALID_REQUEST;
                break;
            }
            case 'invalid_client':
            {
                $this->error = self::ERR_INVALID_CLIENT;
                break;
            }
            case 'invalid_grant':
            {
                $this->error = self::ERR_INVALID_GRANT;
                break;
            }
            case 'unauthorized_client':
            {
                $this->error = self::ERR_UNATHORIZED_CLIENT;
                break;
            }
            case 'unsupported_grant_type':
            {
                $this->error = self::ERR_UNSUPPORTED_GRANT_TYPE;
                break;
            }
            case 'invalid_scope':
            {
                $this->error = self::ERR_INVALID_SCOPE;
                break;
            }
            case 'access_denied':
            {
                $this->error = self::ERR_ACCESS_DENIED;
                break;
            }
            case 'unsupported_response_type':
            {
                $this->error = self::ERR_UNSUPPORTED_RESPONSE_TYPE;
                break;
            }
            case 'server_error':
            {
                $this->error = self::ERR_SERVER_ERROR;
                break;
            }
            case 'temporarily_unavailable':
            {
                $this->error = self::ERR_TEMORARY_UNAVAILABLE;
                break;
            }
            default:
            {
                $this->error = self::ERR_UNKNOWN;
                break;
            }
        }
        if ( isset($response->error_description) )
        {
            $this->errorDescription = $response->error_description;
        }
        $this->errorString = $response->error;
    }
    
    /**
     * Возвращает номер последней ошибки (см. константы self::ERR_*)
     * или ноль, если ошибок нет.
     * 
     * @return integer
     */
    public function getError()
    {
        return $this->error;
    }
    
    /**
     * Возвращает ошибку которую венул сервер oauth2 сервиса в виде строки.
     * Для более подробной информации @see https://tools.ietf.org/html/rfc6749#section-5.2
     * 
     * @return string
     */
    public function getErrorString()
    {
        return $this->errorString;
    }
    
    /**
     * Возвращает описание ошибки из параметра error_description.
     * Для более подробной информации @see https://tools.ietf.org/html/rfc6749#section-5.2
     * 
     * @return string
     */
    public function getErrorDescription()
    {
        return $this->errorDescription;
    }
    
}
