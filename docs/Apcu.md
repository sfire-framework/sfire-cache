# Apcu Cache Adapter

- [Introduction](#introduction)
- [Requirements](#requirements)
- [Installation](#installation)
- [Setup](#setup)
    - [Namespace](#namespace)
    - [Instance](#instance)
    - [Configuration](#configuration)
- [Usage](#usage)
    - [Retrieving data from cache](#retrieving-data-from-cache)
    - [Storing data in cache](#storing-data-in-cache)
    - [Removing data from cache](#removing-data-from-cache)
    - [Renewing expiration of stored data](#renewing-expiration-of-stored-data)
    - [Check if cache exists](#check-if-cache-exists)
    - [Clear all cache data](#clear-all-cache-data)
- [Examples](#examples)
- [Notes](#notes)
    - [Chaining](#chaining)
    - [Data types](#data-types)



## Introduction

Apcu Cache Adapter is based on storing cache in [APCU](https://www.php.net/apcu) which implements an in memory data caching functionality. When performing an expensive operation, like reading a file or fetch a network resource, you can store the result in the cache to speed up a later request of the same object.
The only downside of APCu is that it’s local to the machine it runs on, and local to the PHP process and system. This means that if you use PHP as a FastCGI process (e.g. Nginx and php-fpm) every PHP process will have its own cache. Unless you expect to run your application on multiple servers or processes APCU is a good caching mechanism.     



## Requirements

- [APCU](https://www.php.net/manual/en/book.apcu.php) should be installed



## Installation

Install this package using [Composer](https://getcomposer.org/):
```shell script
composer require sfire-framework/sfire-cache
```



## Setup

### Namespace
```php
use sFire\Cache\Adapter\Apcu;
```



### Instance

```php
$cache = new Apcu();
```



### Configuration

There are no configuration settings available for the Apcu Cache Adapter. Note that this packages relies on [APCU](https://www.php.net/apcu) and therefore must be installed as an extension. APCU may also be used in the [CLI](https://en.wikipedia.org/wiki/Command-line_interface), but needs an extra setting to do so in `apcu.ini`:
```
apc.enable_cli=on
```



## Usage

#### Retrieving data from cache
To retrieve data, you have to use the `get()` method. If the data exists and is not expired, it will return the data type save. If an item does not or no longer exists, it will return `null` as default. 
You can use the second parameter to overwrite the default value to return when the value for the provided key does not exists.


##### Syntax
```php
$cache -> get(mixed $key, mixed $default);
```

##### Example 1: Retrieving data
```php
$cache -> set('foo', 'bar');
$cache -> get('foo'); //Output "bar"
```

##### Example 2: Setting a default value
Will return "baz" if the key foo does not exist or is expired.
```php
$cache -> get('quez', 'baz'); //Output "baz"
```



#### Storing data in cache

Storing data will always require a key for retrieval of the data after storing. Data is serialized for data type dependency. If you store an integer, it will come out as an integer. See the [Types of data](#types-of-data)" section for compatible data types.
The default expiration is 300 seconds (5 minutes). Expiration will always be the amount of seconds from the moment of store.

##### Syntax
```php
$cache -> set(mixed $key, mixed $value, [int $seconds = 300]);
```

##### Example: Storing data
```php
$cache -> set('foo', 'bar');
$cache -> get('foo'); //Output: "bar"
```



#### Removing data from cache

You can manually expire keys by calling the `expire()` method. The data is no longer available due to permanent deletion and thus cannot be recovered.

##### Syntax
```php
$cache -> expire(mixed $key);
```

##### Example: Removing data based on a given key
```php
$cache -> set('foo', 'bar');
$cache -> get('foo'); //Output "bar"
$cache -> expire('foo');
$cache -> get('foo'); //Output null
```


#### Renewing expiration of stored data
The `touch()` method can renew the expiration time of a stored data based on a given key.

##### Syntax
```php
$cache -> touch(mixed $key);
```

##### Example: Renewing expiration time
```php
$cache -> set('foo', 'bar');
sleep(5);
$cache -> touch('foo'); //Resets the lifetime
```



#### Check if cache exists

The `exists()` method will return a bool `true` or `false` if cache exists based on a given key.

##### Syntax
```php
$cache -> exists(mixed $key);
```


##### Example: Check if cache exists
```php
$cache -> exists('foo'); //Returns false
$cache -> set('foo', 'bar');
$cache -> exists('foo'); //Returns true
```


#### Clear all cache data
The `clear()` method will delete all stored cache data.  The data is no longer available due to permanent deletion and thus cannot be recovered.

##### Syntax
```php
$cache -> clear();
```

##### Example: Clearing all data
```php
$cache -> set('foo', 'bar');
$cache -> clear();
$cache -> get('foo'); //Returns null
```



## Examples

### Anti-brute force based on remote ip address
```php
function isIpBruteForcing() {

    $cache  = new Apcu(); //Create new Apcu cache instance
    $key    = 'anti-brute-force-' .  $_SERVER['REMOTE_ADDR']; //Define a key with the client IP address
    $amount = $cache -> get($key, 0); //Retrieve the amount of hits, default 0
    $cache -> set($key, $amount++, 10000); //Increase the amount and store it in cache
    
    return $amount > 5;
}

isIpBruteForcing();
```



## Notes

### Chaining
Most of the provided methods may be chained together:
```php
$cache -> set('foo', 'bar') -> exists();
```



### Data types

The following data is supported for storing:

- Strings - Character strings of arbitrary size in any PHP-compatible encoding.
- Integers - All integers of any size supported by PHP, up to 64-bit signed.
- Floats - All signed floating-point values.
- Boolean - True and false.
- Null - The actual null value.
- Arrays - Indexed, associative and multidimensional arrays of arbitrary depth.
- Object - Any object that supports lossless serialization and deserialization such that `$o == unserialize(serialize($o))`. Objects may leverage PHP's Serializable interface, `__sleep()` or `__wakeup()` magic methods.