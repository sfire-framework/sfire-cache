<?php
/**
 * sFire Framework (https://sfire.io)
 *
 * @link      https://github.com/sfire-framework/ for the canonical source repository
 * @copyright Copyright (c) 2014-2020 sFire Framework.
 * @license   http://sfire.io/license BSD 3-CLAUSE LICENSE
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use sFire\Cache\Adapter\Redis;


final class RedisTest extends TestCase {


    /**
     * Contains an instance of Redis
     * @var null|Redis
     */
    private ?Redis $cache = null;


    /**
     * Setup. Created new Redis cache instance
     * @return void
     */
    protected function setUp(): void {
       
        $this -> cache = new Redis();
        $this -> cache -> setPort(6379);
        $this -> cache -> clear();
    }


     /**
     * Test if cache can be stored and retrieved
     * @return void
     */
    public function testIfCacheCanBeSet(): void {

        $this -> cache -> set('key', 'value', 200);
        $this -> assertTrue($this -> cache -> exists('key'));
        $this -> assertEquals('value', $this -> cache -> get('key'));
        
        $this -> cache -> set('key', ['foo' => 'bar'], 200);
        $this -> assertTrue($this -> cache -> exists('key'));
        $this -> assertEquals(['foo' => 'bar'], $this -> cache -> get('key'));

        usleep(200000);

        $this -> assertFalse($this -> cache -> exists('key'));
        $this -> cache -> clear();
    }


    /**
     * Test if all data can be flushed/removed
     * @return void
     */
    public function testIfAllKeysCanBeFlushed(): void {

        $this -> cache -> set('key', 'value', 200);
        $this -> assertTrue($this -> cache -> exists('key'));
        $this -> cache -> clear();
        $this -> assertFalse($this -> cache -> exists('key'));
    }


    /**
     * Test if cache can be manual expired
     * @return void
     */
    public function testExpireCacheFiles(): void {

        $this -> cache -> set('key', 'value', 5000);
        $this -> assertTrue($this -> cache -> exists('key'));
        $this -> cache -> expire('key');
        $this -> assertFalse($this -> cache -> exists('key'));
        $this -> cache -> clear();
    }


    /**
     * Test if cache files that are expired can be manualy removed
     * @return void
     */
    public function testIfExistingCacheCanBeTouchedAgain(): void {

        $this -> cache -> set('key', 'value1', 3000);
        $this -> assertTrue($this -> cache -> exists('key'));
        
        usleep(1000);
        
        $this -> cache -> touch('key', 3000);
        $this -> assertEquals(3000, $this -> cache -> getExpiration('key'));
        $this -> assertTrue($this -> cache -> exists('key'));
        $this -> cache -> clear();
    }
}