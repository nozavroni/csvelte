<?php namespace CSVelte\Contract;
/**
 * Seekable interface
 * Implement this interface to be "seekable"
 *
 * @package   CSVelte\Contract
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
interface Seekable
{
    /**
     * Seek to a position within an input
     *
     * @param integer Position to seek to
     * @return boolean True if seek was successful
     * @access public
     */
    public function seek($pos);
}
