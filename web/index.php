<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

$app['debug'] = true;
$app['params'] = require __DIR__.'/../config/config.php';

/*$app['script_repository'] = $app->share(function ($app) {
    $savePath = __DIR__.'/../'.$app['params']['script_save_path'];
    
    return new Model\ScriptRepository(
            new \Symfony\Component\Finder\Finder(),
            $savePath
    );
});*/
$app['dba.factory'] = function ($app) {
    return new \Dba\DbaFactory($app['params']['db.store_paths']);
};
$app['dba.unique_validator'] = function ($app) {
    return new \Dba\UniqueValidator($app['dba.factory']);
};
$app['dba.script_manager'] = function ($app) {
    return new \Dba\DbaScriptManager($app['dba.factory']);
};
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'locale_fallbacks' => array('en'),
));
$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
	'twig.path' => __DIR__.'/../src/views',
));

$app->match('/', 'Controller\\ScriptController::index');
$app->post('/script/execute', 'Controller\\ScriptController::execute');
$app->post('/script/save', 'Controller\\ScriptController::save');
$app->post('/script/rename', 'Controller\\ScriptController::rename');
$app->match('/script/{key}/json', 'Controller\\ScriptController::scriptJson');

$app->run();
