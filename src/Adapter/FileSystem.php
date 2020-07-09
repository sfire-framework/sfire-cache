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
use sFire\Cache\Expiration;
use sFire\FileControl\File;


/**
 * Class FileSystem
 * @package sFire\Cache
 */
class FileSystem extends CacheAbstract {


    /**
     * Contains the probability that all expired cache files will be cleared
     * The higher the number, the lower the chance the cache will be cleared
     * @var int
     */
    private int $probability = 5;


    /**
     * Contains the path of the cache directory
     * @var string
     */
    private ?string $directory = null;


    /**
     * Contains the file extension for the cache file
     * @var string
     */
    private string $extension = '.cache';


    /**
     * Sets the probability
     * @param int $probability The higher the number, the lower the chance the cache will be cleared. Set to 0 to disable automatic cache clearing.
     * @return self
     */
    public function setProbability(int $probability): self {

        $this -> probability = $probability;
        return $this;
    }


    /**
     * Sets the cache file extension
     * @param string $extension The extension with or without leading dot.
     * @return self
     */
    public function setExtension(string $extension): self {

        $this -> extension = '.' . ltrim($extension, '.');
        return $this;
    }


    /**
     * Sets the directory where all cache files will be saved
     * @param string $directory The path of the cache directory
     * @return self
     * @throws RuntimeException
     */
    public function setDirectory(string $directory): self {

        if(false === is_writable($directory)) {
            throw new RuntimeException(sprintf('Cache folder "%s" is not writable', $directory));
        }

        if(false === is_readable($directory)) {
            throw new RuntimeException(sprintf('Cache folder "%s" is not readable', $directory));
        }

        $this -> directory = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        return $this;
    }


    /**
     * Saves new data into the cache
     * @param mixed $key The unique name of the cache data
     * @param mixed $value The value of the cache
     * @param int $expiration [optional] Time in milliseconds (default 5 minutes)
     * @return self
     */
    public function set($key, $value, ?int $expiration = 300000): self {

        $this -> garbage();

        $files = glob($this -> directory . $this -> generateName($key));

        if(true === is_array($files)) {

            foreach($files as $file) {

                $file = new File($file);
                $file -> delete();
            }
        }

        $cache = new File($this -> directory . $this -> generateName($key, $expiration));
        $cache -> create();
        $cache -> append(serialize($value));

        return $this;
    }


    /**
     * Returns the cache data if available, otherwise returns the default parameter
     * @param mixed $key The unique name of the cache
     * @param mixed $default [optional] Value returned when cache could not be found
     * @return mixed
     */
    public function get($key, $default = null) {

        $this -> garbage();

        $files = glob($this -> directory . $this -> generateName($key));

        if(count($files) > 0 && true === is_array($files)) {

            $file 		= new File($files[0]);
            $expiration = $this -> extractExpiration($file);

            //Check if expiration date is not overdue
            if($expiration -> getTime() >= microtime(true)) {
                return unserialize($file -> getContent() ?? '');
            }

            $file -> delete();
        }

        return $default;
    }


    /**
     * Returns the expiration time and the time when the cache file was created
     * @param mixed $key The unique name of the cache data
     * @return null|Expiration
     */
    public function getExpiration($key): ?Expiration {

        $files = glob($this -> directory . $this -> generateName($key));

        if(count($files) > 0 && true === is_array($files)) {

            $file 		= new File($files[0]);
            $expiration = $this -> extractExpiration($file);

            if($expiration -> getTime() >= microtime(true)) {
                return $expiration;
            }

            $file -> delete();
        }

        return null;
    }


    /**
     * Expires cache data based on key
     * @param mixed $key The unique name of the cache
     * @return self
     */
    public function expire($key): self {

        $files = glob($this -> directory . $this -> generateName($key));

        if(count($files) > 0 && true === is_array($files)) {

            $file = new File($files[0]);
            $file -> delete();
        }

        return $this;
    }


    /**
     * Clears all cache data
     * @return self
     */
    public function clear(): self {

        $files = glob($this -> directory . '*');

        if(true === is_array($files)) {

            foreach($files as $file) {

                $file = new File($file);
                $file -> delete();
            }
        }

        return $this;
    }


    /**
     * Clears all expired cache
     * @return self
     */
    public function clearExpired(): self {

        $files = glob($this -> directory . '*');

        if(true === is_array($files)) {

            foreach($files as $file) {

                $file 		= new File($file);
                $expiration = $this -> extractExpiration($file);

                if($expiration -> getTime() <= microtime(true)) {
                    $file -> delete();
                }
            }
        }

        return $this;
    }


    /**
     * Resets lifetime of a cached file
     * @param mixed $key The unique name of the cache
     * @param int $expiration [optional] Time in milliseconds
     * @return self
     */
    public function touch($key, int $expiration = null): self {

        $files = glob($this -> directory . $this -> generateName($key));

        if(true === is_array($files) && count($files) > 0) {

            $file = new File($files[0]);

            if(null === $expiration) {

                $expiration = $this -> extractExpiration($file);
                $expiration = $expiration -> getExpiration();
            }

            $file -> rename($this -> generateName($key, $expiration));
        }

        return $this;
    }


    /**
     * Returns if cache data exists based on a given key
     * @param mixed $key The unique name of the cache
     * @return bool
     */
    public function exists($key): bool {

        $files = glob($this -> directory . $this -> generateName($key));

        if(true === is_array($files) && count($files) > 0) {

            $file 		= new File($files[0]);
            $expiration = $this -> extractExpiration($file);

            if($expiration -> getTime() >= microtime(true)) {
                return true;
            }

            $file -> delete();
        }

        return false;
    }


    /**
     * Generates the cache file name
     * @param mixed $key The unique name of the cache
     * @param int $expiration [optional] Time in milliseconds
     * @return string
     */
    private function generateName($key, ?int $expiration = null): string {
        return md5(serialize($key)) . '-' . ($expiration ? (string) $expiration : '*') . '-' . ($expiration ? (microtime(true) + ($expiration / 1000)) : '*') . $this -> extension;
    }


    /**
     * Returns the expiration time of cache file
     * @param File $file The path of the cache file
     * @return Expiration
     */
    private function extractExpiration(File $file): Expiration {

        $expiration = new Expiration();
        $time       = explode('-', $file -> getName());

        if(count($time) > 2) {

            $expiration -> setTime((float) $time[2]);
            $expiration -> setExpiration((int) $time[1]);
        }

        return $expiration;
    }


    /**
     * Clears all expired cache files based on a probability
     * @return void
     */
    private function garbage(): void {

        if(0 === $this -> probability) {
            return;
        }

        if(1 === mt_rand(1, $this -> probability)) {
            $this -> clearExpired();
        }
    }
}