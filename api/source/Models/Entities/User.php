<?php
namespace Models\Entities;

use \Libs\DB\DB;
use \Libs\KVDB\KVDB;


class User extends Entity
{
    
    const HASH_SOLT = 'oij34WqdDDo';
    const HASH_TTL  = 15552000;  // 180 days
    
    public $id;
    public $name;
    public $email;
    public $phone;
    
    private $_services = array();
    
    public static function getByKey($key)
    {
        $user = new self;
        $res  = DB::schema('users')->getPropertiesByKey($id);
        while ( $row = $res->fetch() ) 
        {
            $user->{$row->key} = $row->value;
        }
        return empty($user->id)? false: $user;
    }
    
    public static function getById($id)
    {
        $user = new self;
        $res  = DB::schema('users')->getPropertiesById($id);
        while ( $row = $res->fetch() ) 
        {
            $user->{$row->key} = $row->value;
        }
        return empty($user->id)? false: $user;
    }
    
    public static function getByHash($userhash)
    {
        $kvdb = new KVDB('users:sessions');
        if ( $id = $kvdb->get($userhash) )
        {
            return self::getById($id);
        }
        return false;
    }
    
    public static function findByProperties(array $properties)
    {
        $result = array();
        if ( $list = DB::schema('users')->getIdByProperties(json_encode($properties))->column() )
        {
            for ( $i=0; $i<count($list); $i++ )
            {
                $result[] = self::getById($list[$i]);
            }
        }
        return $result? $result: false;
    }
    
    
    public function genHash()
    {
        $kvdb = new KVDB('users:sessions');
        do
        {
            $code = sha1($this->id . ':' . self::HASH_SOLT . ':' . uniqid());
        }
        while ( $kvdb->get($code) );
        $kvdb->set($code, $this->id);
        $kvdb->expire($code, self::HASH_TTL);
        return $code;
    }
    
    
    public function bindService($key, Service $service)
    {
        $this->_services[$key] = $service;
    }
    
    /**
     * @todo здесь нужен какой-то маркер, в общем что-то все-таки надо придумать с авторизацией приложеня
     * 
     */
    /*public function createSession($token, $ttl)
    {
        if ( DB::schema('users')->getSessionByToken($token)->count() )
        {
            return false;
        }
        DB::schema('users')->createSession($this->id, $token, $ttl);
        return true;
    }*/
    
    protected function getProperties()
    {
        $properties = array();
        if ( isset($this->id) )
        {
            $properties['id'] = $this->id;
        }
        if ( isset($this->name) )
        {
            $properties['name'] = $this->name;
        }
        if ( isset($this->email) )
        {
            $properties['email'] = $this->email;
        }
        if ( isset($this->phone) )
        {
            $properties['phone'] = $this->phone;
        }
        return $properties;
    }
    
    public function create()
    {
        $properties = $this->getProperties();
        if ( empty($properties) )
        {
            $this->id = DB::schema('users')->create()->value();
        }
        else
        {
            $this->id = DB::schema('users')->create(json_encode($properties))->value();
        }
        foreach ( $this->_services as $key => $service )
        {
            DB::schema('users')->bindService($this->id, $service->id, $key);
        }
        return true;
    }

}
