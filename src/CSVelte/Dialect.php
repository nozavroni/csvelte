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
namespace CSVelte;

use Traversable;
use function Noz\to_array;

class Dialect
{
    const QUOTE_NONE = 0;
    const QUOTE_ALL = 1;
    const QUOTE_MINIMAL = 2;
    const QUOTE_NONNUMERIC = 3;

    // standard
    protected $commentPrefix = "#";
    protected $delimiter = ",";
    protected $doubleQuote = true;
    protected $encoding = "utf-8";
    protected $header = true;
    protected $headerRowCount = 1;
    protected $lineTerminators = ["\r\n", "\n"];
    protected $quoteChar = '"';
    protected $skipBlankRows = false;
    protected $skipColumns = 0;
    protected $skipInitialSpace = false;
    protected $skipRows = 0;
    protected $trim = false;

    // non-standard
    protected $quoteStyle = self::QUOTE_MINIMAL;

    /**
     * Dialect constructor.
     *
     * Any of the above properties may be set within the $attribs array to initialize the dialect with different
     * attributes than are defined above.
     *
     * @param array|Traversable $attribs An array of dialect attributes
     */
    public function __construct($attribs = null)
    {

    }

    public function setCommentPrefix($commentPrefix)
    {
        $this->commentPrefix = (string) $commentPrefix;
        return $this;
    }

    public function getCommentPrefix()
    {
        return $this->commentPrefix;
    }

    public function setDelimiter($delimiter)
    {
        $this->delimiter = (string) $delimiter;
        return $this;
    }

    public function getDelimiter()
    {
        return $this->delimiter;
    }

    public function setIsDoubleQuote($doubleQuote)
    {
        $this->doubleQuote = (bool) $doubleQuote;
        return $this;
    }

    public function isDoubleQuote()
    {
        return $this->doubleQuote;
    }

    public function setEncoding($encoding)
    {
        $this->encoding = (string) $encoding;
        return $this;
    }

    public function getEncoding()
    {
        return $this->encoding;
    }

    public function setHasHeader($header)
    {
        $this->header = (bool) $header;
        return $this;
    }

    public function hasHeader()
    {
        return $this->header;
    }

    public function setLineTerminators($lineTerminators)
    {
        $this->lineTerminators = to_array($lineTerminators, true);
        return $this;
    }

    public function getLineTerminators()
    {
        return $this->lineTerminators;
    }

    public function setQuoteChar($quoteChar)
    {
        $this->quoteChar = (string) $quoteChar;
        return $this;
    }

    public function getQuoteChar()
    {
        return $this->quoteChar;
    }

    public function setIsSkipBlankRows($skipBlankRows)
    {
        $this->skipBlankRows = (bool) $skipBlankRows;
        return $this;
    }

    public function isSkipBlankRows()
    {
        return $this->skipBlankRows;
    }

    public function setSkipColumns($skipColumns)
    {
        $this->skipColumns = (int) $skipColumns;
        return $this;
    }

    public function getSkipColumns()
    {
        return $this->skipColumns;
    }

    public function setIsSkipInitialSpace($skipInitialSpace)
    {
        $this->skipInitialSpace = (bool) $skipInitialSpace;
        return $this;
    }

    public function isSkipInitialSpace()
    {
        return $this->skipInitialSpace;
    }

    public function setSkipRows($skipRows)
    {
        $this->skipRows = (int) $skipRows;
        return $this;
    }

    public function getSkipRows()
    {
        return $this->skipRows;
    }

    public function setIsTrim($trim)
    {
        $this->trim = (bool) $trim;
        return $this;
    }

    public function isTrim()
    {
        return $this->trim;
    }

    public function setQuoteStyle($quoteStyle)
    {
        $this->quoteStyle = (int) $quoteStyle;
        return $this;
    }

    public function getQuoteStyle()
    {
        return $this->quoteStyle;
    }
}