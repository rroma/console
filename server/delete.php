<?php

/** @var \Symfony\Component\HttpFoundation\Request $request */
$request = require_once __DIR__.'/bootstrap.php';

use Symfony\Component\HttpFoundation\JsonResponse;

$response = new JsonResponse();

$ids = $request->query->get('ids');

$deleted = 0;
if ($ids) {
    $scripts = json_decode(file_get_contents(CONSOLE_SCRIPTS));
    foreach ($scripts as $key => $script) {
        if (array_search($script->id, $ids) !== false) {
            unset($scripts[$key]);
            $deleted++;
        }
    }
}

if (
    (is_array($ids) && count($ids) > 0 && $deleted !== count($ids))
    || file_put_contents(CONSOLE_SCRIPTS, json_encode(array_values($scripts))) === false
) {
    $data = array(
        'success' => false,
        'errors' => array(
            'Unable to delete scripts'
        ),
    );
    $status = 400;
} else {
    $data = array(
        'success' => true,
        'deletedItems' => $deleted,
    );
    $status = 200;
}

$response->setData($data);
$response->setStatusCode($status);
$response->send();