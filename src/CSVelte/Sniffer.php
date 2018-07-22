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

use CSVelte\Contract\Streamable;

use CSVelte\Exception\SnifferException;
use CSVelte\Sniffer\SniffDelimiterByConsistency;
use CSVelte\Sniffer\SniffDelimiterByDistribution;
use CSVelte\Sniffer\SniffHeaderByDataType;
use CSVelte\Sniffer\SniffLineTerminatorByCount;
use CSVelte\Sniffer\SniffQuoteAndDelimByAdjacency;
use CSVelte\Sniffer\SniffQuoteStyle;
use Noz\Collection\Collection;
use function Noz\to_array;
use RuntimeException;

use function Noz\collect;
use function Stringy\create as s;

class Sniffer
{
    /** CSV data sample size - sniffer will use this many bytes to make its determinations */
    const SAMPLE_SIZE = 2500;

    /**
     * ASCII character codes for "invisibles".
     */
    const HORIZONTAL_TAB  = 9;
    const LINE_FEED       = 10;
    const CARRIAGE_RETURN = 13;
    const SPACE           = 32;

    /**
     * @var array A list of possible delimiters to check for (in order of preference)
     */
    protected $delims = [',', "\t", ';', '|', ':', '-', '_', '#', '/', '\\', '$', '+', '=', '&', '@'];

    /**
     * @var Streamable A stream of the sample data
     */
    protected $stream;

    /**
     * Sniffer constructor.
     *
     * @param Streamable $stream The data to sniff
     * @param array $delims A list of possible delimiter characters in order of preference
     */
    public function __construct(Streamable $stream, $delims = null)
    {
        $this->stream = $stream;
        if (!is_null($delims)) {
            $this->setPossibleDelimiters($delims);
        }
    }

    /**
     * Set possible delimiter characters
     *
     * @param array $delims A list of possible delimiter characters
     *
     * @return self
     */
    public function setPossibleDelimiters(array $delims)
    {
        $this->delims = collect($delims)
            ->filter(function($val) {
                return s($val)->length() == 1;
            })
            ->values()
            ->toArray();

        return $this;
    }

    /**
     * Get list of possible delimiter characters
     *
     * @return array
     */
    public function getPossibleDelimiters()
    {
        return $this->delims;
    }

    /**
     * Sniff CSV data (determine its dialect)
     *
     * Since CSV is less a format than a collection of similar formats, you can never be certain how a particular CSV
     * file is formatted. This method inspects CSV data and returns its "dialect", an object that can be passed to
     * either a `CSVelte\Reader` or `CSVelte\Writer` object to tell it what "dialect" of CSV to use.
     *
     * @todo look into which other Dialect attributes you can sniff for
     *
     * @return Dialect
     */
    public function sniff()
    {
        $sample = $this->stream->read(static::SAMPLE_SIZE);
        $lineTerminator = $this->sniffLineTerminator($sample);
        try {
            list($quoteChar, $delimiter) = $this->sniffQuoteAndDelim($sample, $lineTerminator);
        } catch (SnifferException $e) {
            if ($e->getCode() !== SnifferException::ERR_QUOTE_AND_DELIM) {
                throw $e;
            }
            $quoteChar = '"';
            $delimiter = $this->sniffDelimiter($sample, $lineTerminator);
        }
        /**
         * @todo Should this be null? Because doubleQuote = true means this = null
         */
        $escapeChar = '\\';
        $quoteStyle = $this->sniffQuotingStyle($sample, $delimiter, $lineTerminator);
        $header     = $this->sniffHasHeader($sample, $delimiter, $lineTerminator);
        $encoding   = s($sample)->getEncoding();

        return new Dialect(compact('quoteChar', 'escapeChar', 'delimiter', 'lineTerminator', 'quoteStyle', 'header', 'encoding'));
    }

    /**
     * Sniff sample data for line terminator character
     *
     * @param string $data The sample data
     *
     * @return string
     */
    protected function sniffLineTerminator($data)
    {
        $sniffer = new SniffLineTerminatorByCount();
        return $sniffer->sniff($data);
    }

    /**
     * Sniff quote and delimiter chars
     *
     * The best way to determine quote and delimiter characters is when columns
     * are quoted, often you can seek out a pattern of delim, quote, stuff, quote, delim
     * but this only works if you have quoted columns. If you don't you have to
     * determine these characters some other way... (see lickDelimiter).
     *
     * @throws SnifferException
     *
     * @param string $data The data to analyze
     * @param string $lineTerminator The line terminator char/sequence
     *
     * @return array A two-row array containing quotechar, delimchar
     */
    protected function sniffQuoteAndDelim($data, $lineTerminator)
    {
        $sniffer = new SniffQuoteAndDelimByAdjacency(compact('lineTerminator'));
        return $sniffer->sniff($data);
    }

    protected function sniffDelimiter($data, $lineTerminator)
    {
        $delimiters = $this->getPossibleDelimiters();
        $consistency = new SniffDelimiterByConsistency(compact('lineTerminator', 'delimiters'));
        $winners = $consistency->sniff($data);
        if (count($winners) > 1) {
            $delimiters = $winners;
            return (new SniffDelimiterByDistribution(compact('lineTerminator', 'delimiters')))
                ->sniff($data);
        }
        return current($winners);
    }

    protected function sniffQuotingStyle($data, $delimiter, $lineTerminator)
    {
        $sniffer = new SniffQuoteStyle(compact( 'lineTerminator', 'delimiter'));
        return $sniffer->sniff($data);
    }

    protected function sniffHasHeader($data, $delimiter, $lineTerminator)
    {
        $sniffer = new SniffHeaderByDataType(compact(  'lineTerminator', 'delimiter'));
        return $sniffer->sniff($data);
    }
}
