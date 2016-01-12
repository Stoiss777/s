<?php
namespace Models\Entities;

use \Libs\DB\DB;


class App extends Entity
{
    
    public $id;
    public $name;
    public $secret;
    public $enabled;
    public $trusted;
    public $domains = array();
    public $ip      = array();
    public $scopes  = array();
    
    
    public static function getById($id)
    {
        if ( $row = DB::schema('apps')->getById($id)->first() )
        {
            $app = new self;
            $app->id      = $id;
            $app->name    = $row->name;
            $app->secret  = $row->secret;
            $app->enabled = $row->getAsBoolean('enabled');
            $app->trusted = $row->getAsBoolean('trusted');
            $app->domains = $row->getAsArray('domains');
            $app->ip      = $row->getAsArray('ip');
            $app->scopes  = $row->getAsArray('scopes');
            return $app;
        }
        return false;
    }
    
    
    public function create()
    {
        return false;
    }
    
}
