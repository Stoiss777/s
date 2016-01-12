<?php
namespace Libs\KVDB;
/**

- make others magic methods
- when eturns object value it need do something. maybe json???
- expire method

*/
class KVDB implements \Iterator
{
    
    /**
     * End of line symbols of Redis.
     *
     */
    const REDIS_EOL = "\r\n";
    
    
    /**
     * Simple Strings are used to transmit non binary safe strings with minimal overhead. 
     * For example many Redis commands reply with just "OK" on success.
     
     */
    const DT_SIMPLY_STRING = '+';
    
    /**
     * A specific data type for errors.
     *
     */
    const DT_ERROR         = '-';
    
    /**
     * This type is just a CRLF terminated string representing an integer
     *
     */
    const DT_INTEGER       = ':';
    
    /**
     * Bulk Strings are used in order to represent a single binary safe string up to 512 MB in length.
     *
     */
    const DT_BULK_STRING   = '$';
    
    /**
     * Redis commands returning collections of elements to the client.
     *
     */
    const DT_ARRAY         = '*';
    
    
    
    /**
     * Current socket.
     * One socket for all instances.
     * 
     * @var resource
     */
    static $socket;
    
    /**
     * Namespace for current instance.
     * 
     * @var string
     */
    protected $namespace;
    
    /**
     * Key list for iterator.
     * 
     * @var array
     */
    private $_keys;
    
    /**
     * Counter of interator.
     * 
     * @var integer
     */
    private $_counter;
    
    
    
    /**
     * Конфигуратор подключения к Redis. 
     * Должен вызываться как статический метод. В качестве параметра должен быть
     * переда массив или функция возвращающая массив с параметрами подключения.
     * Можно также передать false, тогда функция вернет текущие параметры.
     * 
     * @staticvar array $config - уже установленные параметры подключения
     * @param array|callable|boolean $configuration - массив или функция возвращающая массив
     * @return array - установленные параметры подключения
     * @throws Exception
     */
    public static function initialize($configuration=false)
    {
        static $config;
        if ( $configuration === false )
        {
            if ( empty($config) )
            {
                throw new Exception('Redis class is not configured.');
            }
            return $config;
        }
        else if ( is_array($configuration) || is_callable($configuration) )
        {
            if ( is_callable($configuration) )
            {
                $configuration = $configuration();
            }
            if ( !is_array($configuration) )
            {
                throw new Exception('Initialize function has return wrong value.');
            }
            $config = $configuration;
            $config['host']    = isset($config['host'])? $config['host']: '127.0.0.1';
            $config['port']    = isset($config['port'])? $config['port']: 6379;
            $config['timeout'] = isset($config['timeout'])? $config['timeout']: ini_get("default_socket_timeout");
        }
        else
        {
            throw new Exception('Initialize function is not a function.');
        }
        return $config;
    }
    
    
    /**
     * Конструктор
     * 
     * @param string $namespace - пространство имен с которым работает класс.
     *                            Пустая строка - глобальное пространоство имен.
     */
    public function __construct($namespace='')
    {
        $this->namespace = $namespace;
    }

    
    /**
     * Подключается к redis, если это не было сделано ранее.
     * 
     * @throws Exception
     */
    protected function connect()
    {
        if ( empty(self::$socket) )
        {
            $config = self::initialize();
            self::$socket = fsockopen($config['host'], $config['port'], $errno, $errstr, $config['timeout']);
            if ( empty(self::$socket) )
            {
                $msg = "Can't connect to Redis server on {$params['host']}:{$params['port']}";
                if ( $errno || $errmsg ) 
                {
                    $msg .= "," . ($errno ? " error $errno" : "") . ($errmsg ? " $errmsg" : "");
                }
                throw new Exception($msg);
            }
        }
    }

    
    /**
     * Отключается от redis
     * 
     * @return boolean - факт отключения
     */
    protected function disconnect()
    {
        if ( is_resource(self::$socket) )
        {
            fclose(self::$socket);
            return true;
        }
        return false;
    }


    public function expire($key, $seconds)
    {
        return $this->cmd('EXPIRE ' . $this->getKey($key) . ' ' . (int) $seconds);
    }
    
    
    public function get($key)
    {
        $res = $this->cmd('GET ' . $this->getKey($key));
        if ( $res )
        {
            return unserialize($res);
        }
        return $res;
    }

    
    public function set($key, $value)
    {
        return $this->cmd('SET ' . $this->getKey($key) . ' ' . $this->getValue($value));
    }


    public function delete($key)
    {
        return $this->cmd('DEL ' . $this->getKey($key));
    }

    
    protected function cmd($cmd)
    {
        $this->connect();
        if ( !($res = fwrite(self::$socket, $cmd . self::REDIS_EOL)) )
        {
            throw new Exception("Can't write to socket.");
        }
        return $this->_response(self::$socket);
    }


    private function _response($socket)
    {
        $res  = fgets($socket);
        $type = substr($res, 0, 1);
        $data = substr($res, 1);
        switch ( $type )
        {
            case self::DT_BULK_STRING:
            {
                $len = (int) $data;
                if ( $len > 0 )
                {
                    return trim(fread($socket, $len + strlen(self::REDIS_EOL)));
                }
                return null;
            }
            case self::DT_SIMPLY_STRING:
            {
                return $data;
            }
            case self::DT_ERROR:
            {
                throw new Exception($data);
            }
            case self::DT_ARRAY:
            {
                $cnt = (int) $data;
                $res = array();
                for ( $i=0; $i<$cnt; $i++ )
                {
                    $res[] = $this->_response($socket);
                }
                return $res;
            }
            case self::DT_INTEGER:
            {
                if ( strpos($data, '.') === false ) 
                {
                    return (int) $data;
                } 
                else 
                {
                    return (float) $data;
                }
            }
        }
    }


    protected function escape($string)
    {
        return addcslashes($string, "\0..\37!@\177..\377\"\'\\");
    }


    protected function getKey($key)
    {
        return '"' . $this->escape((($this->namespace === '')? '': $this->namespace . ':') . $key) . '"';
    }


    protected function getValue($value)
    {
        return '"' . $this->escape(serialize($value)) . '"';
    }


    public function keys()
    {
        $keys = $this->cmd('KEYS ' . $this->namespace . ':*');
        $cut  = strlen($this->namespace) + 1;
        foreach ( $keys as $key )
        {
            $res[] = substr($key, $cut);
        }
        return $res;
}

    
    public function rewind()
    {
        $this->_keys = $this->keys();
        $this->_iterator = 0;
    }

    
    public function current()
    {
        return $this->get($this->_keys[$this->_counter]);
    }
    
    
    public function key() 
    {
        return $this->_keys[$this->_counter];
    }
    
    
    public function next() 
    {
        return $this->get($this->_keys[++$this->_counter]);
    }
    
    
    public function valid()
    {
        return isset($this->_keys[++$this->_counter]);
    }


    public function __get($key)
    {
        return $this->get($key);
    }


    public function __set($key, $value)
    {
        return $this->set($key, $value);
    }


    public function __isset($key)
    {
        return !is_null($this->get($key));
    }
    
    
    public function __unset($key)
    {
        $this->delete($key);
    }

    

    
}
