Resourceful
===========
[![Build Status](https://travis-ci.org/jdesrosiers/resourceful.svg?branch=master)](https://travis-ci.org/jdesrosiers/resourceful)
[![Code Coverage](https://scrutinizer-ci.com/g/jdesrosiers/resourceful/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/jdesrosiers/resourceful/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jdesrosiers/resourceful/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jdesrosiers/resourceful/?branch=master)

Resourceful is a set of tools built on top of [Silex](https://github.com/silexphp/Silex) to
streamline the process of building JSON based Hypermedia APIs by eliminating boilerplate code.  HTTP
methods, headers, etc are well defined in most cases, yet these things are usually left to the
developer to re-implement the details every time they create a new API.  Some of these details are
easy to get wrong if you aren't well versed in the HTTP specification.  Resourceful aims to help you
by handling those details so you can focus on designing your API.

Resourceful was originally built for rapid prototyping, so it does a lot for you.  However, you can
choose which parts you want Resourceful to do and which parts you want to do yourself.  When you put
everything together, you get a very useful tool for rapid prototyping a Hypermedia API that requires
you to write almost no code other than JSON Hyper-Schemas.  This document starts with the basics and
adds new features in each section.

Install
-------
Install Resourceful using composer
```
> composer require jdesrosiers/resourceful
```

The Resourceful Application
---------------------------
The bare minimum that you will want from Resourceful is the Resourceful application.  This class
implements `Silex\Application` and can be used just like you would use a Silex application.  The
Resrourceful application decorates the Silex application with support for content negotiation,
OPTIONS method support, CORS support, and error handling in JSON.  This is the bare minimum stuff
that any JSON based REST API should be doing.

### Quickstart
```php
<?php

use JDesrosiers\Resourceful\Resourceful;
use Silex\Application;

require __DIR__ . "/vendor/autoload.php";

$app = new Resourceful();
$app["debug"] = true;

// Register Controllers
$app->get("/hello/{subject}", function (Application $app, $subject) {
    return $app->json(["greeting" => "Hello $subject!!!"]);
});

// Initialize CORS support
$app->after($app["cors"]);

$app->run();
```

### Content Negotiation
Resourceful defaults to only supporting JSON.  This can be overridden using configuration, but the
advanced features are based on JSON Schema and JSON Hyper-Schema, so supporting anything other than
JSON means you won't be able to use the advanced features.

Any request for a format other than JSON will result in a `406 Not Acceptable` response. Any
requests that pass content that is not JSON will result in a `415 Unsupported Media Type` response.
Content negotiation support is provided by the [silex-conneg-provider](https://github.com/jdesrosiers/silex-conneg-provider)
service provider.

### Support for OPTIONS requests
I don't think anyone cares about OPTIONS request support unless they need it for CORS, but it is
good to have for HTTP compliance anyway. Resourceful gets OPTIONS request support from the
[jdesrosiers/silex-cors-provider](https://github.com/jdesrosiers/silex-cors-provider) service
provider.

### CORS Support
CORS support is provided by the
[jdesrosiers/silex-cors-provider](https://github.com/jdesrosiers/silex-cors-provider) service
provider. To enable CORS support, add the `cors` after middleware to your application.

Resource Controllers
--------------------
The resource controllers aim to eliminate the boilerplate code involved in implementing the CRUD
operations commonly used in REST APIs.  The resource controllers take an implementation of the
`Doctrine\Common\Cache\Cache` interface.  This allows you to implement simple storage functionality
and allow the resource controllers to handle the HTTP details.  Validation is done with JSON Schema.

### Quickstart
```php
<?php

use Doctrine\Common\Cache\FilesystemCache;
use JDesrosiers\Resourceful\Controller\CreateResourceController;
use JDesrosiers\Resourceful\Controller\DeleteResourceController;
use JDesrosiers\Resourceful\Controller\GetResourceController;
use JDesrosiers\Resourceful\Controller\PutResourceController;
use JDesrosiers\Resourceful\Resourceful;

require __DIR__ . "/vendor/autoload.php";

$app = new Resourceful();
$app["debug"] = true;

$data = new FilesystemCache(__DIR__ . "/data");

// Register Controllers
$schema = file_get_contents(__DIR__ . "/schema/foo.json");
$app["json-schema.schema-store"]->add("foo", json_decode($schema));
$app->get("/foo/{id}", new GetResourceController($data))->bind("foo");
$app->put("/foo/{id}", new PutResourceController($data, "foo"));
$app->delete("/foo/{id}", new DeleteResourceController($data));
$app->post("/foo", new CreateResourceController($data, "foo"));

// Initialize CORS support
$app->after($app["cors"]);

$app->run();
```

### Doctrine Cache
I chose to use Doctrine Cache for data storage.  They have a wide range of cache implementations,
you can choose from such as the filesystem, memcache, or redis.  This sort of thing is great for
rapid prototyping, but it can easily be replaced by more permanent data storage.  You just need to
write a class that implements the `Doctrine\Common\Cache\Cache` interface.

### JSON Schema
JSON Schema validation is supported by the
[jdesrosiers/silex-json-schema-provider](https://github.com/jdesrosiers/silex-json-schema-provider)
service provider.

### GetResourceContoller
If a requested resource does not exist, a 404 Not Found response will be given.  An error retrieving
a resource will result in `503 Service Unavailable`.  Success will respond with `200 OK`.

### PutResourceController
A resource can be created or modified using a PUT request. PUT requests do not do partial updates.
The resource passed will be stored exactly how it was passed. The modified resource will be returned
with the response.  The body of the request will be validated using [jdesrosiers/silex-json-schema-provider](https://github.com/jdesrosiers/silex-json-schema-provider).
If it fails validtion it will give a `400 Bad Request` response.  If the resource didn't previously
exist, the response is `201 Created`, otherwise it is `200 OK`.

### DeleteResourceController
When a resource is DELETEd, it will respond with a `204 No Content`. It is not considered an error
to DELETE a resource that does not exist.

### CreateResourceController
When a resource is created using POST, there will be a `Link` header pointing to the newly created
resource.  Resource creation will always respond with `201 Created`. The new resource will be echoed
in the response.  This controller takes the JSON body given and gives it a unique ID generated by
PHP's `uniqid` function.  It then validates the request using [jdesrosiers/silex-json-schema-provider](https://github.com/jdesrosiers/silex-json-schema-provider).
If it fails validtion it will give a `400 Bad Request` response.  In real life, resource creation
often requires more than just generating an ID or you might want to generate IDs in a different way.
Therefore, it is less likely that this controller will be useful than the other three.

Hypermedia Support
------------------
The ResourcefulServiceProvider adds tools for creating Hypermedia APIs with JSON Hyper-Schema.  In
order to support a hyper-schema driven API, you need to associate your responses with hyper-schemas
and serve those schemas for the client to use for discovering links.

### Quickstart
```php
<?php

use Doctrine\Common\Cache\FilesystemCache;
use JDesrosiers\Resourceful\Controller\CreateResourceController;
use JDesrosiers\Resourceful\Controller\DeleteResourceController;
use JDesrosiers\Resourceful\Controller\GetResourceController;
use JDesrosiers\Resourceful\Controller\PutResourceController;
use JDesrosiers\Resourceful\Resourceful;
use JDesrosiers\Resourceful\ResourcefulServiceProvider\ResourcefulServiceProvider;
use JDesrosiers\Resourceful\SchemaControllerProvider\SchemaControllerProvider;

require __DIR__ . "/vendor/autoload.php";

$app = new Resourceful();
$app["debug"] = true;

$app->register(new ResourcefulServiceProvider(), [
    "resourceful.schema-dir" => __DIR__
]);

$data = new FilesystemCache(__DIR__ . "/data");

// Register Supporting Controllers
$app->mount("/schema", new SchemaControllerProvider());

// Register Controllers
$schema = "/schema/foo";
$foo = $app["resources_factory"]($schema);
$foo->get("/{id}", new GetResourceController($data))->bind($schema);
$foo->put("/{id}", new PutResourceController($data, $schema));
$foo->delete("/{id}", new DeleteResourceController($data));
$foo->post("/", new CreateResourceController($data, $schema));
$app->mount("/foo", $foo);

// Initialize CORS support
$app->after($app["cors"]);

$app->run();
```

### Schema Controller
The ResourcefulServiceProvider has a configuration option `resourceful.schema-dir` that allows you
to choose in which directory your schemas will be stored.  This is where you will put the schema you
write.  The SchemaControllerProvider serves schemas properly from that directory.  If you are using
the resource controllers, there is very little coding involved other than writing the hyper-schemas.

### resources_factory
The `resources_factory` service works like Silex's `controllers_factory`, but adds filters to
associate a schema to the controller group.  The main thing this does is to set your `ContentType`
to `application/json; profile="/schema/foo"` which declares that the JSON response is described by
schema identified by the profile.  See the [jdesrosiers/silex-json-schema-provider](https://github.com/jdesrosiers/silex-json-schema-provider)
for more details.

Rapid Prototyping
-----------------
The original purpose of this project was to create a tool for designing Hypermedia APIs using JSON
Hyper-Schema and Jsonary's Generic JSON Browser as generic UI.  The last couple tools described in
this section are for that purpose.  You should be able to fully describe your API and interact with
it while only writing a trivial amount of code.

### Quickstart
```php
<?php

use Doctrine\Common\Cache\FilesystemCache;
use JDesrosiers\Resourceful\CrudControllerProvider\CrudControllerProvider;
use JDesrosiers\Resourceful\FileCache\FileCache;
use JDesrosiers\Resourceful\IndexControllerProvider\IndexControllerProvider;
use JDesrosiers\Resourceful\Resourceful;
use JDesrosiers\Resourceful\ResourcefulServiceProvider\ResourcefulServiceProvider;
use JDesrosiers\Resourceful\SchemaControllerProvider\SchemaControllerProvider;

require __DIR__ . "/vendor/autoload.php";

$app = new Resourceful();
$app["debug"] = true;

$app->register(new ResourcefulServiceProvider(), [
    "resourceful.schema-dir" => __DIR__
]);

$data = new FilesystemCache(__DIR__ . "/data");
$static = new FileCache(__DIR__ . "/static");

// Register Supporting Controllers
$app->mount("/schema", new SchemaControllerProvider());
$app->flush();
$app->mount("/", new IndexControllerProvider($static));

// Register Controllers
$app->mount("/foo", new CrudControllerProvider("foo", $data));

// Initialize CORS support
$app->after($app["cors"]);

$app->run();
```

### Jsonary
[Jsonary](https://github.com/jsonary-js) is JSON Hyper-Schema JavaScript client.  Jsonary includes a
generic JSON Browser implementation that uses Jsonary to provide a generic UI for any
JSON Hyper-Schema enabled API.  Assuming your API is being served at http://localhost:8000, you can
use the JSON Brower deployment at at http://json-browser.s3-website-us-west-1.amazonaws.com/?url=http%3A//localhost%3A8000/
to interact with your API.

### Index Controller
It is largely up to you to make your Hypermedia API discoverable, but the IndexControllerProvider
gets you off to a good start by automatically creating an index schema that points to the root of
you app.  You need to add links to the index schema to direct your users in what they can do with
your application.

### CrudControllerProvider
The CrudControllerProvider registers routes for the CRUD operations and generates a starter
hyper-schema with basic operation to get you started.  To add a new resource you just need to add
one line mounting the CrudControllerProvider and write a corresponding JSON Schema to define the
resource.

Future Work
-----------
The following are some features that would be nice to have, but haven't been implemented.

### Listing Controller
A controller that provides a resource listing of some sort is pretty important for rapid
prototyping.  Just about any real world API will need this kind of functionality.  The main thing
that kept me from doing this so far is the lack of a standardized way of representing a list of
resources.  I tried to follow existing standards for things as much as possible.  I spent some time
figuring out how I wanted to deal with that, but I ended up doing nothing.

### PATCH
It should be fairly straight forward to include support for PATCH as long as it uses a standard
mediatype like JSON Patch ([RFC 6902](https://tools.ietf.org/html/rfc6902)).

### HTTP Cache
I would like at some point to add HTTP cache headers and make use of those headers where
appropriate.
