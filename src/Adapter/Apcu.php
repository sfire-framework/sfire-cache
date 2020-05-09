<?php
/**
 * sFire Framework (https://sfire.io)
 *
 * @link      https://github.com/sfire-framework/ for the canonical source repository
 * @copyright Copyright (c) 2014-2020 sFire Framework.
 * @license   http://sfire.io/license BSD 3-CLAUSE LICENSE
 */

declare(strict_types=1);

namespace sFire\Cache\Adapter;

use sFire\Cache\CacheAbstract;
use sFire\Cache\Exception\InvalidArgumentException;
use sFire\Cache\Exception\BadFunctionCallException;


/**
 * Class Apcu
 * @package sFire\Cache
 */
class Apcu extends CacheAbstract {


	/**
	 * Constructor
     * @throws BadFunctionCallException
	 */
	public function __construct() {

		if(false === extension_loaded('apcu')) {
			throw new BadFunctionCallException('Can not use APCu. APCu is not installed.');
		}

		if('cli' === php_sapi_name() && '1' !== ini_get('apc.enable_cli')) {
            throw new BadFunctionCallException('Can not use APCu. APCu is not enabled in CLI. Enable this by setting "apc.enable_cli=on" in apcu.ini');
        }
	}


    /**
     * Saves new data into the cache
     * @param mixed $key The unique name of the cache data
     * @param mixed $value The value of the cache
     * @param int $expiration [optional] Time in seconds (default 5 minutes)
     * @return self
     */
	public function set($key, $value, ?int $expiration = 300): self {

        //Validate the key
        $this -> validateKey($key);
   		
		$this -> expire($key);
		apcu_add($key, $value, $expiration);

		return $this;
	}


    /**
     * Returns the cache data if available, otherwise returns the given default parameter
     * @param mixed $key The unique name of the cache
     * @param mixed $default [optional] Value returned when cache could not be found
     * @return mixed
     */
	public function get($key, $default = null) {

        //Validate the key
        $this -> validateKey($key);

		$result = apcu_fetch($key, $success);

		if(true === $success) {
			return $result;
		}

		return $default;
	}


    /**
     * Expires cache data based on key
     * @param mixed $key The unique name of the cache
     * @return self
     */
	public function expire($key): self {

        //Validate the key
        $this -> validateKey($key);

		apcu_delete($key);
		return $this;
	}


    /**
     * Clears all cache data
     * @return self
     */
	public function clear(): self {

		apcu_clear_cache();
		return $this;
	}


    /**
     * Resets lifetime of a cached file
     * @param mixed $key The unique name of the cache
     * @return self
     */
	public function touch($key): self {

	    //Validate the key
        $this -> validateKey($key);

		if(true === $this -> exists($key)) {

			$info = apcu_cache_info();

			if(true === is_array($info) && true === isset($info['cache_list'])) {

				foreach($info['cache_list'] as $index) {

					if(true === isset($index['info'], $index['ttl']) && $index['info'] === $key) {

						$data 		= $this -> get($key);
						$expiration = $index['ttl'];

						$this -> expire($key);
						$this -> set($key, $data, $expiration);
						break;
					}
				}
			}
		}

		return $this;
	}


	/**
	 * Returns if cache data exists based on a given key
	 * @param string|array $key The unique name of the cache
	 * @return bool
	 */
	public function exists($key): bool {

        //Validate the key
		$this -> validateKey($key);
		return apcu_exists($key);
	}


    /**
     * Validates a given key
     * @param string|array $key The unique name of the cache
     * @throws InvalidArgumentException
     */
	private function validateKey($key): void {

        if(false === is_string($key) && false === is_array($key)) {
            throw new InvalidArgumentException(sprintf('Argument 1 passed to %s() must be of the type string or array, "%s" given', __METHOD__, gettype($key)));
        }

        if(true === is_string($key) && 0 === strlen($key)) {
            throw new InvalidArgumentException(sprintf('Argument 1 passed to %s() may not be an empty string', __METHOD__));
        }

        if(true === is_array($key) && 0 === count($key)) {
            throw new InvalidArgumentException(sprintf('Argument 1 passed to %s() may not be an empty array', __METHOD__));
        }
    }
}