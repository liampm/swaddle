# Swaddle

Swaddle allows you to wrap collections of key-value pairs such as associative arrays and `\stdClass` objects so that you can more easily access their properties and have more certainty of what properties do exist.
The aim of Swaddle is to reduce the amount of `isset` or `property_exists` checks.

## Installation
```bash
$ composer require liampm/swaddle
```

## Basic Usage

```php
<?php

use liampm\Swaddle;

$configuration = json_decode(file_get_contents('config.json'));

$swaddle = Swaddle::wrapObject($configuration);

$swaddle->getProperty('count', 0);
```