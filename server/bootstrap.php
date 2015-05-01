<?php

require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

function handle_error ($code, $description, $file, $line) {
    $response = new JsonResponse(array(
        'success' => false,
        'errors' => array(
            array(
                'text' => $description,
                'file' => $file,
                'line' => $line,
            ),
        ),
    ));
    $response->setStatusCode(500);
    $response->send();
    exit(0);
}
set_error_handler("handle_error");

define('CONSOLE_SCRIPTS', __DIR__.'/../scripts/scripts.json');
define('CONSOLE_PROLOGUE', __DIR__.'/../include/prologue');
define('CONSOLE_EPILOGUE', __DIR__.'/../include/epilogue');
define('CONSOLE_EXEC_CODE', __DIR__.'/../exec/code.php');
define('CONSOLE_EXEC_MEM', __DIR__.'/../exec/mem');
define('CONSOLE_EXEC_TIME', __DIR__.'/../exec/time');

return $request = Request::createFromGlobals();


