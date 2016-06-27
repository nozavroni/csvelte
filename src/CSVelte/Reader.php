<?php namespace CSVelte;

use CSVelte\Taster;
use CSVelte\Flavor;
use CSVelte\Input\InputInterface;

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
     * This way I can read from local files, streams, FTP, you name it.
     * @var CSVelte\Input\InputInterface
     */
    protected $source;

    /**
     * @var CSVelte\Taster Used to determine "flavor" and "hasHeader"
     */
    protected $taster;

    /**
     * @var CSVelte\Flavor The "flavor" or format of the CSV being read
     */
    protected $flavor;

    /**
     * Class constructor
     * @todo Replace CSVelte\File hint with CSVelte\InputInterface so that reader
     *       can accept streams and any other type of input object you can cook up
     */
    public function __construct(InputInterface $input, Flavor $flavor = null)
    {
        $this->source = $input;
        if (is_null($flavor)) {
            $flavor = $this->getTaster()->lick();
        }
        $this->flavor = $flavor;
    }

    /**
     * Retreive the "flavor" object being used by the reader
     * @return CSVelte\Flavor
     */
    public function getFlavor()
    {
        return $this->flavor;
    }

    /**
     * This method is both a factory AND an accessor for the CSVelte\Taster object
     * @return CSVelte\Taster
     */
    protected function getTaster()
    {
        if (is_null($this->taster)) $this->taster = new Taster($this->source);
        return $this->taster;
    }

    /**
     * Determine whether or not the input source's CSV data contains a header
     * row or not. This method is a logical best guess. The CSV format does not
     * provide metadata of any kind and therefor does not provide this info.
     *
     * This is simply a proxy to the Taster method's tasteHeader method with the
     * addition of a sort of cache that allows you to call hasHeader() as many
     * times as you want without actually calling the expensive lickHeader()
     * again and again.
     *
     * @return boolean True if the input source MOST LIKELY has a header row
     * @uses CSVelte\Taster::lickHeader
     * @access public
     * @todo This method is ugly and unnecessary. If the Flavor class had a
     *     "hasHeader" attribute or even a hasHeader method, I could simply call
     *     that and be done with it...
     * @todo Standardize the amount of data that is passed to taster's methods
     *     to determine whatever. Assign a class constant like TESTDATA_LEN and
     *     make it like 1500 or 2500. Either that or read in a certain amount of
     *     lines or be even smarter and only read in ten lines at a time until
     *     whatever method it is has enough data to make a reliable decision/guess
     */
    public function hasHeader()
    {
        $flavor = $this->getFlavor();
        return $this->getTaster()->lickHeader($this->source->read(2500), $flavor->quoteChar, $flavor->delimiter, $flavor->lineTerminator);
    }
}
