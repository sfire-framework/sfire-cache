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
use sFire\Cache\Adapter\FileSystem;


final class FileSystemTest extends TestCase {


    /**
     * Holds an instance of FileSystem
     * @var FileSystem
     */
    private FileSystem $cache;


    /**
     * Holds the directory path
     * @var string
     */
    private string $directory = '.'. DIRECTORY_SEPARATOR .'test'. DIRECTORY_SEPARATOR .'cache' . DIRECTORY_SEPARATOR;


    /**
     * Setup. Created new FileSystem cache instance
     * @return void
     */
    protected function setUp(): void {
       
        $this -> cache = new FileSystem();
        $this -> cache -> setDirectory($this -> directory);
        $this -> cache -> clear();
    }


     /**
     * Test if cache can be stored and retrieved
     * @return void
     */
    public function testIfCacheCanBeSet(): void {

        $this -> cache -> set('key', 'value', 200);
        $this -> assertTrue($this -> cache -> exists('key'));
        $this -> assertEquals($this -> cache -> get('key'), 'value');
        
        usleep(200000);

        $this -> assertFalse($this -> cache -> exists('key'));
        $this -> cache -> clear();
    }


    /**
     * Test if cache extension can be manualy set
     * @return void
     */
    public function testIfFileExtensionIsWorking(): void {

        $this -> cache -> setExtension('.ch');
        $this -> cache -> set('key', 'value', 1);

        $files = glob($this -> directory . '*.ch');
        $this -> assertTrue(count($files) > 0);
        $this -> cache -> clear();
    }


    /**
     * Test if cache can be manual expired
     * @return void
     */
    public function testExpireCacheFiles(): void {

        $this -> cache -> set('key', 'value', 5);
        $this -> assertTrue($this -> cache -> exists('key'));
        $this -> cache -> expire('key');
        $this -> assertFalse($this -> cache -> exists('key'));
        $this -> cache -> clear();
    }


    /**
     * Test if cache directory can be cleared
     * @return void
     */
    public function testClearDirectory(): void {

        $this -> cache -> setExtension('.ch');

        $this -> cache -> set('key1', 'value1', 1000);
        $this -> cache -> set('key2', 'value2', 1000);
        $this -> cache -> set('key3', 'value3', 1000);
        $this -> cache -> set('key4', 'value4', 1000);
        $this -> cache -> set('key5', 'value5', 1000);

        $this -> assertTrue($this -> cache -> exists('key1'));
        $this -> assertTrue($this -> cache -> exists('key2'));
        $this -> assertTrue($this -> cache -> exists('key3'));
        $this -> assertTrue($this -> cache -> exists('key4'));
        $this -> assertTrue($this -> cache -> exists('key5'));
        
        $this -> cache -> clear();

        $files = glob($this -> directory . '*.ch');
        $this -> assertCount(0, $files);
        $this -> cache -> clear();
    }


    /**
     * Test if cache files that are expired can be manualy removed
     * @return void
     */
    public function testIfExpiredCacheCanBeManualyRemoved(): void {

        $this -> cache -> setExtension('.ch');

        $this -> cache -> set('key1', 'value1', 5000);
        $this -> cache -> set('key2', 'value2', 5000);
        $this -> cache -> set('key3', 'value3', 300);
        $this -> cache -> set('key4', 'value4', 300);
        $this -> cache -> set('key5', 'value5', 300);

        $this -> assertTrue($this -> cache -> exists('key1'));
        $this -> assertTrue($this -> cache -> exists('key2'));
        $this -> assertTrue($this -> cache -> exists('key3'));
        $this -> assertTrue($this -> cache -> exists('key4'));
        $this -> assertTrue($this -> cache -> exists('key5'));

        usleep(400000);

        $this -> cache -> clearExpired();

        $files = glob($this -> directory . '*.ch');
        $this -> assertCount(2, $files);
        $this -> cache -> clear();
    }


    /**
     * Test if cache files that are expired can be manualy removed
     * @return void
     */
    public function testIfExistingCacheCanBeTouchedAgain(): void {

        $this -> cache -> set('key', 'value1', 300);
        $this -> assertTrue($this -> cache -> exists('key'));
        
        usleep(200000);
        
        $this -> cache -> touch('key');

        usleep(200000);

        $this -> assertTrue($this -> cache -> exists('key'));
        $this -> cache -> clear();
    }


    /**
     * Test if expiration times can be retrieved
     * @return void
     */
    public function testRetrievingExpirationTimes(): void {

        $this -> cache -> set('key', 'value1', 300);
        $expiration = $this -> cache -> getExpiration('key');

        $this -> assertIsFloat($expiration -> getTime());
        $this -> assertIsInt($expiration -> getExpiration());
        $this -> assertEquals($expiration -> getExpiration(), 300);
        $this -> assertTrue($expiration -> getTime() > microtime(true));
    }
}