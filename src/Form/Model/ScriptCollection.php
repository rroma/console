<?php

namespace Form\Model;

class ScriptCollection
{
    public $scripts = array();
    
    function __construct(array $scripts = array())
    {
        $this->scripts = $scripts;
    }
}
