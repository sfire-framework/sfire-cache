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
use sFire\Cache\Adapter\Apcu;


final class ApcuTest extends TestCase {


    /**
     * Contains an instance of Apcu
     * @var Apcu
     */
    private Apcu $cache;


    /**
     * Setup. Created new Apcu cache instance
     * @return void
     */
    protected function setUp(): void {
        $this -> cache = new Apcu();
    }


    /**
     * Test if cache can be stored and retrieved
     * @return void
     */
    public function testIfCacheCanBeSet(): void {

        $this -> cache -> set('key', 'value', 1);
        $this -> assertTrue($this -> cache -> exists('key'));
        $this -> assertEquals($this -> cache -> get('key'), 'value');
    }
}