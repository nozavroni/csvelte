<?php namespace CSVelte\Traits;

use CSVelte\Utils;
use CSVelte\Exception\EndOfFileException;
use CSVelte\Exception\OutOfBoundsException;

/**
 * Handle line terminators that fall within a quoted string
 * When line terminators fall within a quoted string, they should be fead into
 * the column just like any other character, rather than starting a new row of
 * CSV data. In order for a reader to be able to handle newlines in this way, it
 * must implement this trait.
 *
 * @package   CSVelte
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @note      Add this trait to a CSVelte\Input class to be able to read mult-line
 *            CSV records properly.
 */
trait HandlesQuotedLineTerminators
{
    protected $open = false;
    protected $escape = false;

    /**
     * Read single line from CSV data source (stream, file, etc.), taking into
     * account CSV's de-facto quoting rules with respect to designated line
     * terminator character when they fall within quoted strings.
     *
     * @param int
     * @param char
     * @return string
     * @access public
     * @see README file for more about CSV de-facto standard
     * @todo This should probably just accept a Flavor as its only argument
     */
    public function readLine($max = null, $eol = PHP_EOL, $quoteChar = '"', $escapeChar = '\\')
    {
        // $this->open = array_fill_keys(str_split($this->quoteChars), false);
        try {
            do {
                if (!isset($lines)) $lines = array();
                array_push($lines, $this->nextLine($max, $eol));
            } while ($this->inQuotedString(end($lines), $quoteChar, $escapeChar));
        } catch (EndOfFileException $e) {
            // only throw the exception if we don't already have lines in the buffer
            if (!count($lines)) throw $e;
        }
        return rtrim(implode($eol, $lines), $eol);
    }

    /**
     * Determine whether last line ended while a quoted string was still "open"
     *
     * @param string Line of csv to analyze
     * @return bool
     * @access protected
     */
    protected function inQuotedString($line, $quoteChar, $escapeChar)
    {
        if (!empty($line)) {
            do {
                if (!isset($i)) $i = 0;
                $c = $line[$i++];
                if ($this->escape) {
                    continue;
                }
                $this->escape = ($c == $escapeChar);
                if ($c == $quoteChar) $this->open = !$this->open;
            } while ($i < strlen($line));
        }
        return $this->open;
    }

    /**
     * Read next line from CSV file
     */
    abstract protected function nextLine($max = null, $eol = PHP_EOL);

    // protected function nextLine($max = null, $eol = "\n")
    // {
    //     if (false === ($line = stream_get_line($this->source, $max ?: self::MAX_LINE_LENGTH, $eol))) {
    //         if ($this->isEof()) {
    //             throw new EndOfFileException('Cannot read line from ' . $this->name() . '. End of file has been reached.');
    //         } else {
    //             // @todo not sure if this is necessary... may cause bugs/unpredictable behavior even...
    //             throw new \OutOfBoundsException('Cannot read line from ' . $this->name());
    //         }
    //     }
    //     return $line;
    // }
}
