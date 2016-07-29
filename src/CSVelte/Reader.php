<?php namespace CSVelte;

use CSVelte\Contract\Readable;
use CSVelte\Table\Row;
use CSVelte\Table\HeaderRow;
use CSVelte\Exception\EndOfFileException;

/**
 * CSVelte
 * A PHP CSV utility library (formerly PHP CSV Utilities).
 *
 * @package   CSVelte
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @todo Is there ever a use case where one needs to simply iterate over every
 *     datum within a CSV data source, ignoring rows almost completely? It would
 *     just iterate over a row until it got to the end of the row, at which point
 *     it would just start over at the beginning of the next row? One continuous
 *     foreach over every datum in the source. IF so, check out RecursiveIterator
 * @todo Use the abstract SPL class FilterIterator (extend it) for a cleaner
 *     interface for eliminating the header row from being iterated.
 * @todo Also, is there any way to do some kind of caching or something? Probably
 *     not but if you could that would be a cool feature...
 * @todo Check out http://php.net/manual/en/class.splfileobject.php and see what info
 *     you might be able to gleen from that. Apparently it has some CSV methods. Can
 *     I use that class/object or can anything be learned from it?
 */
class Reader implements \OuterIterator
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
     * @var CSVelte\Reader\Row Row currently loaded into memory
     */
    protected $current;

    /**
     * @var integer The current line being read (from input source)
     */
    protected $line = 0;

    /**
     * @var CSVelte\Reader\HeaderRow The header row (if any)
     */
    protected $header;

    /**
     * Class constructor
     * @param CSVelte\Contract\Readable The source of our CSV data
     * @param CSVelte\Flavor The "flavor" or format specification object
     * @return void
     * @access public
     * @todo Taster is kind of a mess. It's not particularly easy to work with.
     *     Look at all this code I needed just to use it. Stupid. Time for a
     *     refactor... Maybe pass an argument to the lick() method to have it
     *     run lickHeader and set that value within the returned flavor's
     *     properties rather than all this silliness. Not to mention the oddness
     *     of setting a source in its constructor and then, inexplicably, still
     *     asking for a data sample in lickHeader. Very poor design. SMH at myself.
     */
    public function __construct(Readable $input, Flavor $flavor = null)
    {
        $this->source = $input;
        $taster = new Taster($this->source);
        if (is_null($flavor)) {
            $flavor = $taster->lick();
        }
        try {
            $hasHeader = $flavor->header;
        } catch (\OutOfBoundsException $e) {
            $hasHeader = null;
        } finally {
            if (is_null($hasHeader)) {
                // Flavor is immutable, give me a new one with header set to lickHeader return val
                $flavor = $flavor->copy(array('header' => $taster->lickHeader($this->source->read(Taster::SAMPLE_SIZE), $flavor->quoteChar, $flavor->delimiter, $flavor->lineTerminator)));
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
                $line = $this->source->readLine(null, $this->getFlavor()->lineTerminator);
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

    // protected function loadNew()
    // {
    //     if (!$this->isLoaded()) {
    //         try {
    //             $lt = $this->getFlavor()->lineTerminator;
    //             $line = $this->source->readLine(null, $lt);
    //             $parsed = $this->parse($line);
    //             $this->line++;
    //             if ($this->isHeaderLine()) {
    //                 $row = new HeaderRow($parsed);
    //             } else {
    //                 $row = new Row($parsed);
    //             }
    //         } catch (EndOfFileException $e) {
    //             $this->current = false;
    //         }
    //     }
    // }

    /**
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
     * Replace all instances of newlines and whatever character you specify (as
     * the delimiter) that are contained within quoted text. The replacements are
     * simply a special placeholder string. This is done so that I can use the
     * very unsmart "explode" function and not have to worry about it exploding
     * on delimiters or newlines within quotes. Once I have exploded, I typically
     * sub back in the real characters before doing anything else.
     *
     * @param string The string to do the replacements on
     * @param char The delimiter character to replace
     * @return string The data with replacements performed
     * @access protected
     * @todo I could probably pass in (maybe optionally) the newline character I
     *     want to replace as well. I'll do that if I need to.
     */
    protected function replaceQuotedSpecialChars($data, $delim, $eol)
    {
        return preg_replace_callback('/([\'"])(.*)\1/imsU', function($matches) use ($delim, $eol) {
            $ret = str_replace($eol, self::PLACEHOLDER_NEWLINE, $matches[0]);
            $ret = str_replace($delim, self::PLACEHOLDER_DELIM, $ret);
            return $ret;
        }, $data);
    }

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
     * Remove quotes wrapping text
     */
    protected function unQuote($data)
    {
        $escapeChar = $this->getFlavor()->doubleQuote ? $this->getFlavor()->quoteChar : $this->getFlavor()->escapeChar;
        $quoteChar = $this->getFlavor()->quoteChar;
        $data = $this->unEscape($data, $escapeChar, $quoteChar);
        return preg_replace('/^(["\'])(.*)\1$/ms', '\2', $data);
    }

    /**
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
     * @todo The Readable class needs to be smart enough to ignore quoted newline
     *     characters. If a newline falls within quotes, it should be considered
     *     part of the line rather than its terminator. Maybe I need to put the
     *     replaceQuotedSpecialChars method into Utils so that I can use it all
     *     over the place? Or... maybe write a stream wrapper or whatever that
     *     does those replacements. That might be a good way to go...
     */
    protected function parse($line)
    {
        $f = $this->getFlavor();
        $replaced = $this->replaceQuotedSpecialChars($line, $f->delimiter, $f->lineTerminator);
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

    public function getInnerIterator()
    {
        return $this->current();
    }

    public function header()
    {
        return $this->header;
    }
}
