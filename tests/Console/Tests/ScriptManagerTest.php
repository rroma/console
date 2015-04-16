<?php

namespace Console\Tests;

use Console\Manager\ScriptManager;
use Console\Model\Script;

class ScriptManagerTest extends \PHPUnit_Framework_TestCase
{
    use ScriptGeneratorTrait;

    /**
     * @group sm
     * @group sm-find-by-name
     */
    public function testFindByName()
    {
        $path = $this->createScriptDir();

        $scriptData = [
            'script1' => 'content1',
            'script2' => 'content2',
            'script3' => 'content3',
        ];

        $this->addScripts($path, $scriptData);

        $manager = new ScriptManager($path);

        $script = $manager->findByName('script1');
        $this->assertEquals('script1', $script->getName());
        $this->assertEquals('content1', $script->getCode());

        $script = $manager->findByName('nonexistent');
        $this->assertNull($script);

        $this->removeScriptDir($path);
    }

    /**
     * @group sm
     * @group sm-find-all
     */
    public function testFindAll()
    {
        $path = $this->createScriptDir();

        $scriptData = [
            'script1' => 'content1',
            'script2' => 'content2',
            'script3' => 'content3',
        ];

        $manager = new ScriptManager($path);

        $scripts = $manager->findAll();
        $this->assertTrue(gettype($scripts) == 'array');
        $this->assertEquals(0, count($scripts));

        $this->addScripts($path, $scriptData);
        $scripts = $manager->findAll();
        $this->assertEquals(count($scriptData), count($scripts));

        $idx = 0;
        foreach ($scriptData as $name => $code) {
            $this->assertEquals($name ,$scripts[$idx]->getName());
            $this->assertEquals($code ,$scripts[$idx]->getCode());
            $idx++;
        }

        $this->removeScriptDir($path);
    }

    /**
     * @group sm-save
     */
    public function testSave()
    {
        $path = $this->createScriptDir();

        $manager = new ScriptManager($path);

        $script1 = new Script('test_script1', 'test content');
        $this->assertTrue($manager->save($script1));
        $script2 = new Script('test_script2');
        $this->assertTrue($manager->save($script2));

        $this->removeScriptDir($path);
    }

}