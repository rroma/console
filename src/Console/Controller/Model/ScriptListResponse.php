<?php

namespace Console\Controller\Model;

use Console\Model\Script;

class ScriptListResponse
{
    /** @var Script[]  */
    protected $scripts = [];

    function __construct($scripts)
    {
        $this->scripts = $scripts;
    }

    /**
     * @return Console\Model\Script[]
     */
    public function getScripts()
    {
        return $this->scripts;
    }

    /**
     * @param Console\Model\Script[] $scripts
     */
    public function setScripts($scripts)
    {
        $this->scripts = $scripts;
    }
}