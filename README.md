Resourceful
===========
[![Build Status](https://travis-ci.org/jdesrosiers/resourceful.svg)](https://travis-ci.org/jdesrosiers/resourceful)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jdesrosiers/resourceful/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jdesrosiers/resourceful/?branch=master)

Resourceful is a simple framework designed for rapid prototyping REST/HTTP applications that are mostly CRUD operations.
It is driven off of JSON Hyper-Schemas.  You use Hyper-Schemas to define your resources and their relationships with
each other.  No coding other than writing Hyper-Schemas and registering new resources is required.  You only need to
worry about your API and not it's implementation.  Good HTTP response codes and headers are managed automatically.

Rapid Prototyping
-----------------
I've tried to make Resourceful as flexible as possible, but it's primary goal is rapid prototyping, so it may not have the
flexibility needed for a production quality application.

How it Works
------------
Install Resourceful using composer
```
> composer require jdesrosiers/resourceful
```

Define your front controller.
```php
<?php

require __DIR__ . "/../vendor/autoload.php";

$app = new JDesrosiers\Silex\Resourceful();
$app["debug"] = true;
$app["rootPath"] = __DIR__ . "/..";

$app["index.title"] = "My API";
$app["index.description"] = "This is my fantastic API";

// Start Registering Controllers

// End Registering Controllers

$app->run();
```

That's it.  You are ready to get started.  Run the application using the builtin PHP server.
```
> php -S localhost:8000 front.php
```

You can use the json browser implementation at
http://json-browser.s3-website-us-west-1.amazonaws.com/?url=http%3A//localhost%3A8000/.  You should see a default index.
A folder called schema is created on this first run and a default index schema is created.  You are expected to add
links to this default index schema as you add resources.

Adding a new resource to your application, requires only one line of code in your front controller.
```php
$app->mount("/foo", new GenericControllerProvider("foo", new FilesystemCache(__DIR__ . "/../data/foo")));
```

This controller adds the "foo" resource using the GenericControllerProvider.  The first argument is the name of the
type.  The second argument is any Doctrine Cache implementation.  Storing files on the filesystem is usually
good enough for a rapid prototype, but you can choose something like memcache or redis if you prefer.  A centralized
data storage can be useful if you are collaborating with others on this application.

Once the resource is registered, a good next step is to add a link to your index to create a "foo".  Refresh your
Jsonary browser and you should see the link you added to the index.  Also, a default "foo" schema was generated in your
`/schema` folder.  Fill out your "foo" schema how you like and then use the index link you created to create a "foo".
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
A resource can be created by making a PUT request on a URI that doesn't contain a resource.  Resource creation will
always respond with `201 Created` and a `Location` header identifying URI of the new resource.  The `Location` header
will always echo the request URI and the resource passed will be saved unmodified as it was passed in the request.  The
new resource will be returned with the response.

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
compliance anyway.  Resourceful gets OPTIONS request support for free by using the silex-cors-provider.

### CORS Support
Support for CORS is built in automatically via the silex-cors-provider service provider.

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
Jsv4 is a JSON Schema validator.  Resourceful uses it validate request JSON based on the Hyper-Schemas you write.

### silex-conneg-provider
No silex REST/HTTP application is complete without the silex-conneg-provider or something like it.  This service
provider adds middleware that inspects a request's content negotiation headers and responds appropriately if there is a
problem.

### silex-cors-provider
I included the silex-cors-provider for CORS support because I prefer to have a Jsonary browser setup as an independent
project.  CORS allows my project to communicate with the independently deployed Jsonary browser.  But, even you choose to
install Jsonary in your application, the silex-cors-provider is still nice to have because it defines OPTIONS routes for
HTTP compliance.

### Doctrine Cache
I chose to use Doctrine Cache for data storage.  They have a wide range of implementations, so you can choose
how you want to store your data.  Some options include the filesystem, memcache, or redis.  If none of these meet your
needs you can always define your own CacheProvider.
