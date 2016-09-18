<?php
/**
 * CSVelte: Slender, elegant CSV for PHP.
 *
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @version   v0.2.1
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace CSVelte\Traits;

use CSVelte\Exception\IOException;
use CSVelte\Exception\NotYetImplementedException;

/**
 * IO IsSeekable Trait.
 *
 * Seek methods shared between CSVelte\IO classes.
 *
 * @package    CSVelte
 * @subpackage CSVelte\Traits
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 * @since      v0.2
 */
trait IsSeekable
{
    /**
     * Seek to specific line (beginning).
     *
     * Seek to the line specified by $offset, starting from the $whence line.
     *
     * @param int $offset Offset to seek to
     * @param int $whence Position from whence to seek from
     * @param string $eol The line terminator string/char
     * @return boolean True if successful
     * @throws CSVelte\Exception\NotYetImplementedException because it isn't
     *     implemented yet
     */
    public function seekLine($offset, $whence = SEEK_SET, $eol = PHP_EOL)
    {
        throw new NotYetImplementedException("This method not yet implemented.");
    }

    /**
     * Assert that this file/stream object is readable.
     *
     * @return void
     * @throws CSVelte\Exception\IOException if stream isn't readable
     */
    protected function assertIsSeekable()
    {
        if (!$this->isSeekable()) {
            throw new IOException("Stream not seekable: " . $this->getName(), IOException::ERR_NOT_SEEKABLE);
        }
    }

    abstract public function getName();

    abstract public function isSeekable();

    abstract public function seek($offset, $whence);

}
