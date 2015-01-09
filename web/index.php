<?php

use JDesrosiers\Silex\MyApplication;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

require __DIR__ . "/../vendor/autoload.php";

$app = new MyApplication();

// Serve schema files
$app->get("/schema/{path}", function ($path) use ($app) {
    $fullpath = "{$app["schemaPath"]}/$path.json";
    if (!file_exists($fullpath)) {
        throw new NotFoundHttpException();
    }
    return Response::create(
        file_get_contents($fullpath),
        Response::HTTP_OK,
        array("Content-Type" => "application/schema+json")
    );
})->assert("path", ".+");

$app->run();
