<?php
namespace Libs\DB;

require_once __DIR__ . '/Exception.php';
require_once __DIR__ . '/Configurator.php';
require_once __DIR__ . '/Row.php';
require_once __DIR__ . '/Result.php';



class DB 
{

    //const CONVERT_CC_TO_UNDERCORE = 1;
    //const CONVERT_UNDERCORE_TO_CC = 2;
    
    static $instance;
    static $configurator;
    static $schema;
    static $connection;
    
    
    public static function initialize($func)
    {
        $func(self::$configurator = new Configurator);
    }
    
    
    public static function schema($name='public')
    {
        /*if ( isset($this) )
        {
            return $this->__call($name, func_get_args());
        }*/
        self::$schema = $name;
        if ( self::$instance )
        {
            return self::$instance;
        }
        if ( self::$connection = @pg_connect(self::$configurator->getConnection()) )
        {
            
            return ( self::$instance = new self );
        }
        $err = error_get_last();
        if ( empty($err['message']) )
        {
            $message = 'Unable to connect to PostgreSQL server.';
        }
        else
        {
            if ( preg_match('/FATAL\:\s*(.+?)$/', $err['message'], $o) )
            {
                $message = $o[1];
            }
            else
            {
                $message = $err['message'];
            }
            $message = 'Unable to connect to PostgreSQL server: ' . html_entity_decode($message);
        }
        throw new Exception($message);
    }
    
    
    public function __call($name, array $arguments)
    {
        // converting camel cast to snake case (underscore) and escaping
        $table  = pg_escape_identifier(strtolower(preg_replace('/([a-z0-9])([A-Z])/', '${1}_${2}', $name)));
        $params = array();
        foreach ( $arguments as $v )
        {
            $params[] = is_null($v)? 'null': ("'" . pg_escape_string($v) . "'");
        }
        $sql = 'SELECT * FROM ' . pg_escape_identifier(self::$schema) . '.' . $table . '(' . implode(',', $params) . ')';
        if ( $res = @pg_query(self::$connection, $sql) )
        {
            return new Result($res);
        }
        $err = error_get_last();
        if ( empty($err['message']) )
        {
            $message = "Query failed: {$sql}";
        }
        else
        {
            $message = html_entity_decode(preg_replace('/.+?ERROR:\s*(.+)/', '${1}', $err['message']));
        }
        throw new Exception($message);
    }
    
    
}
