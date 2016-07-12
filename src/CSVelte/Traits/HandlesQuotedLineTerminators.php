<?php namespace CSVelte\Traits;

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
    protected $escapeChar = '\\';
    protected $quoteChars = '"\'';
    protected $open = array();

    public function readLine($max = null, $eol = "\n")
    {
        $this->open = array_fill_keys(str_split($this->quoteChars), false);
        do {
            if (!isset($lines)) $lines = array();
            array_push($lines, $this->nextLine($max, $eol));
        } while ($this->inQuotedString(end($lines)));
        return rtrim(implode($eol, $lines), $eol);
    }

    protected function inQuotedString($line)
    {
        if (!empty($line)) {
            do {
                if (!isset($i)) $i = 0;
                $c = $line[$i++];
                if (strpos($this->quoteChars, $c) !== false) {
                    $this->open[$c] = !$this->open[$c];
                }
            } while ($i < strlen($line));
        }
        $result = array_unique($this->open);
        if (count($result) > 1 || current($result)) {
            return true;
        }
        return false;
    }

    abstract protected function nextLine($max = null, $eol = "\n");

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
