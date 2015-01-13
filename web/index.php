<?php

use JDesrosiers\Silex\Generic\GenericControllerProvider;
use JDesrosiers\Silex\MyApplication;

require __DIR__ . "/../vendor/autoload.php";

$app = new MyApplication();

$app->mount("/score", new GenericControllerProvider("score", $app["genericService.file"]("score")));

$app->run();
