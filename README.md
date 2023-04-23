# ItalyStrap Cache API

[![Build status](https://github.com/ItalyStrap/cache/actions/workflows/test.yml/badge.svg)](https://github.com/ItalyStrap/cache/actions/workflows/test.yml?query=workflow%3Atest)
[![Latest Stable Version](https://img.shields.io/packagist/v/italystrap/cache.svg)](https://packagist.org/packages/italystrap/cache)
[![Total Downloads](https://img.shields.io/packagist/dt/italystrap/cache.svg)](https://packagist.org/packages/italystrap/cache)
[![Latest Unstable Version](https://img.shields.io/packagist/vpre/italystrap/cache.svg)](https://packagist.org/packages/italystrap/cache)
[![License](https://img.shields.io/packagist/l/italystrap/cache.svg)](https://packagist.org/packages/italystrap/cache)
![PHP from Packagist](https://img.shields.io/packagist/php-v/italystrap/cache)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2FItalyStrap%2Fcache%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/ItalyStrap/cache/master)

PSR-16 & PSR-6 Cache implementations for WordPress transient and cache the OOP way

### This is a BC breaks please read the following

## Table Of Contents

* [Installation](#installation)
* [Basic Usage](#basic-usage)
* [Advanced Usage](#advanced-usage)
* [Contributing](#contributing)
* [License](#license)

## Installation

The best way to use this package is through Composer:

```CMD
composer require italystrap/cache
```
This package adheres to the [SemVer](http://semver.org/) specification and will be fully backward compatible between minor versions.

## Basic Usage

From [WordPress Transients API docs](https://codex.wordpress.org/Transients_API)

### Timer constants

```php
const MINUTE_IN_SECONDS  = 60; // (seconds)
const HOUR_IN_SECONDS    = 60 * MINUTE_IN_SECONDS;
const DAY_IN_SECONDS     = 24 * HOUR_IN_SECONDS;
const WEEK_IN_SECONDS    = 7 * DAY_IN_SECONDS;
const MONTH_IN_SECONDS   = 30 * DAY_IN_SECONDS;
const YEAR_IN_SECONDS    = 365 * DAY_IN_SECONDS;
```

### Common usage with WordPress Transients API

```php
if (false === ($special_data_to_save = \get_transient('special_data_to_save'))) {
    // It wasn't there, so regenerate the data and save the transient
    $special_data_to_save = ['some-key' => 'come value'];
    \set_transient('special_data_to_save', $special_data_to_save, 12 * HOUR_IN_SECONDS);
}
```

### Common usage with CacheItemPool

```php
use ItalyStrap\Cache\CacheItemPool;

$pool = new CacheItemPool();
$item = $pool->getItem('special_data_to_save');
if (! $item->isHit()) {
    // It wasn't there, so regenerate the data and save the transient
    $item->set(['some-key' => 'come value']);
    $item->expiresAfter(12 * HOUR_IN_SECONDS);
    $pool->save($item);
}
$special_data_to_save = $item->get();

'special_data_to_save' === $special_data_to_save; // True
```

### Saving cache

```php
use ItalyStrap\Cache\SimpleCache;

$cache = new SimpleCache();
$cache->set('special_data_to_save', ['some-key' => 'come value'], 12 * HOUR_IN_SECONDS); // Return bool
```

### Fetching cache

```php
use ItalyStrap\Cache\SimpleCache;

$cache = new SimpleCache();
$cache->set('special_data_to_save', ['some-key' => 'come value'], 12 * HOUR_IN_SECONDS);

$fetched_value = $cache->get('special_data_to_save'); // ['some-key' => 'come value']
```

### Deleting cache

```php
use ItalyStrap\Cache\SimpleCache;

$cache = new SimpleCache();
$cache->set('special_data_to_save', ['some-key' => 'come value'], 12 * HOUR_IN_SECONDS);

$cache->delete('special_data_to_save'); // Return bool
```

### Check cache exists

```php
use ItalyStrap\Cache\SimpleCache;

$cache = new SimpleCache();
$cache->has('special_data_to_save'); // Return bool
```

### Saving multiple cache

```php
use ItalyStrap\Cache\SimpleCache;

$cache = new SimpleCache();

$values = [
    'key'       => 'value',
    'key2'      => 'value2',
];

$cache->setMultiple($values, 12 * HOUR_IN_SECONDS); // Return bool
```

### Fetching multiple cache

```php
use ItalyStrap\Cache\SimpleCache;

$cache = new SimpleCache();

$values = [
    'key'       => 'value',
    'key2'      => 'value2',
    'key3'      => false, // This will be replaced with 'some default value'
];

$fetched_values = $cache->getMultiple(\array_keys($values), 'some default value'); // Return values
```

### Deleting multiple cache

```php
use ItalyStrap\Cache\SimpleCache;

$cache = new SimpleCache();

$values = [
    'key'       => 'value',
    'key2'      => 'value2',
    'key3'      => false, // This will be replaced with 'some default value'
];

$cache->deleteMultiple(\array_keys($values)); // Return bool
```

### Clearing cache

This method do not clear the entire WordPress cache, only the cache used by client with
::set() and ::setMultiple() methods.

```php
use ItalyStrap\Cache\SimpleCache;

$cache = new SimpleCache();
$cache->set('special_data_to_save',['some-key' => 'come value'], 12 * HOUR_IN_SECONDS);

$values = [
    'key'       => 'value',
    'key2'      => 'value2',
];

$cache->setMultiple($values, 12 * HOUR_IN_SECONDS);

$cache->clear(); // Return bool
```

Cache::clear() will flush 'special_data_to_save', 'key' and 'key2'.

## Advanced Usage

```php
use ItalyStrap\Cache\SimpleCache;

$cache = new SimpleCache();

// Get any existing copy of our transient data
if (false === ($special_data_to_save = $cache->get('special_data_to_save'))) {
    // It wasn't there, so regenerate the data and save the transient
     $cache->set('special_data_to_save', ['some-key' => 'come value'], 12 * HOUR_IN_SECONDS);
}
// Use the data like you would have normally...

//Or

// Get any existing copy of our transient data
if (!$cache->has('special_data_to_save')) {
    // It wasn't there, so regenerate the data and save the transient
     $cache->set('special_data_to_save', ['some-key' => 'come value'], 12 * HOUR_IN_SECONDS);
}
// Use the data like you would have normally...
```

## Contributing

All feedback / bug reports / pull requests are welcome.

## License

Copyright (c) 2019 Enea Overclokk, ItalyStrap

This code is licensed under the [MIT](LICENSE).

## Credits

* [DoctrineSimpleCache](https://github.com/Roave/DoctrineSimpleCache)

## To read

* https://json5.org/
* https://github.com/Roave/infection-static-analysis-plugin
* https://www.sitepoint.com/creating-strictly-typed-arrays-collections-php/