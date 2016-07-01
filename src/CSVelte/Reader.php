<?php namespace CSVelte;

use CSVelte\Contract\Readable;
use CSVelte\Reader\Row;

/**
 * CSVelte
 * A PHP CSV utility library (formerly PHP CSV Utilities).
 *
 * @package   CSVelte
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @todo      Use SPL interfaces such as Iterator, SeekableIterator, Countable,
 *     etc. to make the reader as easy as possible to work with
 */
class Reader
{
    const PLACEHOLDER_DELIM = '[=[__DELIM__]=]';
    const PLACEHOLDER_NEWLINE = '[=[__NEWLINE__]=]';

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
            $hasHeader = $flavor->getProperty('hasHeader');
        } catch (\OutOfBoundsException $e) {
            $hasHeader = null;
        } finally {
            if (is_null($hasHeader)) {
                $flavor->setProperty('hasHeader', $taster->lickHeader($this->source->read(Taster::SAMPLE_SIZE), $flavor->quoteChar, $flavor->delimiter, $flavor->lineTerminator));
            }
        }
        $this->flavor = $flavor;
        $this->load();
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
            $line = $this->source->readLine(null, $this->getFlavor()->lineTerminator);
            $this->current = new Row($this->parse($line));
        }
    }

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
        try {
            return $this->getFlavor()->getProperty('hasHeader');
        } catch (\OutOfBoundsException $e) {
            return false;
        }
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

    protected function unQuote($data)
    {
        return preg_replace('/^(["\'])(.*)\1$/', '\2', $data);
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
}
