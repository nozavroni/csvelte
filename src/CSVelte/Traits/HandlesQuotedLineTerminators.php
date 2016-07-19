<?php namespace CSVelte\Traits;

use CSVelte\Exception\EndOfFileException;

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
    /**
     * @var bool True if current line ended while inside a quoted string
     */
    protected $open = false;

    /**
     * @var bool True if last character read was the escape character
     */
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
                    $this->escape = false;
                    continue;
                }
                $this->escape = ($c == $escapeChar);
                if ($c == $quoteChar) $this->open = !$this->open;
            } while ($i < strlen($line));
        }
        return $this->open;
    }

    /**
     * Read next line from CSV file (delegate to class)
     * Because this trait overrides the readLine method of the Readable interface,
     * it has to require this method in its place. That way it can still delegate
     * the reading of data to the actual class and only concern itself with the
     * task at hand (quoted newlines)
     *
     * @abstract
     * @access protected
     * @param int
     * @param char
     * @return string The next line of text from the input source
     * @throws CSVelte\Exception\EndOfFileException
     */
    abstract protected function nextLine($max = null, $eol = PHP_EOL);
}
