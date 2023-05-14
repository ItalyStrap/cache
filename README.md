# ItalyStrap Cache API

[![Build status](https://github.com/ItalyStrap/cache/actions/workflows/test.yml/badge.svg)](https://github.com/ItalyStrap/cache/actions/workflows/test.yml?query=workflow%3Atest)
[![Latest Stable Version](https://img.shields.io/packagist/v/italystrap/cache.svg)](https://packagist.org/packages/italystrap/cache)
[![Total Downloads](https://img.shields.io/packagist/dt/italystrap/cache.svg)](https://packagist.org/packages/italystrap/cache)
[![Latest Unstable Version](https://img.shields.io/packagist/vpre/italystrap/cache.svg)](https://packagist.org/packages/italystrap/cache)
[![License](https://img.shields.io/packagist/l/italystrap/cache.svg)](https://packagist.org/packages/italystrap/cache)
![PHP from Packagist](https://img.shields.io/packagist/php-v/italystrap/cache)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2FItalyStrap%2Fcache%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/ItalyStrap/cache/master)

PSR-16 & PSR-6 Cache implementations for WordPress transient and cache the OOP way

**Version 2.0 is a BC breaks please read the following documentation**

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

For more information about the WordPress API:

* [WordPress Transients API docs](https://developer.wordpress.org/apis/transients/)
* [WordPress Cache API docs](https://developer.wordpress.org/reference/classes/wp_object_cache/)

## Why this package?

Initially I created this package to use the [PSR-16](https://www.php-fig.org/psr/psr-16/) cache interface in WordPress, but starting from this issue [#4](https://github.com/ItalyStrap/cache/issues/4) I decided to extract the drivers and also add the [PSR-6](https://www.php-fig.org/psr/psr-6/) cache interface too.

From version 2.0 I also added the PSR-6 implementation, so you can also use the `Pool` class to cache your data if you need it.

So this package now support both the [PSR-16](https://www.php-fig.org/psr/psr-16/) and [PSR-6](https://www.php-fig.org/psr/psr-6/) cache interfaces.

The driver required by this package uses the Transients and Object Cache APIs from WordPress to store the data, but if you need to use other APIs, you can create your own driver because just implements the interface from [Storage API](https://github.com/ItalyStrap/storage).

## Basic Usage

### Timer constants

Inside WordPress there are some constants that can be used to express time in seconds.
Here is a list of them:

```php
const MINUTE_IN_SECONDS  = 60; // (seconds)
const HOUR_IN_SECONDS    = 60 * MINUTE_IN_SECONDS;
const DAY_IN_SECONDS     = 24 * HOUR_IN_SECONDS;
const WEEK_IN_SECONDS    = 7 * DAY_IN_SECONDS;
const MONTH_IN_SECONDS   = 30 * DAY_IN_SECONDS;
const YEAR_IN_SECONDS    = 365 * DAY_IN_SECONDS;
```

### Common usage with builtin WordPress Transients API

```php
if (false === ($special_data_to_save = \get_transient('special_data_to_save'))) {
    // It wasn't there, so regenerate the data and save the transient
    $special_data_to_save = ['some-key' => 'come value'];
    \set_transient('special_data_to_save', $special_data_to_save, 12 * HOUR_IN_SECONDS);
}
```

The data you can save can be anything that is supported by the [Serialization API](https://developer.wordpress.org/reference/functions/maybe_serialize/).
In short, you can save any scalar value, array, object.

### Moving from Version 1 to Version 2

The first important thing is from the version 2 you need to pass the driver to the constructor of the class you want to use.

`SimpleCache` and `Pool` are the two classes that need the driver to be passed to the constructor.

The driver is an object wrapper for the WordPress Transient API and the WordPress Object Cache API.

Below in the documentation you will find the name of the driver that you can use.

The second important thing is that the driver must implement the `StorageInterface` from [Storage API](https://github.com/ItalyStrap/storage), this way if you need tp create your own driver you can do it simply by implementing the interface.

### Why the needs of an Expiration object?

The expiration object is used to set the expiration time of the cache.
Because I want to be as close as possible to the PSR-16 and PSR-6 specifications, I have created an object that is responsible for setting the expiration time, this way I can reuse the same logic across all PSR-16 and PSR-6 implementations and I didn't need to create more methods that are not in the specifications.

### Common usage with the Pool cache

```php
use ItalyStrap\Cache\Pool;
use ItalyStrap\Cache\Expiration;
use ItalyStrap\Storage\Transient;

$driver = new Transient(); // Or use new Cache()
$expiration = new Expiration();

$pool = new Pool($driver, $expiration);

// Pass the pool object to other classes that need to save data
// then retrieve the data from the pool
$item = $pool->getItem('special_data_to_save');
if (!$item->isHit()) {
    // It wasn't there, so regenerate the data and save the transient
    $item->set(['some-key' => 'some value']);
    $item->expiresAfter(12 * HOUR_IN_SECONDS);
    $pool->save($item);
}
$special_data_to_save = $item->get();

['some-key' => 'some value'] === $special_data_to_save; // True
```

### Common usage with the SimpleCache

```php
use ItalyStrap\Cache\SimpleCache;
use ItalyStrap\Storage\Transient;

$driver = new Transient(); // Or use new Cache()

$cache = new SimpleCache($driver);

// Pay attention to `SimpleCacheInterface::get()` method because if there is no value will return `null` and not `false` as the WordPress Transient API does.
if (null === ($special_data_to_save = $cache->get('special_data_to_save'))) {
    // It wasn't there, so regenerate the data and save the transient
    $special_data_to_save = ['some-key' => 'some value'];
    $cache->set('special_data_to_save', $special_data_to_save, 12 * HOUR_IN_SECONDS);
}

['some-key' => 'some value'] === $special_data_to_save; // True
```

### Deleting cache with Pool

```php
use ItalyStrap\Cache\Pool;
use ItalyStrap\Cache\Expiration;
use ItalyStrap\Storage\Transient;

$driver = new Transient(); // Or use new Cache()
$expiration = new Expiration();

$pool = new Pool($driver, $expiration);

$item = $pool->getItem('special_data_to_save');
$item->set(['some-key' => 'some value']);
$item->expiresAfter(12 * HOUR_IN_SECONDS);
$pool->save($item);

$pool->deleteItem('special_data_to_save'); // Return bool

// `::getItem()` will return a new item instance, always
$pool->getItem('special_data_to_save')->isHit(); // Return false
```


### Deleting cache with SimpleCache

```php
use ItalyStrap\Cache\SimpleCache;
use ItalyStrap\Storage\Transient;

$driver = new Transient(); // Or use new Cache()

$cache = new SimpleCache($driver);

$cache->set('special_data_to_save', ['some-key' => 'some value'], 12 * HOUR_IN_SECONDS);

$cache->delete('special_data_to_save'); // Return bool

$cache->get('special_data_to_save'); // Return null
```

### Check cache exists with Pool

```php

use ItalyStrap\Cache\Pool;
use ItalyStrap\Cache\Expiration;
use ItalyStrap\Storage\Transient;

$driver = new Transient(); // Or use new Cache()
$expiration = new Expiration();
    
$pool = new Pool($driver, $expiration);

$item = $pool->getItem('special_data_to_save');
$item->set(['some-key' => 'some value']);
$item->expiresAfter(12 * HOUR_IN_SECONDS);
$pool->save($item);

$pool->hasItem('special_data_to_save'); // Return true

// But also this will return false if the item is expired or not exists
$pool->hasItem('expired_or_not_existent_value'); // Return false
```


### Check cache exists with SimpleCache

```php
use ItalyStrap\Cache\SimpleCache;
use ItalyStrap\Storage\Transient;

$driver = new Transient(); // Or use new Cache()

$cache = new SimpleCache($driver);

$cache->set('special_data_to_save', ['some-key' => 'some value'], 12 * HOUR_IN_SECONDS);
$cache->has('special_data_to_save'); // Return true

// But also this will return false if the item is expired or not exists
$cache->has('expired_or_not_existent_value'); // Return false
```

### Saving multiple cache with SimpleCache

```php
use ItalyStrap\Cache\SimpleCache;
use ItalyStrap\Storage\Transient;

$driver = new Transient(); // Or use new Cache()

$cache = new SimpleCache($driver);

$values = [
    'key'       => 'value',
    'key2'      => 'value2',
];

$cache->setMultiple($values, 12 * HOUR_IN_SECONDS); // Return bool
```

### Fetching multiple cache with SimpleCache

```php
use ItalyStrap\Cache\SimpleCache;
use ItalyStrap\Storage\Transient;

$driver = new Transient(); // Or use new Cache()

$cache = new SimpleCache($driver);

$values = [
    'key'       => 'value',
    'key2'      => 'value2',
    'key3'      => false, // This will be replaced with 'some default value' because the method pass a default value
];

$fetched_values = $cache->getMultiple(\array_keys($values), 'some default value'); // Return values
```

### Deleting multiple cache with SimpleCache

```php
use ItalyStrap\Cache\SimpleCache;
use ItalyStrap\Storage\Transient;

$driver = new Transient(); // Or use new Cache()

$cache = new SimpleCache($driver);

$values = [
    'key'       => 'value',
    'key2'      => 'value2',
    'key3'      => false,
];

$cache->deleteMultiple(\array_keys($values)); // Return bool
```

### Clearing cache with SimpleCache

This method do not clear the entire WordPress cache, only the cache used by client with
::set() and ::setMultiple() methods.

```php
use ItalyStrap\Cache\SimpleCache;
use ItalyStrap\Storage\Transient;

$driver = new Transient(); // Or use new Cache()

$cache = new SimpleCache($driver);
$cache->set('special_data_to_save',['some-key' => 'come value'], 12 * HOUR_IN_SECONDS);

$values = [
    'key'       => 'value',
    'key2'      => 'value2',
];

$cache->setMultiple($values, 12 * HOUR_IN_SECONDS);

$cache->clear(); // Return bool

$cache->get('special_data_to_save'); // Return null
$cache->get('key'); // Return null
$cache->get('key2'); // Return null
```

Cache::clear() will flush 'special_data_to_save', 'key' and 'key2'.

## Other examples

```php
use ItalyStrap\Cache\SimpleCache;
use ItalyStrap\Storage\Transient;

$driver = new Transient(); // Or use new Cache()

$cache = new SimpleCache($driver);

// Get any existing copy of our transient data
if (false === ($special_data_to_save = $cache->get('special_data_to_save'))) {
    // It wasn't there, so regenerate the data and save the transient
     $cache->set('special_data_to_save', ['some-key' => 'some value'], 12 * HOUR_IN_SECONDS);
}
// Use the data like you would have normally...

//Or

// Get any existing copy of our transient data
if (!$cache->has('special_data_to_save')) {
    // It wasn't there, so regenerate the data and save the transient
     $cache->set('special_data_to_save', ['some-key' => 'some value'], 12 * HOUR_IN_SECONDS);
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
* [PHP Cache organisation](https://github.com/php-cache/integration-tests)