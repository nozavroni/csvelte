<?php

/*
 * CSVelte: Slender, elegant CSV for PHP
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @version   {version}
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace CSVelte;

use CSVelte\Contract\Streamable;

use CSVelte\Exception\EndOfFileException;
use CSVelte\Reader\FilteredIterator as FilteredReader;
use CSVelte\Table\HeaderRow;

use CSVelte\Table\Row;

use function
    CSVelte\streamize;

/**
 * CSV Reader.
 *
 * Reads CSV data from any object that implements CSVelte\Contract\Readable.
 *
 * @package CSVelte
 * @subpackage Reader
 *
 * @since v0.1
 *
 * @todo Also, is there any way to do some kind of caching or something? Probably
 *     not but if you could that would be a cool feature...
 */
class Reader implements \Iterator
{
    const PLACEHOLDER_DELIM   = '[=[__DLIM__]=]';
    const PLACEHOLDER_NEWLINE = '[=[__NWLN__]=]';

    /**
     * This class supports any sources of input that implements this interface.
     * This way I can read from local files, streams, FTP, any class that implements
     * the "Readable" interface.
     *
     * @var Contract\Streamable
     */
    protected $source;

    /**
     * @var Flavor The "flavor" or format of the CSV being read
     */
    protected $flavor;

    /**
     * @var Table\Row|null Row currently loaded into memory
     */
    protected $current;

    /**
     * @var int The current line being read (from input source)
     */
    protected $line = 0;

    /**
     * @var Table\HeaderRow The header row (if any)
     */
    protected $header;

    /**
     * @var array An array of callback functions
     */
    protected $filters = [];

    /**
     * @var bool True if current line ended while inside a quoted string
     */
    protected $open = false;

    /**
     * @var bool True if last character read was the escape character
     */
    protected $escape = false;

    /**
     * Reader Constructor.
     * Initializes a reader object using an input source and optionally a flavor.
     *
     * @param mixed             $input  The source of our CSV data
     * @param Flavor|array|null $flavor The "flavor" or format specification object
     */
    public function __construct($input, $flavor = null)
    {
        $this->setSource($input)
             ->setFlavor($flavor)
             ->rewind();
    }

    /**
     * Flavor Getter.
     *
     * Retreive the "flavor" object being used by the reader
     *
     * @return Flavor
     */
    public function getFlavor()
    {
        return $this->flavor;
    }

    /**
     * Check if flavor object defines header.
     *
     * Determine whether or not the input source's CSV data contains a header
     * row or not. Unless you explicitly specify so within your Flavor object,
     * this method is a logical best guess. The CSV format does not
     * provide metadata of any kind and therefor does not provide this info.
     *
     * @return bool True if the input source has a header row (or, to be more )
     *              accurate, if the flavor SAYS it has a header row)
     *
     * @todo Rather than always reading in Taster::SAMPLE_SIZE, read in ten lines at a time until
     *     whatever method it is has enough data to make a reliable decision/guess
     */
    public function hasHeader()
    {
        return $this->getFlavor()->header;
    }

    /**
     * Retrieve current row.
     *
     * @return Table\Row The current row
     */
    public function current()
    {
        return $this->current;
    }

    /**
     * Advance to the next row.
     *
     * @return Table\Row|null The current row (if there is one)
     */
    public function next()
    {
        $this->current = null;
        $this->load();

        return $this->current;
    }

    /**
     * Determine if current position has valid row.
     *
     * @return bool True if current row is valid
     */
    public function valid()
    {
        return (bool) $this->current;
    }

    /**
     * Retrieve current row key (line number).
     *
     * @return int The current line number
     */
    public function key()
    {
        return $this->line;
    }

    /**
     * Rewind to the beginning of the dataset.
     *
     * @return Table\Row|null The current row
     */
    public function rewind()
    {
        $this->line = 0;
        $this->source->rewind();
        $this->current = null;
        $this->load();
        if ($this->hasHeader()) {
            $this->next();
        }

        return $this->current();
    }

    /**
     * Retrieve header row.
     *
     * @return Table\HeaderRow The header row if there is one
     */
    public function header()
    {
        return $this->header;
    }

    /**
     * Add anonumous function as filter.
     *
     * Add an anonymous function that accepts the current row as its only argument.
     * Return true from the function to keep that row, false otherwise.
     *
     * @param callable $filter An anonymous function to filter out row by certain criteria
     *
     * @return $this
     */
    public function addFilter(callable $filter)
    {
        array_push($this->filters, $filter);

        return $this;
    }

