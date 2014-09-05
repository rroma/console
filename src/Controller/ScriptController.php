<?php

namespace Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Silex\Application;
use Form\ScriptType;
use Model\Script;
use Dba\DbaObjectManager;
use Form\ScriptCollectionType;
use Form\Model\ScriptCollection;

class ScriptController
{
    public function index(Application $app)
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
    }
}
