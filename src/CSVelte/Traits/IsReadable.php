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

/**
 * IO IsReadable Trait.
 *
 * Read methods shared between CSVelte\IO classes.
 */
trait IsReadable
{
    /**
     * Read single line.
     * Read the next line from the file (moving the internal pointer down a line).
     * Returns multiple lines if newline character(s) fall within a quoted string.
     *
     * @param string|array $eol       A string or array of strings to be used as EOL char/sequence
     * @param int          $maxLength Maximum number of bytes to return (line will be truncated to this -1 if set)
     *
     * @throws IOException
     *
     * @return string A single line read from the file.
     *
     * @todo Should this add a newline if maxlength is reached?
     * @todo I could actually buffer this by reading x chars at a time and doing
     *     the same thing with looping char by char if this is too IO intensive.
     */
    public function readLine($eol = PHP_EOL, $maxLength = null)
    {
        $size                     = 0;
        $buffer                   = false;
        if (!is_array($eol)) {
            $eol = [$eol];
        }
        while (!$this->eof()) {
            // Using a loose equality here to match on '' and false.
            if (null == ($byte = $this->read(1))) {
                return $buffer;
            }
            $buffer .= $byte;
            // Break when a new line is found or the max length - 1 is reached
            if (array_reduce($eol, function ($carry, $eol) use ($buffer) {
                    if (!$carry) {
                        $eollen = 0 - strlen($eol);

                        return substr($buffer, $eollen) === $eol;
                    }

                    return true;
                }, false) || ++$size === $maxLength - 1) {
                break;
            }
        }

        return $buffer;
    }

    abstract public function isReadable();

    abstract public function read($length);

    abstract public function eof();

    /**
     * Assert that this file/stream object is readable.
     *
     * @throws IOException
     */
    protected function assertIsReadable()
    {
        if (!$this->isReadable()) {
            throw new IOException('Stream not readable', IOException::ERR_NOT_READABLE);
        }
    }
}
