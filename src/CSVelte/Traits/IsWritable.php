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

/**
 * IO IsWritable Trait.
 *
 * Write methods shared between CSVelte\IO classes.
 *
 * @package    CSVelte
 * @subpackage CSVelte\Traits
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 * @since      v0.2
 */
trait IsWritable
{
    /**
     * Write single line to file/stream
     *
     * Writes a line to the file/stream (if it is writable)
     *
     * @param string $line The line to be written to the stream
     * @param string $eol The end of line string
     * @return int The number of bytes written to the stream
     * @throws CSVelte\Exception\IOException
     */
    public function writeLine($line, $eol = PHP_EOL)
    {
        return $this->write($line . $eol);
    }

    /**
     * Assert that this file/stream object is readable.
     *
     * @return void
     * @throws CSVelte\Exception\IOException if stream isn't readable
     */
    protected function assertIsWritable()
    {
        if (!$this->isWritable()) {
            throw new IOException("Stream not writable: " . $this->getName(), IOException::ERR_NOT_WRITABLE);
        }
    }

    abstract public function getName();

    abstract public function isWritable();

    abstract public function write($str);

}
