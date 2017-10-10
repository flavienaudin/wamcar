<?php

use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Dotenv\Dotenv;


require __DIR__.'/../vendor/autoload.php';
Debug::enable();

(new Dotenv())->load(__DIR__.'/../.env');

$kernel = new AppKernel('dev', true);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
