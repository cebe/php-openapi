# php-openapi

READ OpenAPI yaml and json files and make the content accessable in PHP objects.

## Install

    composer require cebe/php-openapi

## Requirements

- PHP 7.0 or higher

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

Object properties are exactly like in the [OpenAPI specification](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#openapi-specification).
You may also access additional properties added by specification extensions.


## Completeness

This library is currently work in progress, the following list tracks completeness:

- [ ] read OpenAPI 3.0 JSON
- [ ] read OpenAPI 3.0 YAML
- [ ] OpenAPI 3.0 Schema
  - [x] OpenAPI Object
  - [x] Info Object
  - [x] Contact Object
  - [x] License Object
  - [x] Server Object
  - [x] Server Variable Object
  - [x] Components Object
  - [ ] Paths Object
  - [ ] Path Item Object
  - [ ] Operation Object
  - [x] External Documentation Object
  - [ ] Parameter Object
  - [ ] Request Body Object
  - [ ] Media Type Object
  - [ ] Encoding Object
  - [ ] Responses Object
  - [ ] Response Object
  - [ ] Callback Object
  - [ ] Example Object
  - [ ] Link Object
  - [ ] Header Object
  - [ ] Tag Object
  - [ ] Reference Object
  - [ ] Schema Object
  - [ ] Discriminator Object
  - [ ] XML Object
  - [ ] Security Scheme Object
  - [ ] OAuth Flows Object
  - [ ] OAuth Flow Object
  - [ ] Security Requirement Object
