# WordPress Plugin Test Helper
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://packagist.org/packages/ekandreas/bladerunner)

*** WORK IN PROGRESS ***

Step 1, checking out wp develop to unittest, ok!

Step 2, adding local test, in progress...

Step 3, put the tests inside the docker machine

## Setup

To be included inside your PHP Deploy projects with WordPress development.

Include the recipe in your deployer script, eg:
```php
include_once 'vendor/ekandreas/testrunner/recipe.php';
```

## Running tests

Run the test, eg:
```bash
dep tests
```

If you have a stage option, eg:
Run the test, eg:
```bash
dep tests development
```
