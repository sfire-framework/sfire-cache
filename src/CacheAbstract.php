<?php
/**
 * sFire Framework (https://sfire.io)
 *
 * @link      https://github.com/sfire-framework/ for the canonical source repository
 * @copyright Copyright (c) 2014-2020 sFire Framework.
 * @license   http://sfire.io/license BSD 3-CLAUSE LICENSE
 */

declare(strict_types=1);

namespace sFire\Cache;


/**
 * Class CacheAbstract
 * @package sFire\Cache
 */
abstract class CacheAbstract {


    /**
     * Set new cache by key name
     * @param mixed $key The unique name of the cache
     * @param mixed $value The value of the cache
     * @param int $expiration Time
     * @return self
     */
    abstract public function set($key, $value, int $expiration = null);


    /**
     * Returns the cache if available, otherwise returns the default parameter
     * @param mixed $key The unique name of the cache
     * @param mixed $default Value returned when cache could not be found
     * @return mixed
     */
    abstract public function get($key, $default = null);


    /**
     * Expire cache based on key
     * @param mixed $key The unique name of the cache
     * @return self
     */
    abstract public function expire($key);


    /**
     * Clear all cache files
     * @return self
     */
    abstract public function clear();


    /**
     * Returns if a cache file exists based on key
     * @param mixed $key The unique name of the cache
     * @return bool
     */
    abstract public function exists($key): bool;
}