<?php namespace CSVelte\Input;

use CSVelte\Traits\StreamIO;
use CSVelte\Contract\Readable;
use CSVelte\Exception\EndOfFileException;
use CSVelte\Traits\HandlesQuotedLineTerminators;
use CSVelte\Exception\InvalidStreamResourceException;

/**
 * CSVelte\Input\Stream
 * Represents a stream source for CSV data
 *
 * @package   CSVelte
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @todo      Look at the ArrayObject class and see if it can be used
 */
class Stream implements Readable
{
    use HandlesQuotedLineTerminators, StreamIO;

    const FOPEN_MODE = 'r';

    /**
     * @const string
     */
    const RESOURCE_TYPE = 'stream';

    /**
     * @const integer
     */
    const MAX_LINE_LENGTH = 4096;

    /**
     * @inheritDoc
     */
    public function read($length)
    {
        $this->assertStreamExistsAndIsReadable();
        if (false === ($data = fread($this->source, $length))) {
            if ($this->isEof())
                throw new EndOfFileException('Cannot read from ' . $this->name() . '. End of file has been reached.');
            // @todo not sure if this is necessary... may cause bugs/unpredictable behavior even...
            //throw new \OutOfBoundsException('Cannot read from ' . $this->name());
            return false;
        }
        $this->updateInfo();
        return $data;
    }

    /**
     * @inheritDoc
     */
    public function nextLine($max = null, $eol = PHP_EOL)
    {
        $this->assertStreamExistsAndIsReadable();
        if (false === ($line = stream_get_line($this->source, $max ?: self::MAX_LINE_LENGTH, $eol))) {
            if ($this->isEof())
                throw new EndOfFileException('Cannot read from ' . $this->name() . '. End of file has been reached.');
            // @todo not sure if this is necessary... may cause bugs/unpredictable behavior even...
            //throw new \OutOfBoundsException('Cannot read line from ' . $this->name());
            return false;
        }
        $this->updateInfo();
        return $line;
    }

    /**
     * Have we reached the EOF (end of file/stream)?
     *
     * @return boolean
     * @access public
     */
    public function isEof()
    {
        return feof($this->source);
    }

    /**
     * File must be able to be rewound when the end is reached
     *
     * @return void
     * @access public
     */
    public function rewind()
    {
        rewind($this->source);
        $this->updateInfo();
    }

    /**
     * Does a series of checks on the internal stream resource to ensure it is
     * readable, hasn't been closed already, etc. If it finds a problem, the
     * appropriate exception will be thrown. Called before any attempts to read
     * from the stream resource.
     *
     * @return void
     * @throws CSVelte\CSVelte\Exception\InvalidStreamResourceException
     * @access protected
     */
    protected function assertStreamExistsAndIsReadable()
    {
        switch (true) {
            case !is_resource($this->source):
                throw new InvalidStreamResourceException('Cannot read from ' . $this->name() . '. It is either invalid or has been closed.');
        }
    }
}
