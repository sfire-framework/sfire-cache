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
 * Class Expiration
 * @package sFire\Cache
 */
class Expiration {


    /**
     * Contains the time. Default is 0.
     * @var float
     */
    private float $time = 0;


    /**
     * Contains the expiration. Default is 0.
     * @var int
     */
    private int $expiration = 0;


    /**
     * Sets the expiration
     * @param int $expiration
     * @return void
     */
    public function setExpiration(int $expiration): void {
        $this -> expiration = $expiration;
    }


    /**
     * Returns the expiration
     * @return int
     */
    public function getExpiration(): int {
        return $this -> expiration;
    }


    /**
     * Sets the time
     * @param float $time
     * @return void
     */
    public function setTime(float $time): void {
        $this -> time = $time;
    }


    /**
     * Returns the time
     * @return float
     */
    public function getTime(): float {
        return $this -> time;
    }
}
