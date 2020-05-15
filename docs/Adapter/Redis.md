# Redis Cache Adapter

- [Introduction](#introduction)
- [Installation](#installation)
- [Setup](#setup)
    - [Namespace](#namespace)
    - [Instance](#installation)
    - [Configuration](#configuration)
- [Usage](#usage)
    - [Retrieving data from cache](#retrieving-data-from-cache)
    - [Storing data in cache](#storing-data-in-cache)
    - [Removing data from cache](#removing-data-from-cache)
    - [Renewing expiration of stored data](#renewing-expiration-of-stored-data)
    - [Check if cache exists](#check-if-cache-exists)
    - [Retrieve the expiration time](#retrieve-the-expiration-time)
    - [Clear all cache data](#clear-all-cache-data)
- [Examples](#examples)
- [Notes](#notes)
    - [Chaining](#chaining)
    - [Data types](#data-types)


## Introduction
[Redis](https://redis.io/) is an open source, in-memory data structure store, used as a database, cache and message broker. The Redis Cache Adapter provides an easy-to-use interface for retrieving and setting data using a Redis cache instance. This instance may be installed locally or even be on a different network.   


## Installation
Install this package using [Composer](https://getcomposer.org/):
```shell script
composer require sfire-framework/sfire-cache
```


## Setup
### Namespace
```php
use sFire\Cache\Adapter\Redis;
```

### Instance
```php
$cache = new Redis();
```

### Configuration
Below are the default values that the package uses.

#### Default settings
- Default host is "127.0.0.1"
- Default password is null
- Default port is 6379
- Default connection timeout is 2.5 seconds 

#### Overwriting settings
##### Host
You may set the host which the cache adapter should connect to by calling the `setHost()` method. This can be a host name or IP address which sFire will connect to.
###### Syntax
```php
$cache -> setHost(string $host);
```
###### Example
```php
$cache -> setHost('192.168.1.250');
```

##### Password
Redis may be protected with a password. Use the  `setPassword()` method to set the Redis password.
##### Syntax
```php
$cache -> setPassword(string $password);
```
##### Example
```php
$cache -> setPassword('password');
```

##### Port
You can set the port which a Redis instance is listening by using the  `setPort()` method.
##### Syntax
```php
$cache -> setPort(int $portNumber);
```
##### Example
```php
$cache -> setPort(5000);
```

##### Timeout
You may set a connection timeout by using the `setTimeout()` method. If a connection could not be established within this time, an Exception will be thrown.
##### Syntax
```php
$cache -> setTimeout(float $seconds);
```
##### Example
```php
$cache -> setTimeout(5.5);
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
The default expiration is 300000 milliseconds (5 minutes). Expiration will always be the amount of milliseconds from the moment of store.

##### Syntax
```php
$cache -> set(mixed $key, mixed $value, [int $milliseconds = 300000]);
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
The `touch()` method can renew the expiration time of a stored data based on a given key. Time is in milliseconds.

##### Syntax
```php
$cache -> touch(mixed $key, [int $milliseconds = 300000]);
```

##### Example 1: Renewing expiration time
```php
$cache -> set('foo', 'bar');
sleep(5);
$cache -> touch('foo'); //Resetting original lifetime
```

##### Example 2: Setting new expiration time
```php
$cache -> set('foo', 'bar');
sleep(5);
$cache -> touch('foo', 4000); //Set new lifetime for 4 seconds
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


#### Retrieve the expiration time
The `getExpiration()` method returns the remaining time to live based on a given key.

##### Syntax
```php
$cache -> getExpiration(mixed $key);
```

##### Example: Retrieve the remaining time to live
```php
$cache -> getExpiration('foo'); //Returns 0
$cache -> set('foo', 'bar', 1000);
$cache -> getExpiration('foo'); //Returns 1000
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

### Example anti-brute force based on remote ip address
```php
function isIpBruteForcing() {

    $cache  = new Redis(); //Create new Redis cache instance
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
- Boolean - True and False.
- Null - The actual null value.
- Arrays - Indexed, associative and multidimensional arrays of arbitrary depth.
- Object - Any object that supports lossless serialization and deserialization such that $o == unserialize(serialize($o)). Objects may leverage PHP's Serializable interface, __sleep() or __wakeup() magic methods.