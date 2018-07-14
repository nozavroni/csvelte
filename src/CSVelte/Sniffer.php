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
use CSVelte\Sniffer\SniffLineTerminatorByCount;
use CSVelte\Sniffer\SniffQuoteAndDelimByAdjacency;
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
        $quoteStyle = $this->sniffQuotingStyle($delimiter, $lineTerminator);
        $header     = $this->sniffHeader($delimiter, $lineTerminator);
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
    public function sniffLineTerminator($data)
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
     *
     * @todo This should throw an exception if it cannot determine the delimiter this way.
     * @todo This should check for any line endings not just \n
     */
    protected function sniffQuoteAndDelim($data, $lineTerminator)
    {
        $sniffer = new SniffQuoteAndDelimByAdjacency(compact('lineTerminator'));
        return $sniffer->sniff($data);
    }

    /**
     * @todo To make this class more oop and test-friendly, implement strategy pattern here with each delim sniffing method implemented in its own strategy class.
     */
    protected function sniffDelimiter($data, $eol)
    {
        $consistency = new SniffDelimiterByConsistency([
            'lineTerminator' => $eol,
            'delimiters' => $this->getPossibleDelimiters()
        ]);
        $winners = $consistency->sniff($data);
        if (count($winners) > 1) {
            /**
             * @todo Add a method here to figure out where duplicate best-match
             *     delimiter(s) fall within each line and then, depending on
             *     which one has the best distribution, return that one.
             */
            try {
                return $this->guessDelimByDistribution($data, $winners, $eol);
            } catch (SnifferException $e) {
                // if we still can't come to a decision, just return the first one on the preferred list

            }
        }
    }

    public function guessDelimByDistribution($data, $delims, $eol)
    {
        $lines = collect(explode($eol, $this->removeQuotedStrings($data)));
        return collect($delims)->flip()->map(function($x, $char) use ($lines) {

            // standard deviation
            $sd = $lines->map(function($line, $line_no) use ($char) {
                $delimited = collect(s($line)->split($char))
                    ->map(function($str) {
                        return $str->length();
                    });
                // standard deviation
                $avg = $delimited->average();
                return sqrt($delimited->fold(function($d, $len) use ($avg) {
                    return $d->add(pow($len - $avg, 2));
                }, new Collection)
                ->sum() / $delimited->count());
            });
            return $sd->average();

        })
            ->sort()
            ->getKeyAt(1);
    }

    protected function sniffQuotingStyle($delimiter, $eols)
    {
        return Dialect::QUOTE_MINIMAL;
    }

    protected function sniffHeader($delimiter, $eols)
    {
        return true;
    }
}
