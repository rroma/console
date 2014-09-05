<?php

namespace Dba;

use Dba\DbaObjectManager;

class DbaScriptManager extends DbaObjectManager
{    
    public function getClass()
    {
        return 'Model\Script';
    }    
}