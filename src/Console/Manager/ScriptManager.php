<?php

namespace Console\Manager;

use Console\Model\Script;

class ScriptManager
{
    protected $scriptPath;

    /**
     * @param $scriptPath
     * @throws \Exception
     */
    function __construct($scriptPath)
    {
        if (!is_dir($scriptPath)) {
            throw new \Exception("Directory {$scriptPath} does not exist.");
        }

        $this->scriptPath = $scriptPath;
    }

    /**
     * Find script by name
     *
     * @param $name
     * @return Script|null
     */
    public function findByName($name)
    {
        $scriptCode = @file_get_contents("{$this->scriptPath}/{$name}");

        if ($scriptCode === false) {
            return null;
        }
        
        return new Script($name ,$scriptCode);
    }

    /**
     * Get all scripts
     *
     * @return Script[]
     */
    public function findAll()
    {
        $pathnames = glob("{$this->scriptPath}/*");

        $scripts = [];
        foreach ($pathnames as $pathname) {
            $scriptCode = file_get_contents($pathname);
            $scripts[] = new Script(basename($pathname), $scriptCode);
        }

        return $scripts;
    }

    /**
     * @param Script $script
     * @return bool
     */
    public function save(Script $script)
    {
        return (false !== @file_put_contents(
            $this->scriptPath.'/'.$script->getName(),
            $script->getCode()
        ));
    }

    /**
     * @return mixed
     */
    public function getScriptPath()
    {
        return $this->scriptPath;
    }
}