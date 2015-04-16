<?php

$config['script_path'] = __DIR__.'/../../data';
$config['include_path'] = __DIR__.'/../include';
$config['prologue'] = __DIR__.'/../include/prologue';
$config['epilogue'] = __DIR__.'/../include/epilogue';
$config['exec_path'] = __DIR__.'/../../bin';
$config['exec_file'] = __DIR__.'/../../bin/exec_code.php';
$config['exec_mem'] = __DIR__.'/../../bin/exec_mem';
$config['exec_time'] = __DIR__.'/../../bin/exec_time';

$app['params'] = $config;
$app['debug'] = true;
