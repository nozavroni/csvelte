<?php
/**
 * CSVelte: Slender, elegant CSV for PHP
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @version   v0.1
 *
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace CSVelte;

use CSVelte\Contract\Writable;
use CSVelte\Exception\WriterException;
use CSVelte\Table\Data;
use CSVelte\Table\HeaderRow;
use CSVelte\Table\Row;

/**
 * CSVelte Writer Base Class
 * A PHP CSV utility library (formerly PHP CSV Utilities).
 *
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 *
 * @todo Buffer write operations so that you can call things like setHeaderRow()
 *     and change flavor and all that jivey divey goodness at any time.
 */
class Writer
{
    /**
     * @var CSVelte\Flavor
     */
    protected $flavor;

    /**
     * @var CSVelte\Contracts\Writable
     */
    protected $output;

    /**
     * @var \Iterator
     */
    protected $headers;

    /**
     * @var int lines of data written so far (not including header)
     */
    protected $written = 0;

    /**
     * Class Constructor.
     *
     * @param CSVelte\Contract\Writable
     * @param CSVelte\Flavor
     *
     * @return void
     */
    public function __construct(Writable $output, Flavor $flavor = null)
    {
        if (is_null($flavor)) {
            $flavor = new Flavor();
        }
        $this->flavor = $flavor;
        $this->output = $output;
    }

    /**
     * Get the CSV flavor (or dialect) for this writer.
     *
     * @param void
     *
     * @return CSVelte\Flavor
     */
    public function getFlavor()
    {
        return $this->flavor;
    }

    /**
     * Sets the header row
     * If any data has been written to the output, it is too late to write the
     * header row and an exception will be thrown. Later implementations will
     * likely buffer the output so that this may be called after writeRows().
     *
     * @param \Iterator|array A list of header values
     *
     * @throws CSVelte\Exception\WriteBufferException
     *
     * @return bool
     */
    public function setHeaderRow($headers)
    {
        if ($this->written) {
            throw new WriterException('Cannot set header row once data has already been written. ');
        }
        if (is_array($headers)) {
            $headers = new \ArrayIterator($headers);
        }
        $this->headers = $headers;
    }

    /**
     * Write a single row.
     *
     * @param \Iterator|array
     *
     * @return int
     */
    public function writeRow($row)
    {
        if (!$this->written && $this->headers) {
            $headerRow = new HeaderRow((array) $this->headers);
            $this->writeHeaderRow($headerRow);
        }
        if (is_array($row)) {
            $row = new \ArrayIterator($row);
        }
        $row = $this->prepareRow($row);
        if ($count = $this->output->write((string) $row.$this->getFlavor()->lineTerminator)) {
            $this->written++;

            return $count;
        }
    }

    protected function writeHeaderRow(HeaderRow $row)
    {
        return $this->output->write((string) $row.$this->getFlavor()->lineTerminator);
    }

    /**
     * Write multiple rows.
     *
     * @param \Iterable|array of \Iterable|array
     *
     * @return int number of lines written
     */
    public function writeRows($rows)
    {
        if (is_array($rows)) {
            $rows = new \ArrayIterator($rows);
        }
        if (!($rows instanceof \Iterator)) {
            throw new \InvalidArgumentException('First argument for '.__METHOD__.' must be iterable');
        }
        $written = 0;
        if ($rows instanceof Reader) {
            $this->writeHeaderRow($rows->header());
        }
        foreach ($rows as $row) {
            if ($this->writeRow($row)) {
                $written++;
            }
        }

        return $written;
    }

    /**
     * Prepare a row of data to be written
     * This means taking an array of data, and converting it to a Row object.
     *
     * @param \Iterator|array of data items
     *
     * @return CSVelte\Table\AbstractRow
     */
    protected function prepareRow(\Iterator $row)
    {
        $items = [];
        foreach ($row as $data) {
            $items [] = $this->prepareData($data);
        }
        $row = new Row($items, $this->getFlavor());

        return $row;
    }

    /**
     * Prepare a cell of data to be written (convert to Data object).
     *
     * @param mixed Any value that can be converted to a CSVelte\Table\Data object
     *
     * @return CSVelte\Table\Data
     */
    protected function prepareData($data)
    {
        // @todo This can't be properly implemented until I get Data and DataType right...
        // it should be returning a Data object but until I get that working properly
        // it's just going to have to return a string
        return $this->quoteString($data);
    }

    protected function quoteString($str)
    {
        $flvr = $this->getFlavor();
        // Normally I would make this a method on the class, but I don't intend
        // to use it for very long, in fact, once I finish writing the Data class
        // it is gonezo!
        $hasSpecialChars = function ($s) use ($flvr) {
            $specialChars = preg_quote($flvr->lineTerminator.$flvr->quoteChar.$flvr->delimiter);
            $pattern = "/[{$specialChars}]/m";

            return preg_match($pattern, $s);
        };
        switch ($flvr->quoteStyle) {
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
        $quoteChar = ($doQuote) ? $flvr->quoteChar : '';

        return sprintf('%s%s%s',
            $quoteChar,
            $this->escapeString($str, $doQuote),
            $quoteChar
        );
    }

    protected function escapeString($str, $isQuoted = true)
    {
        $flvr = $this->getFlavor();
        $escapeQuote = '';
        if ($isQuoted) {
            $escapeQuote = ($flvr->doubleQuote) ? $flvr->quoteChar : $flvr->escapeChar;
        }
        // @todo Not sure what else, if anything, I'm supposed to be escaping here..
        return str_replace($flvr->quoteChar, $escapeQuote.$flvr->quoteChar, $str);
    }
}
