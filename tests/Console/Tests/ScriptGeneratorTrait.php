<?php

namespace Console\Tests;

trait ScriptGeneratorTrait
{
    public function addScripts($path, array $scriptData)
    {
        foreach ($scriptData as $name => $data) {
            $pathname = sprintf(
                '%s/%s',
                $path,
                $name
            );
            file_put_contents($pathname, $data);
        }
    }

    public function getScriptPath()
    {
        return sprintf(
            '%s/testScriptManager',
            sys_get_temp_dir()
        );
    }

    public function createScriptDir($path = null)
    {
        $path = $path ? $path : $this->getScriptPath();

        if (!is_dir($path)) {
            mkdir($path);
        }

        return $path;
    }

    protected function removeScriptDir($path)
    {
        foreach (glob("{$path}/*") as $file) {
            unlink($file);
        }

        return rmdir($path);
    }
}