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
use function Noz\collect,
             Noz\to_array;

/**
 * CSV Dialect - Default dialect
 *
 * Due to CSV being without any definitive format definition for so long, many dialects of it exist. This class allows
 * the creation of reusable "dialects" for commonly used flavors of CSV. You can make one for Excel CSV, another for
 * tab delimited CSV, another for pipe-delimited, etc.
 */
class Dialect
{
    /** Quote none - never quote any columns */
    const QUOTE_NONE = 0;
    /** Quote all - always quote all columns */
    const QUOTE_ALL = 1;
    /** Quote minimal - quote only those columns that contain the delimiter or quote character */
    const QUOTE_MINIMAL = 2;
    /** Quote non-numeric - quote only those columns that contain non-numeric values */
    const QUOTE_NONNUMERIC = 3;

    /** Trim all - trim empty space from start and end */
    const TRIM_ALL = true;
    /** Trim none - do not trim at all */
    const TRIM_NONE = false;
    /** Trim start - trim empty space from the start (left) */
    const TRIM_START = 'start';
    /** Trim end - trim empty space from the end (right) */
    const TRIM_END = 'end';

    /** Standard attributes (from W3 CSVW working group) */

    /** @var string The character to use to begin a comment line */
    protected $commentPrefix = "#";

    /** @var string The character to delimit columns with */
    protected $delimiter = ",";

    /** @var bool Whether to escape quotes within a column by preceding them with another quote */
    protected $doubleQuote = true;

    /** @var string The character encoding for this dialect */
    protected $encoding = "utf-8";

    /** @var bool Whether the dialect expects a header row (or rows) within the data */
    protected $header = true;

    /** @var int How many header rows are expected within the data */
    protected $headerRowCount = 1;

    /** @var string The line ending character or character sequence */
    protected $lineTerminator = "\n";

    /** @var string The quoting character (used to quote columns depending on quoteStyle) */
    protected $quoteChar = '"';

    /** @var bool Whether blank rows within the data should be skipped/ignored */
    protected $skipBlankRows = false;

    /** @var int How many columns to skip/ignore */
    protected $skipColumns = 0;

    /** @var bool Whether to skip/ignore initial space within a column */
    protected $skipInitialSpace = false;

    /** @var int How many rows to skip/ignore */
    protected $skipRows = 0;

    /** @var bool|string Whether to trim empty space and where (see TRIM_* constants above) */
    protected $trim = self::TRIM_ALL;

    /** Non-standard attributes (my own additions) */