    /**
     * Add multiple filters at once.
     *
     * Add an array of anonymous functions to filter out certain rows.
     *
     * @param array $filters An array of anonymous functions
     *
     * @return $this
     */
    public function addFilters(array $filters)
    {
        foreach ($filters as $filter) {
            $this->addFilter($filter);
        }

        return $this;
    }

    /**
     * Returns an iterator with rows from user-supplied filter functions removed.
     *
     * @return FilteredReader An iterator with filtered rows
     */
    public function filter()
    {
        return new FilteredReader($this, $this->filters);
    }

    /**
     * Retrieve the contents of the dataset as an array of arrays.
     *
     * @return array An array of arrays of CSV content
     */
    public function toArray()
    {
        return array_map(function ($row) {
            return $row->toArray();
        }, iterator_to_array($this));
    }

    /**
     * Set the flavor.
     *
     * Set the ``CSVelte\Flavor`` object, used to determine CSV format.
     *
     * @param Flavor|array|null $flavor Either an array or a flavor object
     *
     * @return $this
     */
    protected function setFlavor($flavor = null)
    {
        if (is_array($flavor)) {
            $flavor = new Flavor($flavor);
        }
        // @todo put this inside a try/catch
        if (is_null($flavor)) {
            $flavor = taste($this->source);
        }
        if (is_null($flavor->header)) {
            // Flavor is immutable, give me a new one with header set to lickHeader return val
            $flavor = $flavor->copy(['header' => taste_has_header($this->source)]);
        }
        $this->flavor = $flavor;

        return $this;
    }

    /**
     * Set the reader source.
     *
     * The reader can accept anything that implements Readable and is actually
     * readable (can be read). This will make sure that whatever is passed to
     * the reader meets these expectations and set $this->source. It can also
     * accept any string (or any object with a __toString() method), or an
     * SplFileObject, so long as it represents a file rather than a directory.
     *
     * @param mixed $input See description
     *
     * @return $this
     */
    protected function setSource($input)
    {
        if (!($input instanceof Streamable)) {
            $input = streamize($input);
        }
        $this->source = $input;

        return $this;
    }

    /**
     * Load a line into memory.
     */
    protected function load()
    {
        if (is_null($this->current)) {
            try {
                $line = $this->readLine();
                $this->line++;
                $parsed = $this->parse($line);
                if ($this->hasHeader() && $this->line === 1) {
                    $this->header = new HeaderRow($parsed);
                } else {
                    $this->current = new Row($parsed);
                    if ($this->header) {
                        $this->current->setHeaderRow($this->header);
                    }
                }
            } catch (EndOfFileException $e) {
                $this->current = null;
            }
        }
    }

    /**
     * Read single line from CSV data source (stream, file, etc.), taking into
     * account CSV's de-facto quoting rules with respect to designated line
     * terminator character when they fall within quoted strings.
     *
     * @throws Exception\EndOfFileException when eof has been reached
     *                                      and the read buffer has all been returned
     *
     * @return string A CSV row (could possibly span multiple lines depending on
     *                quoting and escaping)
     */
    protected function readLine()
    {
        $f   = $this->getFlavor();
        $eol = $f->lineTerminator;
        try {
            do {
                if (!isset($lines)) {
                    $lines = [];
                }
                if (false === ($line = $this->source->readLine($eol))) {
                    throw new EndOfFileException('End of file reached');
                }
                array_push($lines, rtrim($line, $eol));
            } while ($this->inQuotedString(end($lines), $f->quoteChar, $f->escapeChar));
        } catch (EndOfFileException $e) {
            // only throw the exception if we don't already have lines in the buffer
            if (!count($lines)) {
                throw $e;
            }
        }

        return rtrim(implode($eol, $lines), $eol);
    }

    /**
     * Determine whether last line ended while a quoted string was still "open".
     *
     * This method is used in a loop to determine if each line being read ends
     * while a quoted string is still "open".
     *
     * @param string $line       Line of csv to analyze
     * @param string $quoteChar  The quote/enclosure character to use
     * @param string $escapeChar The escape char/sequence to use
     *
     * @return bool True if currently within a quoted string
     */
    protected function inQuotedString($line, $quoteChar, $escapeChar)
    {
        if (!empty($line)) {
            do {
                if (!isset($i)) {
                    $i = 0;
                }
                $c                 = $line[$i++];
                if ($this->escape) {
                    $this->escape = false;
                    continue;
                }
                $this->escape                     = ($c == $escapeChar);
                if ($c == $quoteChar) {
                    $this->open = !$this->open;
                }
            } while ($i < strlen($line));
        }

        return $this->open;
    }

