<?php
namespace Libs\DB;



class Row implements \Iterator
{
    
    private $_data;
    static $snakeKeys = array();
    static $camelKeys = array();
    
    public function __construct(array $data)
    {
        $this->_data = $data;
    }

    public function __set($name, $value)
    {
        $this->_data[$this->_toSnake($name)] = $value;
    }
    
    public function __get($name)
    {
        return $this->_data[$this->_toSnake($name)];
    }
    
    
    public function __isset($name)
    {
        return isset($this->_data[$this->_toSnake($name)]);
    }
    
    
    public function __unset($name)
    {
        unset($this->_data[$this->_toSnake($name)]);
    }
    
    
    public function __debugInfo()
    {
        $res = array();
        foreach ( $this->_data as $k => $v )
        {
            $res[$this->_toCamel($k)] = $v;
        }
        return $res;
    }
    
    
    public function getAsBoolean($fieldName)
    {
        $value = $this->__get($fieldName);
        return is_null($value)? null: ($value === 't');
    }
    
    
    public function getAsArray($fieldName)
    {
        $value  = $this->__get($fieldName);
        $result = array();
        if ( is_null($value) )
        {
            return null;
        }
        if ( !preg_match("/^\s*?(?:\{|\[|ARRAY\[)\s*(.*)(?:\}|\])\s*$/i", $value, $o) ) 
        {
            return false;
        }
        $o[1] = rtrim($o[1]);
        if ( $o[1] == '' ) 
        {
            return $result;
        }
        $link = array( &$result );
        $deep = 0;
        $tmp  = '';
        $flg  = false;
        $elements = preg_split("/\s*,\s*/", $o[1]);
        foreach ( $elements as $v ) 
        {
            while ( $v{0} == '{' || $v{0} == '[' ) 
            {
                $link[$deep][] = array();
                $link[$deep+1] = &$link[$deep][count($link[$deep])-1];
                ++$deep;
                $v = ltrim(substr($v, 1));
            }
            $diff = 0;
            while ( $deep && ($v{strlen($v)-1} == '}' || $v{strlen($v)-1} == ']') ) 
            {
                $v = rtrim(substr($v, 0, strlen($v)-1));
                --$deep;
                ++$diff;
            }
            if ( $flg || preg_match("/^([\"\'])(.*)$/", $v, $o) ) 
            {
                if ( preg_match("/([\\\\]*){$o[1]}$/", $v, $out) && (strlen($out[1]) % 2 == 0) ) 
                {
                    if ( $flg )
                    {
                        $link[$deep + $diff][] = $tmp . ',' . stripslashes(substr($v, 0, strlen($v)-1));
                    }
                    else
                    {
                        $link[$deep + $diff][] = stripslashes(substr($o[2], 0, strlen($o[2])-1));
                    }
                    $tmp = '';
                    $flg = false;
                } 
                else 
                {
                    $tmp = $tmp . stripslashes($o[2]);
                    $flg = true;
                }
            } 
            else if ( $v == 'NULL' ) 
            {
                $link[$deep + $diff][] = null;
            } 
            else if ($v !== '') 
            {
                $link[$deep + $diff][] = $v;
            }
        }
        return $result;
    }
    
    
    private function _toCamel($name)
    {
        if ( !isset(self::$snakeKeys[$name]) )
        {
            self::$snakeKeys[$name] = preg_replace_callback('/(\\w)_(\\w)/', function($matches) 
            {
                return $matches[1] . strtoupper($matches[2]);
            }, $name);
            self::$camelKeys[self::$snakeKeys[$name]] = $name;
        }
        return self::$snakeKeys[$name];
    }
   
    private function _toSnake($name)
    {
        if ( !isset(self::$camelKeys[$name]) )
        {
            self::$camelKeys[$name] = strtolower(preg_replace('/([a-z0-9])([A-Z])/', '${1}_${2}', $name));
            self::$snakeKeys[self::$camelKeys[$name]] = $name;
        }
        return self::$camelKeys[$name];
    }
    
    
    
    public function rewind()
    {
        reset($this->_data);
    }
    
    public function current()
    {
        return current($this->_data);
    }
    
    public function key() 
    {
        return $this->_toCamel(key($this->_data));
    }
    
    public function next() 
    {
        return next($this->_data);
    }
    
    public function valid()
    {
        $key = key($this->_data);
        return ($key !== NULL && $key !== FALSE);
    }
    
}
