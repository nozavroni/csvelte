<?php
/**
 * CSVelte: Slender, elegant CSV for PHP
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @version   v0.2.1
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace CSVelte;

use CSVelte\Contract\Streamable;

use CSVelte\Table\AbstractRow;
use \Iterator;
use \ArrayIterator;
use CSVelte\Table\HeaderRow;
use CSVelte\Table\Row;

use \InvalidArgumentException;
use CSVelte\Exception\WriterException;

/**
 * CSVelte Writer Base Class
 * A PHP CSV utility library (formerly PHP CSV Utilities).
 *
 * @package   CSVelte
 * @copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @todo Buffer write operations so that you can call things like setHeaderRow()
 *     and change flavor and all that jivey divey goodness at any time.
 */
class Writer
{
    /**
     * The flavor (format) of CSV to write.
     *
     * @var Flavor
     */
    protected $flavor;

    /**
     * The output stream to write to.
     *
     * @var Contract\Streamable
     */
    protected $output;

    /**
     * The header row.
     *
     * @var \Iterator
     */
    protected $headers;

    /**
     * Number of lines written so far (not including header)
     *
     * @var int
     */
    protected $written = 0;

    /**
     * Class Constructor.
     *
     * @param Contract\Streamable $output An output source to write to
     * @param Flavor|array $flavor A flavor or set of formatting params
     */
    public function __construct(Streamable $output, $flavor = null)
    {
        if (!($flavor instanceof Flavor)) $flavor = new Flavor($flavor);
        $this->flavor = $flavor;
        $this->output = $output;
    }

    /**
     * Get the CSV flavor (or dialect) for this writer.
     *
     * @param void
     * @return Flavor
     */
    public function getFlavor()
    {
        return $this->flavor;
    }

    /**
     * Sets the header row
     * If any data has been written to the output, it is too late to write the
     * header row and an exception will be thrown. Later implementations will
     * likely buffer the output so that this may be called after writeRows()
     *
     * @param \Iterator|array A list of header values
     * @return $this
     * @throws Exception\WriterException
     */
    public function setHeaderRow($headers)
    {
        if ($this->written) {
            throw new WriterException("Cannot set header row once data has already been written. ");
        }
        if (is_array($headers)) $headers = new ArrayIterator($headers);
        $this->headers = $headers;
        return $this;
    }

    /**
     * Write a single row
     *
     * @param \Iterator|array $row The row to write to source
     * @return int The number or bytes written
     */
    public function writeRow($row)
    {
        $eol = $this->getFlavor()->lineTerminator;
        $delim = $this->getFlavor()->delimiter;
        if (!$this->written && $this->headers) {
            $headerRow = new HeaderRow((array) $this->headers);
            $this->writeHeaderRow($headerRow);
        }
        if (is_array($row)) $row = new ArrayIterator($row);
        $row = $this->prepareRow($row);
        if ($count = $this->output->writeLine($row->join($delim), $eol)) {
            $this->written++;
            return $count;
        }
        return false;
    }

    /**
     * Write the header row.
     *
     * @param HeaderRow $row
     * @return int|false
     */
    protected function writeHeaderRow(HeaderRow $row)
    {
        $eol = $this->getFlavor()->lineTerminator;
        $delim = $this->getFlavor()->delimiter;
        $row = $this->prepareRow($row);
        return $this->output->writeLine($row->join($delim), $eol);
    }

    /**
     * Write multiple rows
     *
     * @param \Iterator|array $rows List of \Iterable|array
     * @return int number of lines written
     */
    public function writeRows($rows)
    {
        if (is_array($rows)) $rows = new ArrayIterator($rows);
        if (!($rows instanceof Iterator)) {
            throw new InvalidArgumentException('First argument for ' . __METHOD__ . ' must be iterable');
        }
        $written = 0;
        if ($rows instanceof Reader) {
            $this->writeHeaderRow($rows->header());
        }
        foreach ($rows as $row) {
            if ($this->writeRow($row)) $written++;
        }
        return $written;
    }

    /**
     * Prepare a row of data to be written
     * This means taking an array of data, and converting it to a Row object
     *
     * @param \Iterator $row of data items
     * @return AbstractRow
     */
    protected function prepareRow(Iterator $row)
    {
        $items = array();
        foreach ($row as $data) {
            $items []= $this->prepareData($data);
        }
        $row = new Row($items);
        return $row;
    }

    /**
     * Prepare a cell of data to be written (convert to Data object)
     *
     * @param string $data A string containing cell data
     * @return string quoted string data
     */
    protected function prepareData($data)
    {
        // @todo This can't be properly implemented until I get Data and DataType right...
        // it should be returning a Data object but until I get that working properly
        // it's just going to have to return a string
        return $this->quoteString($data);
    }

    /**
     * Enclose a string in quotes.
     *
     * Accepts a string and returns it with quotes around it.
     *
     * @param string $str The string to wrap in quotes
     * @return string
     */
    protected function quoteString($str)
    {
        $flvr = $this->getFlavor();
        // Normally I would make this a method on the class, but I don't intend
        // to use it for very long, in fact, once I finish writing the Data class
        // it is gonezo!
        $hasSpecialChars = function($s) use ($flvr) {
            $specialChars = preg_quote($flvr->lineTerminator . $flvr->quoteChar . $flvr->delimiter);
            $pattern = "/[{$specialChars}]/m";
            return preg_match($pattern, $s);
        };
        switch($flvr->quoteStyle) {
            case Flavor::QUOTE_ALL:
                $doQuote = true;
                break;
            case Flavor::QUOTE_NONNUMERIC:
                $doQuote = !is_numeric($str);
                break;
            case Flavor::QUOTE_MINIMAL:
                $doQuote = $hasSpecialChars($str);
                break;
            case Flavor::QUOTE_NONE:
            default:
                // @todo I think that if a cell is not quoted, newlines and delimiters should still be escaped by the escapeChar... no?
                $doQuote = false;
                break;
        }
        $quoteChar = ($doQuote) ? $flvr->quoteChar : "";
        return sprintf("%s%s%s",
            $quoteChar,
            $this->escapeString($str, $doQuote),
            $quoteChar
        );
    }

    /**
     * Escape a string.
     *
     * Return a string with all special characters escaped.
     *
     * @param string $str The string to escape
     * @param bool $isQuoted True if string is quoted
     * @return string
     */
    protected function escapeString($str, $isQuoted = true)
    {
        $flvr = $this->getFlavor();
        $escapeQuote = "";
        if ($isQuoted) $escapeQuote = ($flvr->doubleQuote) ? $flvr->quoteChar : $flvr->escapeChar;
        // @todo Not sure what else, if anything, I'm supposed to be escaping here..
        return str_replace($flvr->quoteChar, $escapeQuote . $flvr->quoteChar, $str);
    }
}
