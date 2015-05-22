<?php

/** @var \Symfony\Component\HttpFoundation\Request $request */
$request = require_once __DIR__.'/bootstrap.php';

use Symfony\Component\HttpFoundation\JsonResponse;

$result = array('success' => true);

$script = json_decode($request->getContent());
$prologue = file_get_contents(CONSOLE_PROLOGUE);
$epilogue = sprintf(
    file_get_contents(CONSOLE_EPILOGUE),
    CONSOLE_EXEC_MEM,
    CONSOLE_EXEC_TIME
);
$code = $prologue.$script->code.$epilogue;
if(false === file_put_contents(CONSOLE_EXEC_CODE, $code)) {
    $result['success'] = false;
}

$fp = popen('php ' . CONSOLE_EXEC_CODE, 'r');
if (!$fp) {
    $result['success'] = false;
}
$output = stream_get_contents($fp);
pclose($fp);

$mem = (float) file_get_contents(CONSOLE_EXEC_MEM);
$time = (float) file_get_contents(CONSOLE_EXEC_TIME);
$output = preg_replace(
    sprintf('@%s@', preg_quote(realpath(CONSOLE_EXEC_CODE))),
    "\"{$script->name}\"",
    $output
);

$response = new JsonResponse();
if ($result['success']) {
    $result['result'] = array(
        'output' => $output,
        'mem' => $mem,
        'time' => $time,
    );
    $response->setStatusCode(200);
} else {
    $result['errors'] = array('Unable to execute script.');
    $response->setStatusCode(400);
}
$response->setData($result);
$response->send();