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
 * Seekable interface.
 *
 * Implement this interface to be "seekable"
 *
 * @since v0.1
 */
interface Seekable
{
    /**
     * Seek to a position within an input.
     *
     * @param int Position to seek to
     *
     * @return bool True if seek was successful
     */
    public function seek($pos);
}
