# php-openapi

Read and write [OpenAPI](https://www.openapis.org/) 3.0.x YAML and JSON files and make the content accessible in PHP objects.

It also provides a CLI tool for validating and converting OpenAPI 3.0.x Description files.

[![Latest Stable Version](https://poser.pugx.org/cebe/php-openapi/v/stable)](https://packagist.org/packages/cebe/php-openapi)
[![Total Downloads](https://poser.pugx.org/cebe/php-openapi/downloads)](https://packagist.org/packages/cebe/php-openapi)
[![Build Status](https://github.com/cebe/php-openapi/workflows/CI/badge.svg)](https://github.com/cebe/php-openapi/actions)


## Install

    composer require cebe/php-openapi

## Requirements

- PHP 7.1 or higher (works fine with PHP 8)

## Used by

This library provides a low level API for reading and writing OpenAPI files. It is used by higher level tools to
do awesome work:

- [cebe/yii2-openapi](https://github.com/cebe/yii2-openapi) Code Generator for REST API from OpenAPI 3 Descriptions, includes fake data generator.
- [cebe/yii2-app-api](https://github.com/cebe/yii2-app-api) Yii framework application template for developing API-first applications.
- [league/openapi-psr7-validator](https://github.com/thephpleague/openapi-psr7-validator) validates PSR-7 messages (HTTP request/response) against OpenAPI descriptions.
- [dsuurlant/response2schema](https://github.com/dsuurlant/response2schema) a quick and easy tool for generating OpenAPI schemas based on example data.
- [hotmeteor/spectator](https://github.com/hotmeteor/spectator) a light-weight OpenAPI testing tool for existing Laravel test suite.
- [googoogajoob/openapi-slim4](https://github.com/googoogajoob/openapi-slim4) Configure the paths of a slim4 application from an openapi definition.
- ... ([add yours](https://github.com/cebe/php-openapi/edit/master/README.md#L24))

## Usage

### CLI Tool

    $ vendor/bin/php-openapi help
    PHP OpenAPI 3 tool
    ------------------
    by Carsten Brandt <mail@cebe.cc>

    Usage:
      php-openapi <command> [<options>] [input.yml|input.json] [output.yml|output.json]

      The following commands are available:

        validate   Validate the API Description in the specified input file against the OpenAPI v3.0 schema.
                   Note: the validation is performed in two steps. The results are composed of
                    (1) structural errors found while reading the API Description file, and
                    (2) violations of the OpenAPI v3.0 schema.

                   If no input file is specified input will be read from STDIN.
                   The tool will try to auto-detect the content type of the input, but may fail
                   to do so. You may specify --read-yaml or --read-json to force the file type.

                   Exits with code 2 on validation errors, 1 on other errors and 0 on success.

        convert    Convert a JSON or YAML input file to JSON or YAML output file.

                   If no input file is specified input will be read from STDIN.
                   If no output file is specified output will be written to STDOUT.
                   The tool will try to auto-detect the content type of the input and output file, but may fail
                   to do so. You may specify --read-yaml or --read-json to force the input file type.
                   and --write-yaml or --write-json to force the output file type.

                   By default all references are resolved (replaced with the object referred to). You can control
                   handling of references with the following arguments:

                   --resolve-none      Do not resolve references.
                   --resolve-external  Only resolve references that point to external files.
                                       This process is often referred to as "inlining".
                   --resolve-all       Resolve all references (default).
                                       Recursive pointers will stay references.

        inline     Convert a JSON or YAML input file to JSON or YAML output file and
                   resolve all external references. The output will be a single API Description file.
                   This is a shortcut for calling convert --resolve-external.

        help       Shows this usage information.

      Options:

        --read-json   force reading input as JSON. Auto-detect if not specified.
        --read-yaml   force reading input as YAML. Auto-detect if not specified.
        --write-json  force writing output as JSON. Auto-detect if not specified.
        --write-yaml  force writing output as YAML. Auto-detect if not specified.
        -s, --silent  silent mode. Will hide all success/information messages and only print errors.


### Reading API Description Files

Read OpenAPI Description from JSON file:

```php
use cebe\openapi\Reader;

// realpath is needed for resolving references with relative Paths or URLs
$openapi = Reader::readFromJsonFile(realpath('openapi.json'));
```

Read OpenAPI Description from YAML:

```php
use cebe\openapi\Reader;

// realpath is needed for resolving references with relative Paths or URLs
$openapi = Reader::readFromYamlFile(realpath('openapi.yaml'));
// you may also specify the URL to your API Description file
$openapi = Reader::readFromYamlFile('https://raw.githubusercontent.com/OAI/OpenAPI-Specification/3.0.2/examples/v3.0/petstore-expanded.yaml');
```

Access API Description data:

```php
echo $openapi->openapi; // openAPI version, e.g. 3.0.0
echo $openapi->info->title; // API title
foreach($openapi->paths as $path => $definition) {
    // iterate path definitions
}
```

Object properties are exactly like in the [OpenAPI Specification](https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#openapi-specification).
You may also access additional properties added by [specification extensions](https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#specificationExtensions).

Read a component schema and its properties for below spec:

```yaml
openapi: 3.0.0
info:
  title: Test API
  version: 1.0.0
paths:
  /foo:
    put:
      description: create foo
      responses:
        '200':
          description: request succeeded          
components:  
  schemas:
    Foo:
      description: This is an description
      type: object
      properties:
        message:
          type: string
        code:
          type: number
```

```php
# read a component schema
$foo = $openapi->components->schemas['Foo'];

# read a property of schema
$foo->properties['message']
```

### Writing API Description Files

```php
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\PathItem;

// create base API Description
$openapi = new OpenApi([
    'openapi' => '3.0.2',
    'info' => [
        'title' => 'Test API',
        'version' => '1.0.0',
    ],
    'paths' => [],
]);
// manipulate description as needed
$openapi->paths['/test'] = new PathItem([
    'description' => 'something'
]);
// ...

$json = \cebe\openapi\Writer::writeToJson($openapi);
```

results in the following JSON data:

```json
{
    "openapi": "3.0.0",
    "info": {
        "title": "Test API",
        "version": "1.0.0"
    },
    "paths": {
        "/test": {
            "description": "something"
        }
    }
}
```

### Writing API Description Files using prepared Objects

Since version 1.2.0, the above example can also be written like this (passing objects instead of arrays):

```php
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\Info;


// create base API Description
$openapi = new OpenApi([
    'openapi' => '3.0.2',
    'info' => new Info([
        'title' => 'Test API',
        'version' => '1.0.0',
    ]),
    'paths' => [
        '/test' => new PathItem([
            'description' => 'something'
        ]),
    ],
]);
$json = \cebe\openapi\Writer::writeToJson($openapi);
```

### Reading API Description Files and Resolving References

In the above we have passed the raw JSON or YAML data to the Reader. In order to be able to resolve
references to structures in external files, we must provide the full context.

```php
use cebe\openapi\Reader;
use cebe\openapi\spec\OpenAPI;
use cebe\openapi\ReferenceContext;

// there are two different modes for resolving references:
// ALL: resolve all references, which will result in a large description with a lot of repetition
// but no references (except if there are recursive references, these will stop at some level)
$mode = ReferenceContext::RESOLVE_MODE_ALL;
// INLINE: only references to external files are resolved, references to places in the current file
// are still Reference objects.
$mode = ReferenceContext::RESOLVE_MODE_INLINE;

// an absolute URL or file path is needed to allow resolving external references
$openapi = Reader::readFromJsonFile('https://www.example.com/api/openapi.json', OpenAPI::class, $mode);
$openapi = Reader::readFromYamlFile('https://www.example.com/api/openapi.yaml', OpenAPI::class, $mode);
```

If data has been loaded in a different way you can manually resolve references like this by giving a context:

```php
$openapi->resolveReferences(
    new \cebe\openapi\ReferenceContext($openapi, 'https://www.example.com/api/openapi.yaml')
);
```

### Validation

The library provides simple validation operations, that check basic OpenAPI spec requirements.
This is the same as "structural errors found while reading the API Description file" from the CLI tool.
This validation does not include checking against the OpenAPI v3.0 JSON schema, this is only implemented in the CLI.

```
// return `true` in case no errors have been found, `false` in case of errors.
$specValid = $openapi->validate();
// after validation getErrors() can be used to retrieve the list of errors found.
$errors = $openapi->getErrors();
```

> **Note:** Validation is done on a very basic level and is not complete. So a failing validation will show some errors,
> but the list of errors given may not be complete. Also a passing validation does not necessarily indicate a completely
> valid spec.


## Completeness

This library is currently work in progress, the following list tracks completeness:

- [x] read OpenAPI 3.0 JSON
- [x] read OpenAPI 3.0 YAML
- [ ] OpenAPI 3.0 Schema
  - [x] OpenAPI Object
  - [x] Info Object
  - [x] Contact Object
  - [x] License Object
  - [x] Server Object
  - [x] Server Variable Object
  - [x] Components Object
  - [x] Paths Object
  - [x] Path Item Object
  - [x] Operation Object
  - [x] External Documentation Object
  - [x] Parameter Object
  - [x] Request Body Object
  - [x] Media Type Object
  - [x] Encoding Object
  - [x] Responses Object
  - [x] Response Object
  - [x] Callback Object
  - [x] Example Object
  - [x] Link Object
    - [ ] [Runtime Expressions](https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#runtime-expressions)
  - [x] Header Object
  - [x] Tag Object
  - [x] Reference Object
  - [x] Schema Object
    - [x] load/read
    - [ ] validation
  - [x] Discriminator Object
  - [x] XML Object
  - [x] Security Scheme Object
  - [x] OAuth Flows Object
  - [x] OAuth Flow Object
  - [x] Security Requirement Object

# Development

You may use the docker environment for local development:

    docker-compose build
    make IN_DOCKER=1 install
    make IN_DOCKER=1 test
    ...


# Support

**Need help with your API project?**

Professional support, consulting as well as software development services are available:

https://www.cebe.cc/en/contact

Development of this library is sponsored by [cebe.:cloud: "Your Professional Deployment Platform"](https://cebe.cloud).
