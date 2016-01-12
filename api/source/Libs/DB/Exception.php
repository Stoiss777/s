<?php
namespace Libs\DB;

use \Libs\Logger;


class Exception extends \Exception
{
    
    public function __construct($message, $traceLevel=1) 
    {
        $trace = debug_backtrace();
        $this->file = $trace[1]['file'];
        $this->line = $trace[1]['line'];
        parent::__construct($message);
        Logger::exception($this);
    }
    
}
