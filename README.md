Resourceful
===========
[![Build Status](https://travis-ci.org/jdesrosiers/resourceful.svg?branch=master)](https://travis-ci.org/jdesrosiers/resourceful)
[![Code Coverage](https://scrutinizer-ci.com/g/jdesrosiers/resourceful/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/jdesrosiers/resourceful/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jdesrosiers/resourceful/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jdesrosiers/resourceful/?branch=master)

Resourceful is a simple framework designed for rapid prototyping REST/HTTP applications that are mostly CRUD operations.
It is driven off of JSON Hyper-Schemas.  You use Hyper-Schemas to define your resources and their relationships with
each other.  No coding other than writing Hyper-Schemas and registering new resources is required.  You only need to
worry about your API and not it's implementation.  Proper HTTP response codes and headers are managed automatically.

How it Works
------------
Install Resourceful using composer
```
> composer require jdesrosiers/resourceful
```

Define your front controller.
```php
<?php

require __DIR__ . "/vendor/autoload.php";

$app = new JDesrosiers\Resourceful\Resourceful();
$app["debug"] = true;

$app->register(new Silex\Provider\TwigServiceProvider());
$app->register(new JDesrosiers\Resourceful\ResourcefulServiceProvider\ResourcefulServiceProvider(), array(
    "resourceful.schemaStore" => new JDesrosiers\Resourceful\FileCache\FileCache(__DIR__ . "/../data"),
));

$app["data"] = new JDesrosiers\Resourceful\FileCache\FileCache(__DIR__ . "/../data");

// Supporting Controllers
$app->mount("/schema", new JDesrosiers\Resourceful\SchemaControllerProvider\SchemaControllerProvider());
$app->flush();
$app->mount("/", new JDesrosiers\Resourceful\IndexControllerProvider\IndexControllerProvider($app["data"]));

// Start Registering Controllers

// End Registering Controllers

// Initialize CORS support
$app->after($app["cors"]);

$app->run();
```

That's it.  You are ready to get started.  Run the application using the built-in PHP server.
```
> php -S localhost:8000 front.php
```

You can use the json browser implementation at
http://json-browser.s3-website-us-west-1.amazonaws.com/?url=http%3A//localhost%3A8000/.  On the first run, a folder
called schema is created and a default index schema and resource is created.  You are expected to add
links to this default index schema as you add resources.  These links wil give your users a place to start.

Adding a new resource to your application, requires only one line of code in your front controller.
```php
$app->mount("/foo", new JDesrosiers\Resourceful\CrudControllerProvider\CrudControllerProvider("foo", $app["data"]));
```

This adds the "foo" resource using the CrudControllerProvider.  The first argument is the name of the type.  The second
argument is any Doctrine Cache implementation.  Storing files on the filesystem is usually good enough for a rapid
prototype, but you can choose something like memcache or redis if you prefer.  A centralized
data storage can be useful if you are collaborating with others on this prototype.

Once the resource is registered, a good next step is to add a link in your index schema to create a "foo".  Refresh your
Jsonary browser and you should see the link you added to the index.  Also, a default "foo" schema was generated in your
`/schema` folder.  Fill out your "foo" schema how you like and then use the index link you added to create a "foo".
All CRUD operations are available for the resource.

Thats all.  Just keep adding resources and links between those resources to make a useful API.

Features
--------------------
### The Index Schema
it is largely up to you to make your REST/HTTP application discoverable, but Resourceful gets you off to a good start by
automatically creating an index schema that points to the root of you app.  The index should be updated to direct your
users in what they can do with your application.

### Schema Generation
The first time the application is run after a new resource is registered, a generic schema is created in the schema
folder.  This is trying to free you up from some of the boiler plate stuff so you can work faster.

### Retrieving a Resource
If a requested resource does not exist, it a `404 Not Found` response will be given.  The Content-Type of the returned
resource will use the JSON Hyper-Schema suggestion of including a `profile` attribute that points to the Hyper-Schema
that defines the resource in the response.

### Creating a Resource
A resource can be created in two ways.  The most common way is to use POST.  When a resource is created using POST, 
there will be a `Link` header pointing to the newly created resource.  A resource can also be created using a PUT
request on a URI that doesn't contain a resource.  Resource creation will always respond with `201 Created`.  The new
resource will be echoed in the response.

### Modifying a Resource
A resource can be modified using a PUT request.  PUT requests do not do partial updates.  The resource passed will be
stored exactly how it was passed.  The modified resource will be returned with the response.

### Deleting a Resource
When a resource is DELETEd, it will respond with a `204 No Content`.  If the resource to be DELETEd does not exist, the
standard success response will be given.  It is not considered an error to DELETE a resource that does not exist.

### Validation
All input JSON is automatically validated for compliance with the JSON Schema that was defined for that resource.
Validation failures result in `400 Bad Request` responses.

### Content Negotiation
Considering that Resourceful is based on JSON Hyper-Schema and Jsonary, the only format supported is JSON.  So, any
requests for a format other than JSON will result in a `406 Not Acceptable` response.  Any requests that pass content
that is not JSON will result in a `415 Unsupported Media Type` response.  This is all handled by the
silex-conneg-provider service provider.

### Support for OPTIONS requests
I don't think anyone cares about OPTIONS request support unless they need it for CORS, but it is good to have for HTTP
compliance anyway.  Resourceful gets OPTIONS request support from the silex-cors-provider.

### CORS Support
CORS support is provided by the silex-cors-provider service provider. To enable CORS support, add the `cors` after
middleware to your application.

Supporting Projects
-------------------
### Silex
Resourceful is a Silex application with some service providers and controllers configured.

### JSON Hyper-Schema
JSON Hyper-Schema is the basis of this project.  JSON Hyper-Schema is the only proposal I have found that can do both
discoverability and hyper linking.  JSON Hyper-Schema makes this project possible.

### Jsonary
Jsonary is a generic Hyper-Schema browser.  It isn't perfect and it certainly isn't pretty, but it gives us the ability
to view and manipulate any Hyper-Schema driven resource without the need to write any front-end code.

### Jsv4
Jsv4 is a JSON Schema validator.  Resourceful uses it to validate request JSON based on the Hyper-Schemas you write.

### silex-conneg-provider
No silex REST/HTTP application is complete without the silex-conneg-provider or something like it.  This service
provider adds middleware that inspects a request's content negotiation headers and responds appropriately if there is a
problem.

### silex-cors-provider
The silex-cors-provider is primarily used for generating OPTIONS routes.  However, CORS support comes in handy as well.

### Doctrine Cache
I chose to use Doctrine Cache for data storage.  They have a wide range of implementations, so you can choose
how you want to store your data.  Some options include the filesystem, memcache, or redis.  If none of these meet your
needs you can always write your own `Doctrine\Common\Cache\Cache` implementation.
