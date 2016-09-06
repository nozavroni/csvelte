<?php
/**
 * CSVelte: Slender, elegant CSV for PHP.
 *
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @version   v0.2
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace CSVelte\Traits;

/**
 * IO ReadLine Trait.
 *
 * Readline method shared between various IO classes.
 *
 * @package    CSVelte
 * @subpackage CSVelte\IO
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 * @since      v0.2
 */
trait ReadLine
{
    /**
     * Read single line.
     * Read the next line from the file (moving the internal pointer down a line).
     * Returns multiple lines if newline character(s) fall within a quoted string.
     *
     * @param string|array A string or array of strings to be used as EOL char/sequence
     * @param int Maximum number of bytes to return (line will be truncated to this -1 if set)
     * @return string A single line read from the file.
     * @throws CSVelte\Exception\IOException
     * @todo Should this add a newline if maxlength is reached?
     * @todo I could actually buffer this by reading x chars at a time and doing
     *     the same thing with looping char by char if this is too IO intensive.
     */
    public function readLine($eol = PHP_EOL, $maxLength = null)
    {
        $size = 0;
        $buffer = false;
        if (!is_array($eol)) $eol = array($eol);
        while (!$this->eof()) {
            // Using a loose equality here to match on '' and false.
            if (null == ($byte = $this->read(1))) {
                return $buffer;
            }
            $buffer .= $byte;
            // Break when a new line is found or the max length - 1 is reached
            if (array_reduce($eol, function($carry, $eol) use ($buffer) {
                if (!$carry) {
                    $eollen = 0 - strlen($eol);
                    return (substr($buffer, $eollen) === $eol);
                }
                return true;
            }, false) || ++$size === $maxLength - 1) {
                break;
            }
        }
        return $buffer;
    }

    abstract public function read($length);

    abstract public function eof();

}
