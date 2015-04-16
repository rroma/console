<?php

namespace Console\Model;

class Script
{
    protected $name;
    protected $code;
    
    function __construct($name = null, $code = null)
    {
        $this->name = $name;
        $this->code = $code;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }
}