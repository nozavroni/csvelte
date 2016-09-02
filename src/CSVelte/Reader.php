<?php
/**
 * CSVelte: Slender, elegant CSV for PHP
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
namespace CSVelte;

use \Closure;
use \FilterIterator;
use CSVelte\Contract\Readable;
use CSVelte\Table\Row;
use CSVelte\Table\HeaderRow;
use CSVelte\Exception\EndOfFileException;
use CSVelte\Reader\FilteredIterator as FilteredReader;

/**
 * CSV Reader
 *
 * Reads CSV data from any object that implements CSVelte\Contract\Readable.
 *
 * @package CSVelte
 * @subpackage Reader
 * @since v0.1
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
     * the "Readable" interface
     * @var CSVelte\Contract\Readable
     */
    protected $source;

    /**
     * @var CSVelte\Flavor The "flavor" or format of the CSV being read
     */
    protected $flavor;

    /**
     * @var CSVelte\Table\AbstractRow Row currently loaded into memory
     */
    protected $current;

    /**
     * @var integer The current line being read (from input source)
     */
    protected $line = 0;

    /**
     * @var CSVelte\Table\HeaderRow The header row (if any)
     */
    protected $header;

    /**
     * @var array An array of callback functions
     */
    protected $filters = array();

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
     * Initializes a reader object using an input source and optionally a flavor
     *
     * @param \CSVelte\Contract\Readable The source of our CSV data
     * @param \CSVelte\Flavor The "flavor" or format specification object
     * @todo Maybe allow SplFileObj as first arg and then do if ($input instanceof SplFileObjs)
     */
    public function __construct(Readable $input, Flavor $flavor = null)
    {
        $this->source = $input;
        $taster = new Taster($this->source);
        // @todo put this inside a try/catch
        if (is_null($flavor)) {
            $flavor = $taster->lick();
        }
        try {
            $hasHeader = $flavor->header;
        } catch (\OutOfBoundsException $e) {
            $hasHeader = null;
        } finally { // @todo get rid of this
            if (is_null($hasHeader)) {
                // Flavor is immutable, give me a new one with header set to lickHeader return val
                $flavor = $flavor->copy(array('header' => $taster->lickHeader($this->source->fread(Taster::SAMPLE_SIZE), $flavor->quoteChar, $flavor->delimiter, $flavor->lineTerminator)));
            }
        }
        $this->flavor = $flavor;
        $this->rewind();
    }

    /**
     * Load a line into memory
     *
     * @return void ($this?)
     * @access protected
     */
    protected function load()
    {
        if (is_null($this->current)) {
            try {
                $line = $this->readLine();
                $this->line++;
                $parsed = $this->parse($line);
                if ($this->hasHeader() && $this->line === 1) {
                    $this->header = new HeaderRow($parsed, $this->flavor);
                } else {
                    $this->current = new Row($parsed, $this->flavor);
                    if ($this->header) $this->current->setHeaderRow($this->header);
                }
            } catch (EndOfFileException $e) {
                $this->current = false;
            }
        }
    }

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
    protected function readLine()
    {
        $f = $this->getFlavor();
        $eol = $f->lineTerminator;
        try {
            do {
                if (!isset($lines)) $lines = array();
                array_push($lines, $this->source->fgets());
            } while ($this->inQuotedString(end($lines), $f->quoteChar, $f->escapeChar));
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
     * Flavor Getter.
     * Retreive the "flavor" object being used by the reader
     *
     * @return CSVelte\Flavor
     * @access public
     */
    public function getFlavor()
    {
        return $this->flavor;
    }

    /**
     * Check if flavor object defines header
     *
     * Determine whether or not the input source's CSV data contains a header
     * row or not. Unless you explicitly specify so within your Flavor object,
     * this method is a logical best guess. The CSV format does not
     * provide metadata of any kind and therefor does not provide this info.
     *
     * @return boolean True if the input source has a header row (or, to be more )
     *     accurate, if the flavor SAYS it has a header row)
     * @access public
     * @todo Rather than always reading in Taster::SAMPLE_SIZE, read in ten lines at a time until
     *     whatever method it is has enough data to make a reliable decision/guess
     */
    public function hasHeader()
    {
        return $this->getFlavor()->header;
    }

    /**
     * Temporarily replace special characters within a quoted string
     *
     * Replace all instances of newlines and whatever character you specify (as
     * the delimiter) that are contained within quoted text. The replacements are
     * simply a special placeholder string. This is done so that I can use the
     * very unsmart "explode" function and not have to worry about it exploding
     * on delimiters or newlines within quotes. Once I have exploded, I typically
     * sub back in the real characters before doing anything else.
     *
     * @param string The string to do the replacements on
     * @param char The delimiter character to replace
     * @param char The quote character
     * @param string Line terminator sequence
     * @return string The data with replacements performed
     * @access protected
     * @internal
     * @todo I could probably pass in (maybe optionally) the newline character I
     *     want to replace as well. I'll do that if I need to.
     * @todo Create a regex class so you can do $regex->escape() rather than
     *     preg_quote
     */
    protected function replaceQuotedSpecialChars($data, $delim, $quo, $eol)
    {
        return preg_replace_callback('/(['. preg_quote($quo, '/') . '])(.*)\1/imsU', function($matches) use ($delim, $eol) {
            $ret = str_replace($eol, self::PLACEHOLDER_NEWLINE, $matches[0]);
            $ret = str_replace($delim, self::PLACEHOLDER_DELIM, $ret);
            return $ret;
        }, $data);
    }

    /**
     * Undo temporary special char replacements
     *
     * Replace the special character placeholders with the characters they
     * originally substituted.
     *
     * @param string $data The data to undo replacements in
     * @param string $delim The delimiter character
     * @param string $eol The character or string of characters used to terminate lines
     * @return string The data with placeholders replaced with original characters
     * @internal
     */
    protected function undoReplaceQuotedSpecialChars($data, $delim, $eol)
    {
        $replacements = array(self::PLACEHOLDER_DELIM => $delim, self::PLACEHOLDER_NEWLINE => $eol);
        if (array_walk($replacements, function($replacement, $placeholder) use (&$data) {
            $data = str_replace($placeholder, $replacement, $data);
        })) {
            return $data;
        }
    }

    /**
     * Remove quotes wrapping text.
     *
     * @param string The data to unquote
     * @return string The data with quotes stripped from the outside of it
     * @internal
     */
    protected function unQuote($data)
    {
        $escapeChar = $this->getFlavor()->doubleQuote ? $this->getFlavor()->quoteChar : $this->getFlavor()->escapeChar;
        $quoteChar = $this->getFlavor()->quoteChar;
        $data = $this->unEscape($data, $escapeChar, $quoteChar);
        return preg_replace('/^(["\'])(.*)\1$/ms', '\2', $data);
    }

    /**
     * @internal
     * @todo This actually shouldn't even be necessary. Characters should be read
     *     in one at a time and a quote that follows another should just be ignored
     *     deeming this unnecessary.
     */
    protected function unEscape($str, $esc, $quo)
    {
        return str_replace($esc . $quo, $quo, $str);
    }

    /**
     * Parse a line of CSV data into an array of columns
     *
     * @param string A line of CSV data to parse
     * @return array An array of columns
     * @access protected
     * @internal
     */
    protected function parse($line)
    {
        $f = $this->getFlavor();
        $replaced = $this->replaceQuotedSpecialChars($line, $f->delimiter, $f->quoteChar, $f->lineTerminator);
        $columns = explode($f->delimiter, $replaced);
        $that = $this;
        return array_map(function($val) use ($that, $f) {
            $undone = $that->undoReplaceQuotedSpecialChars($val, $f->delimiter, $f->lineTerminator);
            return $this->unQuote($undone);
        }, $columns);
    }

    public function current()
    {
        return $this->current;
    }

    public function next()
    {

        $this->current = null;
        $this->load();
        return $this->current;
    }

    public function valid()
    {
        return (bool) $this->current;
    }

    public function key()
    {
        return $this->line;
    }

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

    public function header()
    {
        return $this->header;
    }

    /**
     * @todo Closure should be changed to "Callable" (php5.4+)
     */
    public function addFilter(Closure $filter)
    {
        array_push($this->filters, $filter);
        return $this;
    }

    public function addFilters(array $filters)
    {
        foreach ($filters as $filter) {
            $this->addFilter($filter);
        }
        return $this;
    }

    public function filter()
    {
        return new FilteredReader($this, $this->filters);
    }

}
