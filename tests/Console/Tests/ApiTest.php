<?php

namespace Console\Tests;

use Silex\Application;
use Silex\WebTestCase;

class ApiTest extends WebTestCase
{
    use ScriptGeneratorTrait;

    /**
     * @inheritdoc
     */
    public function createApplication()
    {
        $app = new Application();

        require __DIR__ . '/../../../resources/config/config_test.php';
        require __DIR__ . '/../../../src/app.php';

        $this->app = $app;

        return $app;
    }

    /**
     * @group script
     * @group script-list-get
     */
    public function testScriptListGet()
    {
        $path = $this->app['script_manager']->getScriptPath();

        $scriptData = [
            'script1' => 'content1',
            'script2' => 'content2',
            'script3' => 'content3',
        ];
        $this->addScripts($path, $scriptData);

        $client = $this->createClient();
        $client->request('GET', '/scripts');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent());
        $this->assertTrue(isset($response->scripts));
        $this->assertEquals(count($scriptData), count($response->scripts));

        $this->removeScriptDir($path);
    }

    public function testSave()
    {

    }
}