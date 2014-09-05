<?php

namespace Dba;

use Dba\Dba;

class DbaFactory
{
    protected $storeMap;
    
    function __construct($storeMap)
    {
        $this->storeMap = $storeMap;
    }
    
    public function create($class, $mode = 'c')
    {
        if(gettype($class) == 'object'){
            $dbPath = $this->storeMap[get_class($class)];
        } else {
            $dbPath = $this->storeMap[$class];
        }
        
        return new Dba($dbPath, $mode);
    }
    
    public function getStoreMap()
    {
        return $this->storeMap;
    }

    public function setStoreMap($storeMap)
    {
        $this->storeMap = $storeMap;
        return $this;
    }


}
