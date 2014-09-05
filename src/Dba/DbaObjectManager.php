<?php

namespace Dba;

use Dba\DbaEntity;
use Dba\DbaFactory;

abstract class DbaObjectManager
{
    protected $factory;
    protected $errors;
    
    public function __construct(DbaFactory $factory)
    {
        $this->factory = $factory;
    }
    
    abstract function getClass();
    
    public function insertOrReplace(DbaEntity $entity)
    {        
        $db = $this->factory->create($this->getClass());
        
        $key = $entity->getKey();
        if(!$key){
            $entity->setKey(uniqid());
            if ($db->replace($entity->getKey(), serialize($entity))) {
                return true;
            } else {
                $entity->setKey(null);
                return false;
            }
        }
        
        if ($db->hasKey($key)) {
            return $db->replace($key, serialize($entity));
        } else {
            return $db->insert($key, serialize($entity));
        }
    }
    
    public function fetchAll()
    {
        $db = $this->factory->create($this->getClass());
        
        $entities = [];
        foreach ($db as $serialized) {
            $entities[] = unserialize($serialized);
        }
        
        return $entities;
    }
    
    public function fetch($key)
    {
        $db = $this->factory->create($this->getClass());
        
        $str = $db->fetch($key);
        if (!$str) {
            return null;
        }
        return unserialize($str);
    }
    
    public function getErrors()
    {
        return $this->errors;
    }
}