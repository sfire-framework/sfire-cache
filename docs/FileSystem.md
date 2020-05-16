# FileSystem Cache Adapter

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
    - [Retrieve the expiration time](#retrieve-the-expiration-time)
    - [Clear all cache data](#clear-all-cache-data)
- [Examples](#examples)
- [Notes](#notes)
    - [Chaining](#chaining)
    - [Data types](#data-types)


## Introduction
FileSystem Cache Adapter is based on storing cache in files locally. The cache is accessed through an easy interface for storing and retrieving temporary data. When you perform an expensive operation, like reading a file or fetch a network resource, you can store the result in the user cache to speed up a later request of the same object. Cache files will be removed based on a [probability](#probability) for performance benefits so that PHP doesn't clear the cache with every request. You may adjust this probability by your needs.    


## Requirements
There are no requirements for this package.


## Installation
Install this package using [Composer](https://getcomposer.org/):
```shell script
composer require sfire-framework/sfire-cache
```


## Setup
### Namespace
```php
use sFire\Cache\Adapter\FileSystem;
```

### Instance
```php
$cache = new FileSystem();
```

### Configuration
Below are the default values that the package uses.

#### Default settings
- Default probability is 5
- Default directory is null
- Default extension is ".cache"

#### Overwriting settings
##### Probability
The FileSystem cache adapter will store cache locally on the file systeem in files. Cache will be removed based on a probability. You may set this probabilityby using the `setProbability()` method. The higher the number, the lower the chance the cache will be cleared. Set to 0 to disable automatic cache clearing Set to 1 to automatic clear the cache on every request.
###### Syntax
```php
$cache -> setProbability(int $probability);
```
###### Example
```php
$cache -> setProbability(3);
```

##### Directory
Use the  `setDirectory()` method to set the directory where all cache files will be saved.
##### Syntax
```php
$cache -> setDirectory(string $directory);
```
##### Example
```php
$cache -> setDirectory('/var/www/data/cache/');
```

##### Extension
Cache is stored as files with a extension. You can set this extension by using the `setExtension()` method.
##### Syntax
```php
$cache -> setExtension(string $extension);
```
##### Example 1: Set the extension with leading dot
```php
$cache -> setExtension('.tmp');
```
##### Example 2: Set the extension without leading dot
You may also remove the leading dot.
```php
$cache -> setExtension('tmp');
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

### Anti-brute force based on remote ip address
```php
function isIpBruteForcing() {

    $cache  = new FileSystem(); //Create new FileSystem cache instance
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