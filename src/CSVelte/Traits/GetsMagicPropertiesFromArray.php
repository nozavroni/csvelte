<?php
/**
 * CSVelte: Slender, elegant CSV for PHP
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV 
 * standardization efforts, CSVelte was written in an effort to take all the 
 * suck out of working with CSV. 
 *
 * @version   v0.1
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace CSVelte\Traits;

use CSVelte\Utils;

/**
 * Gets Magic Properties From Array Trait
 * Basically just allows you to specify an array within the class that will be
 * used when people try to access non-existent properties.
 *
 * @package   CSVelte\Trait
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @todo      I believe this can be deleted... I'm not using it anywhere
 */
trait GetsMagicPropertiesFromArray
{
    /**
     * @var array When non-existent properties are requested, search this array
     */
    protected $properties = array();

    /**
     * Retrieve property value from properties array or thrown an exception if
     * none exists.
     *
     * @param string Property name
     * @return string Property value
     * @throws \OutOfBoundsException if property name cannot be found in properties
     */
    public function __get($name)
    {
        try {
            return Utils::array_get($this->properties, $name, null, true);
        } catch (\OutOfBoundsException $e) {
            // @todo should we throw a custom exception here?
            throw new \OutOfBoundsException('Unknown property: ' . $name);
        }
    }

    /**
     * Sets property value in properties
     *
     * @param string Property name
     * @param any Property value
     * @return void
     */
    public function __set($name, $value)
    {
        $this->properties[$name] = $value;
    }
}
