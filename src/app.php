<?php

// Providers
$app->register(new Silex\Provider\SerializerServiceProvider());

// Services

$scriptPath = $app['params']['script_path'];
$app['script_manager'] = function () use ($scriptPath) {
    return new Console\Manager\ScriptManager($scriptPath);
};

// Routing
$app->match('/scripts', 'Console\\Controller\\ScriptController::listAll');
$app->match('/scripts/edit', 'Console\\Controller\\ScriptController::edit');