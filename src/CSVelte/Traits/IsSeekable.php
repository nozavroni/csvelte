<?php
/**
 * CSVelte: Slender, elegant CSV for PHP
 *
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @copyright Copyright (c) 2018 Luke Visinoni
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   See LICENSE file (MIT license)
 */
namespace CSVelte\Traits;

use CSVelte\Exception\IOException;
use CSVelte\Exception\NotYetImplementedException;

/**
 * IO IsSeekable Trait.
 *
 * Seek methods shared between CSVelte\IO classes.
 */
trait IsSeekable
{
    /**
     * Seek to specific line (beginning).
     *
     * Seek to the line specified by $offset, starting from the $whence line.
     *
     * @param int    $offset Offset to seek to
     * @param int    $whence Position from whence to seek from
     * @param string $eol    The line terminator string/char
     *
     * @throws NotYetImplementedException
     *
     * @return bool True if successful
     */
    public function seekLine($offset, $whence = SEEK_SET, $eol = PHP_EOL)
    {
        throw new NotYetImplementedException(sprintf(
            'This method not yet implemented.',
            $offset,
            $whence,
            $eol // these are simply here to satisfy my code analysis tools
        ));
    }

    abstract public function isSeekable();

    abstract public function seek($offset, $whence = SEEK_SET);

    /**
     * Assert that this file/stream object is readable.
     *
     * @throws IOException
     */
    protected function assertIsSeekable()
    {
        if (!$this->isSeekable()) {
            throw new IOException('Stream not seekable', IOException::ERR_NOT_SEEKABLE);
        }
    }
}
