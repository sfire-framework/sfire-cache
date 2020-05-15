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
use sFire\Cache\Exception\RuntimeException;


/**
 * Class Redis
 * @package sFire\Cache
 */
class Redis extends CacheAbstract {
	

	/**
	 * Contains the hostname/ip of the Redis server to connect to
	 * @var string
	 */
	private string $host = '127.0.0.1';


	/**
	 * Contains the Redis port to connect to
	 * @var null|int
	 */
	private ?int $port = 6379;


	/**
	 * Contains the Redis connection
	 * @var null|resource
	 */
	private $connection = null;


	/**
	 * Contains the authentication password if Redis requires it
	 * @var string
	 */
	private ?string $password = null;


	/**
	 * Contains the default timeout in seconds
	 * @var float
	 */
	private ?float $timeout = 2.5;


	/**
	 * Cleans up Redis connection
	 */
	public function __destruct() {

		if(null !== $this -> connection && false === is_bool($this -> connection)) {
			fclose($this -> connection);
		}
	}


	/**
	 * Sets the hostname / ip of the Redis server
	 * @param string $host The hostname or IP address to connect to
	 * @return self
	 */
	public function setHost(string $host): self {
		
		$this -> host = $host;
		return $this;
	}


	/**
	 * Sets the port of the Redis server
	 * @param int $portNumber The port of the Redis server
	 * @return self
	 */
	public function setPort(?int $portNumber): self {
		
		$this -> port = $portNumber;
		return $this;
	}


	/**
	 * Sets authentication password of the Redis server
	 * @param string $password The authentication password for the Redis server 
	 * @return self
	 */
	public function setPassword(string $password): self {
		
		$this -> password = $password;
		return $this;
	}


	/**
	 * Set the timeout in seconds before the stream will get a timeout
	 * @param float $seconds The number of seconds before the stream will get a timeout
	 * @return self
	 */
	public function setTimeout(float $seconds): self {
		
		$this -> timeout = $seconds;
		return $this;
	}


    /**
     * Sets new cache data by key name
     * @param mixed $key The unique name of the cache
     * @param mixed $value The value of the cache
     * @param int $milliseconds [optional] Time in milliseconds (default 5 minutes)
     * @return self
     */
	public function set($key, $value, ?int $milliseconds = 300000): self {

		$this -> command('set', [$key, $value]);
		
		if(null !== $milliseconds) {
			$this -> touch($key, $milliseconds);
		}

		return $this;
	}


    /**
     * Returns the cache data if available, otherwise returns the default parameter
     * @param mixed $key The unique name of the cache
     * @param mixed $default [optional] Value returned when cache could not be found
     * @return mixed
     */
	public function get($key, $default = null) {

		$response = $this -> command('get', [$key]);
		return $response ?? $default;
	}


    /**
     * Returns the expiration time
     * @param mixed $key The unique name of the cache
     * @return int
     */
	public function getExpiration($key): int {

		$response = $this -> command('pttl', [$key]);
		return (int) $response;
	}


    /**
     * Expires cache data based on a given key
     * @param mixed $key The unique name of the cache
     * @return self
     */
	public function expire($key): self {

		$this -> command('pexpire', [$key, -1]);
		return $this;
	}


    /**
     * Clears all cache data
     * @return self
     */
	public function clear(): self {

		$this -> command('flushall');
		return $this;
	}


    /**
     * Resets lifetime of a cached entry
     * @param mixed $key The unique name of the cache
     * @param int $milliseconds [optional] Time in milliseconds
     * @return self
     */
	public function touch($key, ?int $milliseconds = 300000): self {

		$this -> command('pexpire', [$key, $milliseconds]);
		return $this;
	}


    /**
     * Returns if cache entry exists based on a given key
     * @param mixed $key The unique name of the cache
     * @return bool
     */
	public function exists($key): bool {
		return (bool) $this -> command('exists', [$key]);
	}


	/**
	 * Connects to a new stream to the Redis server
	 * @return void
     * @throws RuntimeException
	 */
	private function connect(): void {

		$remote = null === $this -> port ? 'unix://' . $this -> host : 'tcp://' . $this -> host . ':' . $this -> port;
        $this -> connection = @stream_socket_client($remote, $errorNumber, $errorString, $this -> timeout, STREAM_CLIENT_CONNECT);

        if(0 !== $errorNumber) {
        	throw new RuntimeException(sprintf('Could not connect with Redis server. Error number: %s with message "%s"', $errorNumber, $errorString));
        }

        if(null !== $this -> password) {
        	$this -> command('auth', [$this -> password]);
        }
	}


    /**
     * Builds a valid Redis call, executes it and returns the reply from the connection stream
     * @param string $method Redis command name
     * @param array $args Redis command arguments
     * @return mixed
     */
	private function command(string $method, array $args = []) {

		//Check if connection is created
		if(null === $this -> connection) {
			$this -> connect();
		}

		//Build command
		$command   = [];
		$command[] = '*' . (count($args) + 1);
		$command[] = '$' . strlen($method);
		$command[] = strtoupper($method);

		foreach($args as $arg) {

			if(true === is_array($arg)) {
				$arg = json_encode($arg, JSON_INVALID_UTF8_IGNORE);
			}

			$command[] = '$' . strlen($arg);
			$command[] = $arg;
		}

		$command = implode("\r\n", $command) . "\r\n";

		//Send command
		fwrite($this -> connection, $command);

		//Read and return the output
		return $this -> read();
	}


	/**
	 * Reads the reply from the Redis connection stream and returns it
	 * @return mixed
     * @throws RuntimeException
	 */
	private function read() {

		$reply  = fgets($this -> connection);
		$status = substr($reply, 0, 1);
		$reply  = trim(substr($reply, 1));
		
		$response = null;

		switch($status) {
			
			//Error			
			case '-': throw new RuntimeException($reply); break;

			//Single line	
			case '+': $response = 'OK' === $reply ? true : $reply; break;

			//Integer
			case ':': $response = (int) $reply; break;

			//Bulk
			case '$': $response = $this -> bulk((int) $reply); break;
			
			case '*':

				$response = [];

				for($i = 0; $i < $reply; $i++) { 
					$response[$i] = $this -> read();
				}

			 break;
			
			//Something is wrong
			default: throw new RuntimeException('Unexpected response'); break;
		}

		if(true === is_string($response)) {

			$resp = json_decode($response, true);

			if(json_last_error() === JSON_ERROR_NONE) {
				return $resp;
			}
		}

		return $response;
	}


	/**
	 * Reads a bulk reply ($)
	 * @param int $size Size of the reply
	 * @return mixed
	 */
	private function bulk(int $size) {

		if($size === -1) {
			return null;
		} 

		$data = '';
		$read = 0;
		
		while($read < $size) {
			
			if(($chunk = ($size - $read)) > 8192) {
				$chunk = 8192;
			}

			$data .= fread($this -> connection, $chunk);
			$read += $chunk;
		}

		fread($this -> connection, 2);
		return $data;
	}	
}