<?php

/** @var \Symfony\Component\HttpFoundation\Request $request */
$request = require_once __DIR__.'/bootstrap.php';

use Symfony\Component\HttpFoundation\JsonResponse;

$response = new JsonResponse();

$dataToSave = json_decode($request->getContent());
$scripts = json_decode(file_get_contents(CONSOLE_SCRIPTS));

$update = false;
foreach ($scripts as $script) {
    if ($script->id == $dataToSave->id) {
        $script = $dataToSave;
        $update = true;
        break;
    }
}
if (!$update) {
    $scripts[] = $dataToSave;
}

if (file_put_contents(CONSOLE_SCRIPTS, json_encode($scripts)) === false) {
    $data = array(
        'success' => false,
        'errors' => array(
            'Unable to save script.'
        ),
    );
    $status = 400;
} else {
    $data = array(
        'success' => true,
    );
    $status = $update ? 200 : 201;
}

$response->setData($data);
$response->setStatusCode($status);
$response->send();