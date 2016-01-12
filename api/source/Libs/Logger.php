<?php
namespace Libs;


class Logger 
{
    
    static $instance;
    protected $lines = array();
    
    public function __construct()
    {
        //
    }
    
    public function __destruct()
    {
        $this->save();
    }
    
    
    public static function write($message)
    {
        if ( isset($this) )
        {
            $logger = $this;
        }
        else
        {
            if ( empty(self::$instance) )
            {
                self::$instance = new self;
            }
            $logger = self::$instance;
        }
        $logger->lines[] = trim($message);
    }
    
    protected function save()
    {
        $fp = fopen(__DIR__ . '/../../log/main.log', 'a');  // пока так, потом подумать конечно
        if ( flock($fp, LOCK_EX) ) 
        {
            foreach ( $this->lines as $line )
            {
                fwrite($fp, date('[Y-m-d H:i:s]: ') . $line . "\r\n");
            }
            flock($fp, LOCK_UN);
        }
        $this->lines = array();
    }
    
    public static function exception(\Exception $ex)
    {
        $message  = ' ----- ' . get_class($ex) . ' ------------------------------------ ' . "\r\n";
        $message .= 'FILE: ' . $ex->getFile() . "\r\n";
        $message .= 'LINE: ' . $ex->getLine() . "\r\n";
        $message .= 'CODE: ' . $ex->getCode() . "\r\n";
        if ( method_exists($ex, 'getParams') && ($params = $ex->getParams()) )
        {
            $message .= "PARAMS:\r\n";
            foreach ( $params as $k => $v )
            {
                $message .= "  {$k} = {$v}\r\n";
            }
        }
        $message .= 'TEXT: ' . $ex->getMessage() . "\r\n";
        $message .= "TRACE: \r\n" . $ex->getTraceAsString();
        
        if ( isset($this) )
        {
            $this->write($message);
            $this->save();
        }
        else
        {
            self::write($message);
            self::$instance->save();
        }
    }
    
}
