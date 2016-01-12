<?php
namespace Models\Entities;


abstract class Entity 
{
    
    public abstract function create();
    /*public abstract function update();
    public abstract function delete();*/
    
    public function save()
    {
        if ( isset($this->id) )
        {
            $this->update();
        }
        else
        {
            $this->create();
        }
    }
    
}
