<?php

// Providers
$app->register(new Silex\Provider\SerializerServiceProvider());

// Services

$scriptPath = $app['params']['script_path'];
$app['script_manager'] = function () use ($scriptPath) {
    return new Console\Manager\ScriptManager($scriptPath);
};

// Routing
$app->match('/scripts', 'Console\\Controller\\ScriptController::listAll')
    ->method('GET');
$app->match('/scripts', 'Console\\Controller\\ScriptController::edit')
    ->method('POST');
$app->match('scripts/execute', 'Console\\Controller\\ScriptController::execute')
    ->method('POST');