    /** @var int The quoting style (see QUOTE_* constants above) */
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
        $attribs = to_array($attribs, true);
        foreach ($attribs as $attr => $val) {
            if (property_exists($this, $attr)) {
                // find the appropriate setter...
                foreach (['set', 'setIs', 'setHas'] as $prefix) {
                    $setter = $prefix . ucfirst(strtolower($attr));
                    if (method_exists($this, $setter)) {
                        $this->{$setter}($val);
                    }
                }
            }
        }
    }

    /**
     * Set comment prefix character(s)
     *
     * @param string $commentPrefix The character(s) used to begin a comment
     *
     * @return self
     */
    public function setCommentPrefix($commentPrefix)
    {
        $this->commentPrefix = (string) $commentPrefix;
        return $this;
    }

    /**
     * Get comment prefix character(s)
     *
     * @return string
     */
    public function getCommentPrefix()
    {
        return $this->commentPrefix;
    }

    /**
     * Set delimiter character(s)
     *
     * @param string $delimiter The character(s) used to delimit data
     *
     * @return self
     */
    public function setDelimiter($delimiter)
    {
        $this->delimiter = (string) $delimiter;
        return $this;
    }

    /**
     * Set delimiter character(s)
     *
     * @return string
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * Set double quote
     *
     * @param bool $doubleQuote Whether to escape quote character with a preceding quote character
     *
     * @return self
     */
    public function setIsDoubleQuote($doubleQuote)
    {
        $this->doubleQuote = (bool) $doubleQuote;
        return $this;
    }

    /**
     * Get double quote
     *
     * @return bool
     */
    public function isDoubleQuote()
    {
        return $this->doubleQuote;
    }

    /**
     * Set character encoding
     *
     * @param string $encoding The character encoding
     *
     * @return self
     */
    public function setEncoding($encoding)
    {
        $this->encoding = (string) $encoding;
        return $this;
    }

    /**
     * Get character encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Set header row flag
     *
     * @param bool $header Whether the data has header row(s)
     *
     * @return self
     */
    public function setHasHeader($header)
    {
        $this->header = (bool) $header;
        return $this;
    }

    /**
     * Get whether dialect expects header row(s)
     *
     * @return bool
     */
    public function hasHeader()
    {
        return $this->header;
    }

    /**
     * Set header row count
     *
     * @param int $headerRowCount The amount of expected header rows
     *
     * @return self
     */
    public function setHeaderRowCount($headerRowCount)
    {
        $this->headerRowCount = (int) $headerRowCount;
        return $this;
    }

    /**
     * Get header row count
     *
     * @return int
     */
    public function getHeaderRowCount()
    {
        return $this->headerRowCount;
    }

    /**
     * Set line terminator character or character sequence
     *
     * @param string $lineTerminator The line ending character(s)
     *
     * @return self
     */
    public function setLineTerminator($lineTerminator)
    {
        $this->lineTerminator = (string) $lineTerminator;
        return $this;
    }

    /**
     * Get line terminator
     *
     * @return string
     */
    public function getLineTerminator()
    {
        return $this->lineTerminator;
    }

    /**
     * Set quote character
     *
     * @param string $quoteChar The quote character
     *
     * @return self
     */
    public function setQuoteChar($quoteChar)
    {
        $this->quoteChar = (string) $quoteChar;
        return $this;
    }

    /**
     * Get quote character
     *
     * @return string
     */
    public function getQuoteChar()
    {
        return $this->quoteChar;
    }

    /**
     * Set whether to skip blank rows
     *
     * @param bool $skipBlankRows Whether to skip blank rows
     *
     * @return self
     */
    public function setIsSkipBlankRows($skipBlankRows)
    {
        $this->skipBlankRows = (bool) $skipBlankRows;
        return $this;
    }

    /**
     * Get skip blank rows flag
     *
     * @return bool
     */
    public function isSkipBlankRows()
    {
        return $this->skipBlankRows;
    }

    /**
     * Set number of columns to skip/ignore
     *
     * @param int $skipColumns The number of columns to skip/ignore
     *
     * @return self
     */
    public function setSkipColumns($skipColumns)
    {
        $this->skipColumns = (int) $skipColumns;
        return $this;
    }

    /**
     * Get number of columns to skip/ignore
     *
     * @return int
     */
    public function getSkipColumns()
    {
        return $this->skipColumns;
    }

    /**
     * Set skip initial space flag
     *
     * @param bool $skipInitialSpace Skip initial space flag
     *
     * @return self
     */
    public function setIsSkipInitialSpace($skipInitialSpace)
    {
        $this->skipInitialSpace = (bool) $skipInitialSpace;
        return $this;
    }

    /**
     * Get skip initial space flag
     *
     * @return bool
     */
    public function isSkipInitialSpace()
    {
        return $this->skipInitialSpace;
    }

    /**
     * Set number of rows to skip/ignore
     *
     * @param int $skipRows Number of rows to skip/ignore
     *
     * @return self
     */
    public function setSkipRows($skipRows)
    {
        $this->skipRows = (int) $skipRows;
        return $this;
    }

    /**
     * Get number of rows to skip/ignore
     *
     * @return int
     */
    public function getSkipRows()
    {
        return $this->skipRows;
    }

    /**
     * Set trim type
     *
     * Allows you to set whether you want data to be trimmed on one, both, or neither sides.
     *
     * @param bool|string $trim The type trimming you want to do (see TRIM_* constants above)
     *
     * @return self
     */
    public function setTrim($trim)
    {
        $this->trim = $trim;
        return $this;
    }

    /**
     * Get trim type
     *
     * The type will coincide with one of the TRIM_* constants defined above.
     *
     * @return bool|string
     */
    public function getTrim()
    {
        return $this->trim;
    }

    /**
     * Set the quoting style
     *
     * Allows you to set how data is quoted
     *
     * @param int $quoteStyle The desired quoting style (see QUOTE_* constants above)
     *
     * @return self
     */
    public function setQuoteStyle($quoteStyle)
    {
        $this->quoteStyle = (int) $quoteStyle;
        return $this;
    }

    /**
     * Get quoting style
     *
     * The quoteStyle value will coincide with one of the QUOTE_* constants defined above.
     *
     * @return int
     */
    public function getQuoteStyle()
    {
        return $this->quoteStyle;
    }
}