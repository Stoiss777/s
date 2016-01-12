<?php
namespace Libs\DB;


class Configurator
{
    
    const NS_CAMEL_CASE = 1;
    const NS_SNAKE_CASE = 2;
    
    private $_connection;
    private $_clientEncoding;
    private $_fetchStyle;
    
    public function setConnection($connectionString)
    {
        $this->_connection = $connectionString;
    }
    
    public function getConnection()
    {
        return $this->_connection;
    }

    public function setClientEncoding($encoding)
    {
        //
    }
    
    public function getClientEncoding()
    {
        //
    }
    
    public function setFetchStyle($fetchStyle)
    {
        //
    }
    
    public function getFetchStyle()
    {
        //
    }
    
    public function setNameStyles($db, $app)
    {
        //
    }
    
    public function getDBNameStyle()
    {
        //
    }
    
    public function getAppNameStyle()
    {
        
    }
    
}
