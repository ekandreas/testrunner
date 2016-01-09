# WordPress Plugin Test Helper
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://packagist.org/packages/ekandreas/bladerunner)

*** WORK IN PROGRESS ***

ALL TESTS PERFORMED OUTSIDE THE CONTAINER, THIS WILL CHANGE...

*The testsuite should be placed within a plugin that's going to be tested.*

Step 1, checking out wp develop to unittest, ok!

Step 2, adding local test, in progress...

Step 3, put the tests inside the docker machine

## Requirements
* Docker-machine with VirtualBox
* PHP Composer 

## Setup
Install this lib with composer, eg:
```
composer require ekandreas/testrunner:dev-master
```

Include the common recipe and testrunner recipe in your deployer script, eg:
```php
<!-- deploy.php -->
include_once 'vendor/deployer/deployer/recipe/common.php';
include_once 'vendor/ekandreas/testrunner/recipe.php';
```

Add the stage for the Docker machine with name and IP, eg:
```php
<!-- deploy.php -->
set( 'docker_host_name', 'test');
```
If you don't have a docker machine setup then the deploy script will try to create it for you. Virtualbox as default.

### Deploy file example
```php
<!-- deploy.php -->
<?php
date_default_timezone_set('Europe/Stockholm');

include_once 'vendor/deployer/deployer/recipe/common.php';
include_once 'vendor/ekandreas/testrunner/recipe.php';

set( 'docker_host_name', 'test');

```

## Running tests

Run the test with deployer, eg:
```bash
vendor/bin/dep testrunner
```

