<?php

$config = array(
    'script.db_path' => __DIR__.'/../data',
    'script.exec_path' => __DIR__.'/../exec',
    'script.include_path' => __DIR__.'/../src/include',
    
);

$config['db.store_paths'] = array(
    'Model\Script' => $config['script.db_path'].'/script',
    'Model\Settings' => $config['script.db_path'].'/settings',
);

$config['script.used_mem'] = $config['script.exec_path'].'/mem';
$config['script.exec_time'] = $config['script.exec_path'].'/time';

return $config;
