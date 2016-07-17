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
    // protected $quoteChars = '"\'';
    protected $open = false;
    // protected $escapeChar = '\\';
    // protected $escape = false;

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
     */
    public function readLine($max = null, $eol = PHP_EOL, $quoteChar = '"')
    {
        // $this->open = array_fill_keys(str_split($this->quoteChars), false);
        try {
            do {
                if (!isset($lines)) $lines = array();
                array_push($lines, $this->nextLine($max, $eol));
            } while ($this->inQuotedString(end($lines), $quoteChar));
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
    protected function inQuotedString($line, $quoteChar)
    {
        $upshot = function($carry, $item){ return $carry + $item; };
        if (!empty($line)) {
            do {
                if (!isset($i)) $i = 0;
                $c = $line[$i++];
                // if ($this->escape) {
                //     $this->escape = false;
                //     continue;
                // }
                // if ($c == $this->escapeChar) $this->escape = true;
                // if (strpos($this->quoteChars, $c) !== false) {
                if ($c == $quoteChar) {
                    // only open a quoted string if no others are open
                    // make a copy of open array
                    $open = $this->open;
                    // unset current quote character
                    //unset($open[$c]);
                    /*if (!$reduction = array_reduce($open, $upshot))*/ $this->open = !$this->open;
                }
            } while ($i < strlen($line));
        }
        // we're only interested in whether or not the open array contains a true value
        return $this->open; // array_reduce($this->open, $upshot);
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
