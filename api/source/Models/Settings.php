<?php
namespace Models;

use \Libs\DB\DB;
use \Libs\DB\Row as DBRow;
use \Libs\KVDB\KVDB;
use \stdClass;


class Settings 
{

    /**
     * Текущий экзмепляр класса используемым фабричным 
     * методом access.
     * 
     * @var self
     */
    public static $instance;
    
    /**
     * Уровень досутупа в текущей операции, см. self::access()
     * 
     * @var string 
     */
    public static $access;
    
    /**
     * Масиив настроек
     * 
     * @var array
     */
    private $_data = array();
    
    
    /**
     * Метод реализует singleton поведение класса.
     * Его нужно вызывать каждый раз перед выполнением метода get.
     * 
     * @param string $level - тип доступа с которым надо работать: private, authorize или public.
     *                        Более подробно см. self::_checkAccess()
     * @return self - экзмепляр себя
     */
    public static function access($level)
    {
        self::$access = $level;
        if ( empty(self::$instance) )
        {
            self::$instance = new self;
        }
        return self::$instance;
    }
    
    
    /**
     * Возвращает значение элемента по его ключу или массив значений по маске
     * 
     * @param string $key - ключ элемента или его маска. например:
     *                      services.auth.clientId <- ключ
     *                      services.auth.* <- маска
     * @return mixed - если $key это ключ, то вернет значение ключа
     *                 если $key это маска, то вернет ассоциативный массив подходящих значений,
     *                      где ключ массива - полное имя ключа элемента, напр. services.auth.clientId
     *                 если запрашиваемый элемент не найден или к нему нет доступа, то вернет false
     */
    public function get($key)
    {
        $el = $this->_getByKey($key);
        if ( $el instanceof stdClass )
        {
            return $this->_prepareElement($el);
        }
        else if ( is_array($el) )
        {
            $res = array();
            $elements = $this->_getElements($el);
            foreach ( $elements as $v )
            {
                if ( ($vp = $this->_prepareElement($v)) !== false )
                {
                    $res[$v->key] = $vp;
                }
            }
            return $res;
        }
        return false;
    }
    
    
    /**
     * Конструктор. 
     * При необходимости загружает данные из Redis или БД.
     * 
     */
    public function __construct()
    {
        $kvdb = new KVDB;
        $this->_data = $kvdb->settings;
        if ( empty($this->_data) )
        {
            $this->_fromBase('*');
            $this->_toCache();
        }
    }
    
    
    /**
     * Сохраняет элемент из базы данных в self::$_data с ключом $key
     * 
     * @param string $key - ключ элемента
     * @param \Libs\DB\Row $row - строка с данными из БД
     * @return \stdClass|boolean - сохраненный элемент или false при неудаче
     */
    protected function set($key, DBRow $row)
    {
        if ( $el = $this->_setter(explode('.', $key), $row, $this->_data) )
        {
            $el->key = $key;
            return $el;
        }
        return false;
    }
    
    
    /**
     * Вспомогательный метод для self::set(). 
     * Метод работает рекурсивно.
     * 
     * @param array $keys - массив строкового ключа рабитого символом '.' - explode('.', $key)
     * @param \Libs\DB\Row $row - строка с данными из БД
     * @param type $data - текущий уровень вложенности self::$_data
     * @return \stdClass|boolean - полученный элемент или false, если была ошибка
     */
    private function &_setter(array $keys, DBRow $row, &$data)
    {
        $key = array_shift($keys);
        if ( count($keys) == 0 )
        {
            $data[$key] = new stdClass;
            switch ( $row->type )
            {
                case 'scalar':
                {
                    $data[$key]->value = $row->value;
                    break;
                }
                case 'json':
                {
                    $data[$key]->value = ($row->value === '')? '': json_decode($row->value);
                    break;
                }
                case 'callable':
                case 'link':
                {
                    $data[$key]->value = $row->value;
                    break;
                }
            }
            if ( isset($data[$key]->value) )
            {
                $data[$key]->type   = $row->type;
                $data[$key]->access = $row->access;
                $data[$key]->expire = is_null($row->ttl)? null: (time() + $row->ttl);
                return $data[$key];
            }
            return false;
        }
        else
        {
            if ( !is_array($data[$key]) )
            {
                $data[$key] = array();
            }
            return $this->_setter($keys, $row, $data[$key]);
        }
    }
    
    
    /**
     * Сверяет уровень доступа элемента с уровнем доступа текущего запроса
     * 
     * public - публичные данные. Если класс запрошен с уровнем доступа public, то
     *          у него будет доступ только к элементом с таким же доступом
     * authorized - данные только для авторизованных пользователей. Класс с таким
     *              уровнем доступ имеет доступ к элементам с этим же доступом + public
     * private - приватный доступ. Если класс запрошен с этим уровнем доступа, то он
     *           имеет досут ко всем элементам
     * 
     * @param \stdClass $el - элемент уровень доступа которого нужно проверить
     * @return boolean - true - у класса есть к нему доступ, false - нет доступа
     */
    private function _checkAccess(stdClass $el)
    {
        if ( self::$access == 'private' )
        {
            return true;
        }
        else if ( self::$access == 'authorized' && ($el->access == 'authorized' || $el->access == 'public') )
        {
            return true;
        }
        else if ( self::$access == 'public' && $el->access == 'public' )
        {
            return true;
        }
        return false;
    }
    
    
    /**
     * Метод подготавливает элемент к окончательному выводу его значения:
     * проверяет уровень доступа, вызывает необходимые функции и т.д.
     * 
     * @param \stdClass $el - элемент которые готовим к выводу
     * @return mixed - значение элемента или false, если элемент показать нельзя или невозможно
     */
    private function _prepareElement(stdClass &$el)
    {
        if ( !$this->_checkAccess($el) )
        {
            return false;
        }
        if ( $el->expire && ($el->expire <= time()) )
        {
            if ( $el = $this->set($el->key, DB::schema('setting')->get($el->key)->first()) )
            {
                $this->_toCache();
            }
            else
            {
                return false;
            }
        }
        if ( $el instanceof stdClass )
        {
            if ( $el->type == 'link' )
            {
                if ( $data = $this->_getByKey($el->value) )
                {
                    $el->type  = $data->type;
                    $el->value = $data->value;
                    $this->_toCache();
                }
                else
                {
                    unset($el);
                    $this->_toCache();
                }
            }
            if ( $el->type == 'callable' )
            {
                $params = explode('|', $el->value);
                $method = array_shift($params);
                if ( method_exists($this, $method) )
                {
                    $el->type = 'scalar';
                    if ( empty($params) )
                    {
                        $el->value = call_user_func(array($this, $method));
                    }
                    else
                    {
                        $el->value = call_user_func_array(array($this, $method), $params);
                    }
                    $this->_toCache();
                }
            }
            return $el->value;
        }
    }
    
    
    /**
     * Возвращает элемент по строковому значению ключа 
     * или массив ветки self::_data, если ключ был маской
     * 
     * @param string $key - ключ
     * @return mixed - нужный элемент, их массив или false, если он не найден
     */
    private function &_getByKey($key)
    {
        $keys = explode('.', $key);
        $link = &$this->_data;
        $c = count($keys);
        for ( $i=0; $i<$c; $i++ )
        {
            if ( $i == $c - 1 )
            {
                if ( ($keys[$i] != '*') && ($link[$keys[$i]] instanceof stdClass) )
                {
                    return $link[$keys[$i]];
                }
                else if ( ($keys[$i] == '*') && is_array($link) )
                {
                    return $link;
                }
                return false;
            }
            else if ( is_array($link) )
            {
                $link = &$link[$keys[$i]];
            }
            else
            {
                return false;
            }
        }
    }
    
    
    /**
     * Проходит по ветке элементов (из self::_getByKey()) и находит там все элементы.
     * Метод рекурсивен.
     * 
     * @param array|\stdClass $brunch - массив с веткой элементов или сам элемент.
     * @return array - массив со всем элементами из ветки
     */
    private function _getElements($brunch)
    {
        $res = array();
        foreach ( $brunch as $el )
        {
            if ( $el instanceof stdClass )
            {
               $res[] = $el;
            }
            else if ( is_array($el) )
            {
                $res = array_merge($res, $this->_getElements($el));
            }
        }
        return $res;
    }

