<?php
namespace Auth\Views;


class View 
{
    
    const HTML_DIR = APPS_DIR;
    
    protected $data = array();
    protected $lang = '';
    
    /*public function data($data)
    {
        $this->data = $data;
    }
     */
    
    public function set($name, $value)
    {
        $this->data[$name] = $value;
    }
    
    public function get($name)
    {
        return isset($this->data[$name])? $this->data[$name]: null;
    }
    
    public function show($page)
    {
        $file = self::HTML_DIR . '/html/Auth/' . $this->lang . '/' . $page . '.php';
        if ( is_readable($file) )
        {
            include $file;
        }
    }
    
    public function __construct($lang)
    {
        $this->lang = $lang;
    }
    
}
