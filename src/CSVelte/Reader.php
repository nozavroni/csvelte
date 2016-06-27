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
            $taster = new Taster($this->source);
            $flavor = $taster->lick();
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
}
