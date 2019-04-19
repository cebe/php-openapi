# php-openapi

Read and write [OpenAPI](https://www.openapis.org/) 3.0.x YAML and JSON files and make the content accessible in PHP objects.

It also provides a CLI tool for validating and converting OpenAPI 3.0.x YAML and JSON files.

[![Latest Stable Version](https://poser.pugx.org/cebe/php-openapi/v/stable)](https://packagist.org/packages/cebe/php-openapi)
[![Build Status](https://travis-ci.org/cebe/php-openapi.svg?branch=master)](https://travis-ci.org/cebe/php-openapi)
[![License](https://poser.pugx.org/cebe/php-openapi/license)](https://packagist.org/packages/cebe/php-openapi)


## Install

    composer require cebe/php-openapi:~0.9@beta

## Requirements

- PHP 7.1 or higher

## Used by

This library provides a low level API for reading and writing OpenAPI files. It is used by higher level tools to
do awesome work:

- https://github.com/cebe/yii2-openapi Code Generator for REST API from OpenAPI spec, includes fake data generator.
- https://github.com/cebe/yii2-app-api Yii framework application template for developing API-first applications
- ... ([add yours](https://github.com/cebe/php-openapi/edit/master/README.md#L24))

## Usage

### CLI tool

    $ vendor/bin/php-openapi help
    PHP OpenAPI 3 tool
    ------------------
    by Carsten Brandt <mail@cebe.cc>

    Usage:
      php-openapi <command> [<options>] [input.yml|input.json] [output.yml|output.json]

      The following commands are available:

        validate   Validate the API description in the specified input file against the OpenAPI v3.0 schema.
                   Note: the validation is performed in two steps. The results is composed of
                    (1) structural errors found while reading the API description file, and
                    (2) violations of the OpenAPI v3.0 schema.

                   If no input file is specified input will be read from STDIN.
                   The tool will try to auto-detect the content type of the input, but may fail
                   to do so, you may specify --read-yaml or --read-json to force the file type.

                   Exits with code 2 on validation errors, 1 on other errors and 0 on success.

        convert    Convert a JSON or YAML input file to JSON or YAML output file.
                   References are being resolved so the output will be a single specification file.

                   If no input file is specified input will be read from STDIN.
                   If no output file is specified output will be written to STDOUT.
                   The tool will try to auto-detect the content type of the input and output file, but may fail
                   to do so, you may specify --read-yaml or --read-json to force the input file type.
                   and --write-yaml or --write-json to force the output file type.

        help       Shows this usage information.

      Options:

        --read-json   force reading input as JSON. Auto-detect if not specified.
        --read-yaml   force reading input as YAML. Auto-detect if not specified.
        --write-json  force writing output as JSON. Auto-detect if not specified.
        --write-yaml  force writing output as YAML. Auto-detect if not specified.


### Reading Specification information

Read OpenAPI spec from JSON file:

```php
use cebe\openapi\Reader;

// realpath is needed for resolving references with relative Paths or URLs
$openapi = Reader::readFromJsonFile(realpath('openapi.json'));
```

Read OpenAPI spec from YAML:

```php
use cebe\openapi\Reader;

// realpath is needed for resolving references with relative Paths or URLs
$openapi = Reader::readFromYamlFile(realpath('openapi.json'));
// you may also specify the URL to your description file
$openapi = Reader::readFromYamlFile('https://raw.githubusercontent.com/OAI/OpenAPI-Specification/3.0.2/examples/v3.0/petstore-expanded.yaml');
```

Access specification data:

```php
echo $openapi->openapi; // openAPI version, e.g. 3.0.0
echo $openapi->info->title; // API title
foreach($openapi->paths as $path => $definition) {
    // iterate path definitions
}
```

Object properties are exactly like in the [OpenAPI specification](https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#openapi-specification).
You may also access additional properties added by specification extensions.

### Writing Specification files

```php
// create base description
$openapi = new \cebe\openapi\spec\OpenApi([
    'openapi' => '3.0.2',
    'info' => [
        'title' => 'Test API',
        'version' => '1.0.0',
    ],
    'paths' => [],
]);
// manipulate description as needed
$openapi->paths['/test'] = new \cebe\openapi\spec\PathItem([
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

### Reading Specification Files and Resolving References

In the above we have passed the raw JSON or YAML data to the Reader. In order to be able to resolve
references to external files that may exist in the specification files, we must provide the full context.

```php
use cebe\openapi\Reader;
// an absolute URL or file path is needed to allow resolving internal references
$openapi = Reader::readFromJsonFile('https://www.example.com/api/openapi.json');
$openapi = Reader::readFromYamlFile('https://www.example.com/api/openapi.yaml');
```

If data has been loaded in a different way you can manually resolve references like this by giving a context:

```php
$openapi->resolveReferences(
    new \cebe\openapi\ReferenceContext($openapi, 'https://www.example.com/api/openapi.yaml')
);
```

> **Note:** Resolving references currently does not deal with references in referenced files, you have to call it multiple times to resolve these.

### Validation

The library provides simple validation operations, that check basic OpenAPI spec requirements.
This is the same as "structural errors found while reading the API description file" from the CLI tool.
This validation does not include checking against the OpenAPI v3.0 JSON schema.

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

# Support

Professional support, consulting as well as software development services are available:

https://www.cebe.cc/en/contact

Development of this library is sponsored by [cebe.:cloud: "Your Professional Deployment Platform"](https://cebe.cloud).
