# ItalyStrap Cleaner API

[![Build Status](https://travis-ci.org/ItalyStrap/cleaner.svg?branch=master)](https://travis-ci.org/ItalyStrap/cleaner)
[![Latest Stable Version](https://img.shields.io/packagist/v/italystrap/cleaner.svg)](https://packagist.org/packages/italystrap/cleaner)
[![Total Downloads](https://img.shields.io/packagist/dt/italystrap/cleaner.svg)](https://packagist.org/packages/italystrap/cleaner)
[![Latest Unstable Version](https://img.shields.io/packagist/vpre/italystrap/cleaner.svg)](https://packagist.org/packages/italystrap/cleaner)
[![License](https://img.shields.io/packagist/l/italystrap/cleaner.svg)](https://packagist.org/packages/italystrap/cleaner)
![PHP from Packagist](https://img.shields.io/packagist/php-v/italystrap/cleaner)

PHP Sanitizer and Validation OOP way

## Table Of Contents

* [Installation](#installation)
* [Basic Usage](#basic-usage)
* [Advanced Usage](#advanced-usage)
* [Contributing](#contributing)
* [License](#license)

## Installation

The best way to use this package is through Composer:

```CMD
composer require italystrap/cleaner
```
This package adheres to the [SemVer](http://semver.org/) specification and will be fully backward compatible between minor versions.

## Basic Usage

```php
$sanitizator = new \ItalyStrap\Cleaner\Sanitization();
$validator = new \ItalyStrap\Cleaner\Validation();

$sanitizator->addRules( 'trim' );
// `Test`
echo $sanitizator->sanitize( ' Test ' );

// Single string rule
$rule = 'trim';
$sanitizator->addRules( $rule );
// `Test`
echo $sanitizator->sanitize( ' Test ' );

// Multiple rules in string
$rules = 'strip_tags|trim';
$sanitizator->addRules( $rules );
// `Test`
echo $sanitizator->sanitize( ' <p> Test </p> ' );

// Multiple rules string in array
$rules_arr = [
	'strip_tags',
	'trim',
];
$sanitizator->addRules( $rules_arr );
// `Test`
echo $sanitizator->sanitize( ' <p> Test </p> ' );

$callback = function ( $value ) {
	return  'New value from callback';
};

// Callable rule in array
$rule_callable = [
	$callback
];
$sanitizator->addRules( $rule_callable );
// `New value from callback`
echo $sanitizator->sanitize( ' <p> Test </p> ' );

// Multiple callable rules in array
$rules_callable = [
	$callback,
	$callback,
];
$sanitizator->addRules( $rules_callable );
// `New value from callback`
echo $sanitizator->sanitize( ' <p> Test </p> ' );
```
Every ::sanitize() or ::validate() call will reset the rules provided.
Make sure you provide new rule befor calling ::sanitize() or ::validate().

## Advanced Usage

> TODO

## Contributing

All feedback / bug reports / pull requests are welcome.

## License

Copyright (c) 2019 Enea Overclokk, ItalyStrap

This code is licensed under the [MIT](LICENSE).

## Credits

> TODO