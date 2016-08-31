<?php
/**
 * CSVelte
 * Slender, elegant CSV for PHP
 *
 * @version v0.2
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author Luke Visinoni <luke.visinoni@gmail.com>
 * @license See LICENSE file
 */
namespace CSVelte\Contract;
/**
 * Writable Interface
 *
 * Implement this interface to be writable by a CSVelte\Writer object
 *
 * @package CSVelte
 * @subpackage Contract (Interfaces)
 * @since v0.1
 */
interface Writable
{
    /**
     * Write data to the output
     *
     * @param string The data to write
     * @return int The number of bytes written
     * @access public
     */
    public function fwrite($data);
}