    /**
     * Загружает элементы из БД в self::$_data
     * 
     * @param string $mask - ключ или маска для выбора элементов
     */
    public function _fromBase($key)
    {
        $res = DB::schema('settings')->get($key);
        while ( $row = $res->fetch() )
        {
            $this->set($row->key, $row);
        }
    }
    
    
    /**
     * Сохраняет self::$_data в Redis
     * 
     */
    private function _toCache()
    {
        $kvdb = new KVDB;
        $kvdb->settings = $this->_data;
    }
    

    // _____________________________________________________________________________
    // _____ СПЕЦИАЛЬНЫЕ МЕТОДЫ ДЛЯ АВТОМАТИЧЕСКОЙ НАСТРОЙКИ КЛАССОВ _______________

    /*public static function configurator($object)
    {
        if ( $object instanceof OAuth2 )
        {
            $key = 'services.' . OAuth2::$classes[$object->getService()]['key'] . '.auth';
            $settings = self::access('private')->get("{$key}.*");
            $object->setRedirectUri($settings["{$key}.redirectUri"]);
            $object->setClientId($settings["{$key}.clientId"]);
            $object->setClientSecret($settings["{$key}.clientSecret"]);
            $object->setScope($settings["{$key}.scope"]);
            $object->setState($settings["{$key}.state"]);
            $object->setCodeUrl($settings["{$key}.codeUrl"]);
            $object->setTokenUrl($settings["{$key}.tokenUrl"]);
            return true;
        }
    }*/
    
    
    // _____________________________________________________________________________
    // _____ ДОПОЛНИТЕЛЬНЫЕ МЕТОДЫ ДЛЯ CALLABLE НАСТРОЕК ___________________________
    

    /**
     * Возвращает codeUri для OAuth2 сервисов
     * 
     * @param type $key
     * @return type
     */
    /*private function _callOAuth2CodeUri($key)
    {
        $settings = $this->get("services.{$key}.auth.*");
        $param = array();
        foreach ( $settings as $k => $v )
        {
            if ( ($pos = strrpos($k, '.')) === false )
            {
                $pos = 0;
            }
            else
            {
                $pos++;
            }
            $param[substr($k, $pos)] = $v;
        }
        $auth = OAuth2::getInstance(OAuth2::getServiceByKey($key));
        $this->configurator($auth);
        return $auth->getCodeUri();
    }*/
    
    
}
