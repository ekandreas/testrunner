# WordPress Plugin Test Helper with Docker
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://packagist.org/packages/ekandreas/bladerunner)

*** WORK IN PROGRESS ***

When you develop a plugin for WordPress it's important to also build test cases and use them continuous.

This package is a helper to run tests within your plugin folder but use Docker as a testing instance and report generator.

*The testsuite should be placed within a plugin that's going to be tested.*

```
root
│   wp-admin
└───wp-content
    ├───plugins
    │   ├───your-plugin <- install with composer require ekandreas/testrunner:*
    │   │   ...
```

Note! First time is going to take a long time due to creating images and installing wordpress-develop folder, etc.

To continuously run tests to a plugin use the partitial test command:
```bash
dep tests:run
```


## Requirements
* Docker-machine with VirtualBox
* PHP Composer 

## Setup
Install this lib with composer, eg:
```
composer require ekandreas/testrunner:dev-master
```

Now create a deployment file in root of your plugin, eg:
```php
<!-- deploy.php -->
date_default_timezone_set('Europe/Stockholm');
include_once 'vendor/ekandreas/testrunner/recipe.php';
```

You can now enjoy tests with docker with the following deployment command:
```bash
vendor/bin/dep tests
```
This is only the first time, aprox 10 min first time.

And with continuous tests:
```bash
vendor/bin/dep tests:run
```
Only seconds to a test report.


### Deploy file example
```php
<!-- deploy.php -->
<?php
date_default_timezone_set('Europe/Stockholm');

include_once 'vendor/ekandreas/testrunner/recipe.php';

set( 'docker_host_name', 'tests');

```

## Complete run
```bash
vendor/bin/dep tests
```

## Partitial run

### Booting up Docker and make installation
```bash
dep tests:up
```

### Rebuild Docker container images
```bash
dep tests:rebuild
```

### Running just the tests (after tests:up)
```bash
dep tests:run
```

### Stop the tests
This will kill the containers
```bash
dep tests:stop
```

### Killing Docker machine
This will kill the virtual test machine
```bash
dep tests:kill
```

## Recipe parameters, options

If you don't want to use the default docker-machine, define your own, eg:
```php
<!-- deploy.php -->
date_default_timezone_set('Europe/Stockholm');
include_once 'vendor/ekandreas/testrunner/recipe.php';
set( 'docker_host_name', 'tests');
```
If you don't have a docker machine setup then the deploy script will try to create it for you. Virtualbox as default.

## TODO
* Just one single command ``dep tests`` to check and startup mysql and install if missing.

