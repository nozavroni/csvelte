<?php namespace CSVelte;

use CSVelte\Contract\Readable;

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
}
