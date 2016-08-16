<?php
/**
 * CSVelte: Slender, elegant CSV for PHP
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @version   v0.1
 *
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace CSVelte\Input;

use CSVelte\Contract\Readable;
use CSVelte\Exception\EndOfFileException;
use CSVelte\Exception\InvalidStreamResourceException;
use CSVelte\Traits\HandlesQuotedLineTerminators;
use CSVelte\Traits\StreamIO;

/**
 * CSVelte\Input\Stream
 * Represents a stream source for CSV data.
 *
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 *
 * @todo      Look at the ArrayObject class and see if it can be used
 */
class Stream implements Readable
{
    use HandlesQuotedLineTerminators, StreamIO;

    /**
     * @const string
     */
    const RESOURCE_TYPE = 'stream';

    /**
     * @const integer
     */
    const MAX_LINE_LENGTH = 4096;

    /**
     * Get the "mode" used to open stream resource handle.
     *
     * @return string
     *
     * @see fopen function
     *
     * @todo I'm definitely not in love with this design but I'll refactor later
     */
    protected function getMode()
    {
        return 'rb';
    }

    /**
     * {@inheritdoc}
     */
    public function read($length)
    {
        $this->assertStreamExistsAndIsReadable();
        if (false === ($data = fread($this->source, $length))) {
            if ($this->isEof()) {
                throw new EndOfFileException('Cannot read from '.$this->name().'. End of file has been reached.');
            }
            // @todo not sure if this is necessary... may cause bugs/unpredictable behavior even...
            //throw new \OutOfBoundsException('Cannot read from ' . $this->name());
            return false;
        }
        $this->updateInfo();

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function nextLine($max = null, $eol = PHP_EOL)
    {
        $this->assertStreamExistsAndIsReadable();
        if (false === ($line = stream_get_line($this->source, $max ?: self::MAX_LINE_LENGTH, $eol))) {
            if ($this->isEof()) {
                throw new EndOfFileException('Cannot read from '.$this->name().'. End of file has been reached.');
            }
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
     * @return bool
     */
    public function isEof()
    {
        return feof($this->source);
    }

    /**
     * File must be able to be rewound when the end is reached.
     *
     * @return void
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
     * @throws CSVelte\CSVelte\Exception\InvalidStreamResourceException
     *
     * @return void
     */
    protected function assertStreamExistsAndIsReadable()
    {
        switch (true) {
            case !is_resource($this->source):
                throw new InvalidStreamResourceException('Cannot read from '.$this->name().'. It is either invalid or has been closed.');
        }
    }
}
