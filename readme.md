# WordPress Plugin Test Helper
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://packagist.org/packages/ekandreas/bladerunner)

*** WORK IN PROGRESS ***

ALL TESTS PERFORMED OUTSIDE THE CONTAINER, THIS WILL CHANGE...

Step 1, checking out wp develop to unittest, ok!

Step 2, adding local test, in progress...

Step 3, put the tests inside the docker machine

## Requirements
* Docker-machine with VirtualBox
* PHP Composer 

## Setup

To be included inside your PHP Deploy projects with WordPress development.

Include the common recipe and testrunner recipe in your deployer script, eg:
```php
<!-- deploy.php -->
include_once 'vendor/deployer/deployer/recipe/common.php';
include_once 'vendor/ekandreas/testrunner/recipe.php';
```

Add the stage for the Docker machine with name and IP, eg:
```php
<!-- deploy.php -->
server( 'test', '192.168.99.100');
```
If you don't have a docker machine setup then the deploy script will try to create it for you. Virtualbox as default.

## Running tests

Run the test against your stage, eg:
```bash
vendor/bin/dep phpunit test
```

