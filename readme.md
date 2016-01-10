# WordPress Plugin Test Helper
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://packagist.org/packages/ekandreas/bladerunner)

*** WORK IN PROGRESS ***

*The testsuite should be placed within a plugin that's going to be tested.*

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
include_once 'vendor/ekandreas/testrunner/recipe.php';
```

Add hostname to the virtual machine for tests, will default to tests, eg:
```php
<!-- deploy.php -->
set( 'docker_host_name', 'tests');
```
If you don't have a docker machine setup then the deploy script will try to create it for you. Virtualbox as default.

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
Place the bin/dep inside your /usr/local/bin folder for easier access.
```bash
chmod +x vendor/bin/dep
cp vendor/bin/dep /usr/local/bin/dep
```
Then you can skip the vendor/bin -part, eg:
```bash
dep tests
```
and
```bash
dep tests:run
```

## Partitial runs

### Booting up Docker and make installation
```bash
vendor/bin/dep tests:up
```

### Running just the tests (after tests:up)
```bash
vendor/bin/dep tests:run
```

### Stop the tests
This will kill the containers
```bash
vendor/bin/dep tests:stop
```

### Killing Docker machine
This will kill the virtual test machine
```bash
vendor/bin/dep tests:kill
```

