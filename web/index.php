<?php

use JDesrosiers\Silex\Index\IndexControllerProvider;
use JDesrosiers\Silex\MyApplication;
use JDesrosiers\Silex\Schema\SchemaControllerProvider;

require __DIR__ . "/../vendor/autoload.php";

$app = new MyApplication();

$app->mount("/schema", new SchemaControllerProvider());
$app->mount("/", new IndexControllerProvider());

$app->run();