    /**
     * Temporarily replace special characters within a quoted string.
     *
     * Replace all instances of newlines and whatever character you specify (as
     * the delimiter) that are contained within quoted text. The replacements are
     * simply a special placeholder string. This is done so that I can use the
     * very unsmart "explode" function and not have to worry about it exploding
     * on delimiters or newlines within quotes. Once I have exploded, I typically
     * sub back in the real characters before doing anything else.
     *
     * @param string $data  The string to do the replacements on
     * @param string $delim The delimiter character to replace
     * @param string $quo   The quote character
     * @param string $eol   Line terminator character/sequence
     *
     * @return string The data with replacements performed
     *
     * @internal
     *
     * @todo I could probably pass in (maybe optionally) the newline character I
     *     want to replace as well. I'll do that if I need to.
     * @todo Create a regex class so you can do $regex->escape() rather than
     *     preg_quote
     */
    protected function replaceQuotedSpecialChars($data, $delim, $quo, $eol)
    {
        return preg_replace_callback('/([' . preg_quote($quo, '/') . '])(.*)\1/imsU', function ($matches) use ($delim, $eol) {
            $ret = str_replace($eol, self::PLACEHOLDER_NEWLINE, $matches[0]);
            $ret = str_replace($delim, self::PLACEHOLDER_DELIM, $ret);

            return $ret;
        }, $data);
    }

    /**
     * Undo temporary special char replacements.
     *
     * Replace the special character placeholders with the characters they
     * originally substituted.
     *
     * @param string $data  The data to undo replacements in
     * @param string $delim The delimiter character
     * @param string $eol   The character or string of characters used to terminate lines
     *
     * @return string The data with placeholders replaced with original characters
     *
     * @internal
     */
    protected function undoReplaceQuotedSpecialChars($data, $delim, $eol)
    {
        $replacements = [self::PLACEHOLDER_DELIM => $delim, self::PLACEHOLDER_NEWLINE => $eol];
        if (array_walk($replacements, function ($replacement, $placeholder) use (&$data) {
            $data = str_replace($placeholder, $replacement, $data);
        })) {
            return $data;
        }
    }

    /**
     * Remove quotes wrapping text.
     *
     * @param string $data The data to unquote
     *
     * @return string The data with quotes stripped from the outside of it
     *
     * @internal
     */
    protected function unQuote($data)
    {
        $escapeChar = $this->getFlavor()->doubleQuote ? $this->getFlavor()->quoteChar : $this->getFlavor()->escapeChar;
        $quoteChar  = $this->getFlavor()->quoteChar;
        $data       = $this->unEscape($data, $escapeChar, $quoteChar);

        return preg_replace('/^(["\'])(.*)\1$/ms', '\2', $data);
    }

    /**
     * "Unescape" a string.
     *
     * Replaces escaped characters with their unescaped versions.
     *
     * @internal
     *
     * @param string $str The string to unescape
     * @param string $esc The escape character used
     * @param string $quo The quote character used
     *
     * @return mixed The string with characters unescaped
     *
     * @todo This actually shouldn't even be necessary. Characters should be read
     *     in one at a time and a quote that follows another should just be ignored
     *     deeming this unnecessary.
     */
    protected function unEscape($str, $esc, $quo)
    {
        return str_replace($esc . $quo, $quo, $str);
    }

    /**
     * Parse a line of CSV data into an array of columns.
     *
     * @param string $line A line of CSV data to parse
     *
     * @return array An array of columns
     *
     * @internal
     */
    protected function parse($line)
    {
        $f        = $this->getFlavor();
        $replaced = $this->replaceQuotedSpecialChars($line, $f->delimiter, $f->quoteChar, $f->lineTerminator);
        $columns  = explode($f->delimiter, $replaced);
        $that     = $this;

        return array_map(function ($val) use ($that, $f) {
            $undone = $that->undoReplaceQuotedSpecialChars($val, $f->delimiter, $f->lineTerminator);

            return $this->unQuote($undone);
        }, $columns);
    }
}
