<?php
namespace Libs\DB;



class Result 
{
 
    protected $result;
    
    public function __construct($result)
    {
        $this->result = $result;
    }
    
    public function fetch()
    {
        if ( $row = pg_fetch_assoc($this->result) )
        {
            return new Row($row);
        }
        return false;
    }
    

    public function seek($num=0)
    {
        pg_result_seek($this->result, $num);
    }
    
    
    public function first()
    {
        if ( $row = @pg_fetch_assoc($this->result, 0) )
        {
            return new Row($row);
        }
        return false;
    }
    
    public function all()
    {
        $res = array();
        $this->seek(0);
        while ( $row = pg_fetch_assoc($this->result) )
        {
            $res[] = new Row($row);
        }
        return $res;
    }
    
    public function count()
    {
        return pg_num_rows($this->result);
    }
    
    public function column($num=0)
    {
        $res = array();
        $this->seek(0);
        while ( $row = pg_fetch_row($this->result) )
        {
            $res[] = $row[$num];
        }
        return $res;
    }
    
    public function value()
    {
        if ( $row = pg_fetch_row($this->result, 0) )
        {
            return $row[0];
        }
        return false;
    }
    
    public function id()
    {
        //pg_last_oid($this->result);
    }
    
    
}
