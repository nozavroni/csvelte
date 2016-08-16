<?php
/**
 * CSVelte
 * Slender, elegant CSV for PHP5.3+.
 *
 * @version v0.1
 *
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author Luke Visinoni <luke.visinoni@gmail.com>
 * @license See LICENSE file
 */
namespace CSVelte\Contract;

/**
 * Data Type interface.
 *
 * Implement this interface to be a "data type"
 *
 * @since v0.1
 */
interface DataType
{
    /**
     * Test string against regex validation pattern.
     *
     * @param void
     *
     * @return bool
     */
    public function isValid();

    /**
     * Retrieve internal, semantic value of this value.
     *
     * @param void
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Magic method for returning string version of this value.
     *
     * @param void
     *
     * @return string
     */
    public function __toString();

    //protected function fromString($str);
}
