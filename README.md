# php-openapi

READ [OpenAPI](https://www.openapis.org/) 3.0.x YAML and JSON files and make the content accessible in PHP objects.

[![Latest Stable Version](https://poser.pugx.org/cebe/php-openapi/v/stable)](https://packagist.org/packages/cebe/php-openapi)
[![Build Status](https://travis-ci.org/cebe/php-openapi.svg?branch=master)](https://travis-ci.org/cebe/php-openapi)
[![License](https://poser.pugx.org/cebe/php-openapi/license)](https://packagist.org/packages/cebe/php-openapi)


## Install

    composer require cebe/php-openapi:~0.9@beta

## Requirements

- PHP 7.1 or higher

## Used by

This library provides a low level API for reading OpenAPI files. It is used by higher level tools to
do awesome work:

- https://github.com/cebe/yii2-openapi Code Generator for REST API from OpenAPI spec
- https://github.com/cebe/yii2-app-api Yii framework application template for developing API-first applications
- ... ([add yours](https://github.com/cebe/php-openapi/edit/master/README.md#L24))

## Usage

### Reading Specification information

Read OpenAPI spec from JSON:

```php
use cebe\openapi\Reader;

$openapi = Reader::readFromJson(file_get_contents('openapi.json'));
```

Read OpenAPI spec from YAML:

```php
use cebe\openapi\Reader;

$openapi = Reader::readFromYaml(file_get_contents('openapi.yaml'));
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
