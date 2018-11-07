# php-openapi

READ [OpenAPI](https://www.openapis.org/) 3.0.x YAML and JSON files and make the content accessible in PHP objects.

[![Latest Stable Version](https://poser.pugx.org/cebe/php-openapi/v/stable)](https://packagist.org/packages/cebe/php-openapi)
[![Build Status](https://travis-ci.org/cebe/php-openapi.svg?branch=master)](https://travis-ci.org/cebe/php-openapi)
[![License](https://poser.pugx.org/cebe/php-openapi/license)](https://packagist.org/packages/cebe/php-openapi)


## Install

    composer require cebe/php-openapi

## Requirements

- PHP 7.1 or higher

## Usage

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
  - [ ] Paths Object
  - [x] Path Item Object
  - [ ] Operation Object
  - [x] External Documentation Object
  - [ ] Parameter Object
  - [x] Request Body Object
  - [ ] Media Type Object
  - [ ] Encoding Object
  - [ ] Responses Object
  - [x] Response Object
  - [ ] Callback Object
  - [ ] Example Object
  - [x] Link Object
    - [ ] [Runtime Expressions](https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#runtime-expressions)
  - [ ] Header Object
  - [x] Tag Object
  - [ ] Reference Object
  - [x] Schema Object
    - [x] load/read
       - [ ] `additionalProperties` field
    - [ ] validation
  - [x] Discriminator Object
  - [x] XML Object
  - [x] Security Scheme Object
  - [x] OAuth Flows Object
  - [x] OAuth Flow Object
  - [x] Security Requirement Object
