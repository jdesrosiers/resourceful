<?php

use JDesrosiers\Silex\MyApplication;
use JDesrosiers\Silex\Foo\FooControllerProvider;
use JDesrosiers\Silex\Foo\FooServiceProvider;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

require __DIR__ . "/../vendor/autoload.php";

$app = new MyApplication();

// Serve schema files
$app->get("/schema/{path}", function ($path) {
    $fullpath = __DIR__ . "/../schema/$path.json";
    if (!file_exists($fullpath)) {
        throw new NotFoundHttpException();
    }
    $schema = file_get_contents($fullpath);
    return Response::create($schema, Response::HTTP_OK, array("Content-Type" => "application/schema+json"));
})->assert("path", ".+");

$app->register(new FooServiceProvider());
$app->mount("/foo", new FooControllerProvider());

$app->run();
