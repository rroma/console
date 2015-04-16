<?php

namespace Console\Controller;

use Console\Controller\Model\ScriptListResponse;
use Console\Manager\ScriptManager;
use Console\Model\Script;
use Silex\Application;
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
                new ScriptListResponse($scripts),
                'json'
            )
        );

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function edit(Request $request, Application $app)
    {
        $scriptJson = $request->get('script');

        if (!$scriptJson) {
            throw new \Exception('Parameter "script" is missing');
        }

        /** @var ScriptManager $manager */
        $manager = $app['script_manager'];

        /** @var Serializer $serializer */
        $serializer = $app['serializer'];

        /** @var Script $script */
        $script = $serializer->deserialize(
            $scriptJson,
            get_class(new Script()),
            'json'
        );

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

    private function editResponse($errors, $code = null)
    {
        if (!$code) {
            $code = $errors ? 400 : 200;
        }

        $response = new Response();
        $response->setStatusCode($code);
        $response->headers->set('Content-Type', 'application/json');
        $content = ['success' => !$errors];
        if ($errors) {
            $content['errors'] = $errors;
        }
        $response->setContent(json_encode($content));

        return $response;
    }

    /*public function index(Application $app)
    {
        $manager = $app['dba.script_manager'];
        $scripts = $manager->fetchAll();
        $openedScripts = array(new Script(null, "<?php\n\n\n\n?>"));

        $formModel = new ScriptCollection($openedScripts);

        $form = $app['form.factory']->create(new ScriptCollectionType(), $formModel);

        return $app['twig']->render('index.html.twig', array(
            'collectionForm' => $form->createView(),
            'scripts' => $scripts,
        ));
    }
    
    public function execute(Request $request, Application $app)
    {
        $script = new Script();
        $form = $app['form.factory']->create(new ScriptType(), $script);
        $form->bind($request);
        $code = $request->get('code');
        $name = $request->get('name');
        
        $incPath = $app['params']['script.include_path'];
        $execPath = $app['params']['script.exec_path'];
        $memPath = $app['params']['script.used_mem'];
        $timePath = $app['params']['script.exec_time'];
        
        
        $prolog = file_get_contents($incPath.'/prolog');
        $epilog = sprintf(file_get_contents($incPath.'/epilog'), $memPath, $timePath);
        
        $file = $execPath.'/code';
        file_put_contents($file, $prolog.$code.$epilog);
        $fp = popen('php ' . $file, 'r');
        $output = stream_get_contents($fp);
        pclose($fp);
        
        $mem = (float)@file_get_contents($memPath);
        $time = (float)@file_get_contents($timePath);

        $output = preg_replace(
            sprintf('@%s@', preg_quote(realpath($file))),
            "«{$name}»",
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
    
    public function save(Request $request, Application $app)
    {
        $formModel = new ScriptCollection();
        $form = $app['form.factory']->create(new ScriptCollectionType(), $formModel);
        $form->bind($request);

        $objectManager = $app['dba.script_manager'];
        $uniqValidator = $app['dba.unique_validator'];
        $result = [];
        foreach ($formModel->scripts as $idx => $script) {
            $result[$idx] = array();
            $saved = false;
            if($uniqValidator->isValid($script)){
                $saved = $objectManager->insertOrReplace($script);
            } else {
                $fields = $uniqValidator->getViolations();
                if(in_array('name', $fields)){
                    $result[$idx]['errors'][] = sprintf(
                        'Script with name "%s" already exists',
                        $script->getName()
                    );
                }
                $saved = false;
            }
            
            $result[$idx]['dbkey'] = $script->getKey();
            $result[$idx]['saved'] = $saved;
        }
        
        return new JsonResponse($result);
    }
    
    public function rename(Request $request, Application $app)
    {
        $formModel = new ScriptCollection();
        $form = $app['form.factory']->create(new ScriptCollectionType(), $formModel);
        $form->bind($request);

        $objectManager = $app['dba.script_manager'];

        $result = [];
        foreach ($formModel->scripts as $idx => $script){
            if (!$script->getKey()) {
                continue;
            }
            $saved = $objectManager->fetch($script->getKey(), new Script());
            $oldName = $saved->getName();
            $saved->setName($script->getName());
            if ($objectManager->insertOrReplace($saved)) {
                $result[$idx] = array('success' => true);
            } else {
                $result[$idx] = array(
                    'success' => false,
                    'oldName' => $oldName
                );
            }
        }

        return new JsonResponse($result);
    }
    
    public function scriptJson($key, Application $app)
    {
        $objectManager = $app['dba.script_manager'];
        $script = $objectManager->fetch($key);
        $result = array(
            'name' => $script->getName(),
            'code' => $script->getCode(),
            'key' => $script->getKey()
        );
        return new JsonResponse($result);
    }
    
    public function savedList(Application $app)
    {
        $manager = $app['dba.script_manager'];
        $scripts = $manager->fetchAll();

        return $app['twig']->render('savedList.html.twig', array(
            'scripts' => $scripts,
        ));
    }*/
}
