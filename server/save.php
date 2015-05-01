<?php

/** @var \Symfony\Component\HttpFoundation\Request $request */
$request = require_once __DIR__.'/bootstrap.php';

use Symfony\Component\HttpFoundation\JsonResponse;

$response = new JsonResponse();

if (file_put_contents(CONSOLE_SCRIPTS, $request->getContent()) === false) {
    $data = array(
        'success' => 'false',
        'errors' => array(
            'Unable to save script.'
        ),
    );
    $status = 400;
} else {
    $data = array(
        'success' => 'true',
    );
    $status = 200;
}

$response->setData($data);
$response->setStatusCode($status);
$response->send();