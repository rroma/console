<?php

namespace Dba;

abstract class DbaEntity
{
    protected $key;

    public function getKey()
    {
        return $this->key;
    }

    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }
}