<?php

namespace Console\Controller;

use Console\Manager\ScriptManager;
use Console\Model\Script;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;

class ScriptController
{
    public function listAll(Request $request, Application $app)
    {
        /** @var ScriptManager $manager */
        $manager = $app['script_manager'];

        /** @var Serializer $serializer */
        $serializer = $app['serializer'];

        $scripts = $manager->findAll();

        $response = new Response(
            $serializer->serialize(
                ['scripts' => $scripts],
                'json'
            )
        );

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function edit(Request $request, Application $app)
    {
        /** @var ScriptManager $manager */
        $manager = $app['script_manager'];

        $script = $this->scriptFromRequest($request, $app);

        $errors = [];
        $successCode = 200;
        if (!$manager->findByName($script->getName())) {
            $successCode = 201;
        }

        if (!$manager->save($script)) {
            $errors[] = "Unable to save script {$script->getName()}";
        }

        $statusCode = $errors ? 400 : $successCode;

        return $this->editResponse($errors, $statusCode);
    }

    /**
     * @param Request $request
     * @param Application $app
     * @return Script
     * @throws \Exception
     */
    protected function scriptFromRequest(Request $request, Application $app)
    {
        $scriptJson = $request->get('script');

        if (!$scriptJson) {
            throw new \Exception('Parameter "script" is missing');
        }

        /** @var Serializer $serializer */
        $serializer = $app['serializer'];

        /** @var Script $script */
        $script = $serializer->deserialize(
            $scriptJson,
            get_class(new Script()),
            'json'
        );

        return $script;
    }

    protected function editResponse($errors, $code = null)
    {
        if (!$code) {
            $code = $errors ? 400 : 200;
        }

        $response = new JsonResponse();
        $response->setStatusCode($code);
        $content = ['success' => !$errors];
        if ($errors) {
            $content['errors'] = $errors;
        }
        $response->setContent(json_encode($content));

        return $response;
    }
    
    public function execute(Request $request, Application $app)
    {
        $script = $this->scriptFromRequest($request, $app);

        $params = $app['params'];

        $memPath = $params['exec_mem'];
        $timePath = $params['exec_time'];
        $filePath = $params['exec_file'];

        $prologue = file_get_contents($params['prologue']);
        $epilogue = sprintf(
            file_get_contents($params['epilogue']),
            $memPath,
            $timePath
        );

        $code = $prologue.$script->getCode().$epilogue;
        file_put_contents($filePath, $code);
        $fp = popen('php ' . $filePath, 'r');
        $output = stream_get_contents($fp);
        pclose($fp);
        
        $mem = (float) file_get_contents($memPath);
        $time = (float) file_get_contents($timePath);

        $output = preg_replace(
            sprintf('@%s@', preg_quote(realpath($filePath))),
            "«{$script->getName()}»",
            $output
        );
        
        $result = array(
            'output'    => $output,
            'execParams' => array(
                'mem' => $this->formatMemory($mem),
                'time' => $this->formatTime($time),
            ),
        );
        
        return new JsonResponse($result);
    }
    
    public function formatMemory($mem, $dicimals = 2)
    {
        $units = array('B', 'Kb', 'Mb', 'Gb', 'Tb'); 
        $bytes = max($mem, 0); 
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
        $pow = min($pow, count($units) - 1); 
        $bytes /= pow(1024, $pow);

        return number_format($bytes, $dicimals) . ' ' . $units[$pow];
    }
    
    public function formatTime($time, $dicimals = 6)
    {
        return number_format($time, $dicimals) . ' sec';
    }
}
