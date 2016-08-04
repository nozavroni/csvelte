<?php
/**
 * CSVelte
 * Slender, elegant CSV for PHP5.3+
 *
 * @version v0.1
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author Luke Visinoni <luke.visinoni@gmail.com>
 * @license See LICENSE file
 */
namespace CSVelte\Contract;
/**
 * Data Type interface
 *
 * Implement this interface to be a "data type"
 *
 * @package CSVelte
 * @subpackage Contract (Interfaces)
 * @since v0.1
 */
interface DataType
{
    /**
     * Test string against regex validation pattern
     *
     * @param void
     * @return boolean
     * @access public
     */
    public function isValid();

    /**
     * Retrieve internal, semantic value of this value
     *
     * @param void
     * @return mixed
     * @access public
     */
    public function getValue();

    /**
     * Magic method for returning string version of this value
     *
     * @param void
     * @return string
     * @access public
     */
    public function __toString();

    //protected function fromString($str);
}
