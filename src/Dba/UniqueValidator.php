<?php

namespace Dba;

class UniqueValidator
{
    protected $factory;
    
    protected $violations;
    
    function __construct(DbaFactory $factory)
    {
        $this->factory = $factory;
        $this->violations = array();
    }
    
    public function isValid(DbaEntity $entity)
    {        
        $result = true;
        
        $fields =  $entity->uniqueFields();
        if($fields) {
            $db = $this->factory->create($entity);

            $reflection = new \ReflectionClass($entity);

            $values = [];
            $props = [];
            foreach($fields as $field){
                $prop = $reflection->getProperty($field);
                if ($prop->isPrivate() || $prop->isProtected()) {
                    $prop->setAccessible(true);
                }
                $props[$field] = $prop;
                $values[$field] = $prop->getValue($entity);
            }

            $savedObj = [];
            foreach ($db as $saved) {
                $obj = unserialize($saved);
                if ($obj->getKey() === $entity->getKey()) {
                    continue;
                }
                $savedObj[] = $obj;
            }

            foreach($fields as $field){
                $value = $values[$field];
                $savedValues = array_map(
                    function($obj) use($props, $field) { 
                        return $props[$field]->getValue($obj);
                    },
                    $savedObj
                );
                if (in_array($value, $savedValues)) {
                    $result = false;
                    $this->violations[] = $field;
                }
            }
        }
        
        return $result;
    }
    
    public function getViolations()
    {
        return $this->violations;
    }

    public function getFactory()
    {
        return $this->factory;
    }

    public function setFactory($factory)
    {
        $this->factory = $factory;

        return $this;
    }
}