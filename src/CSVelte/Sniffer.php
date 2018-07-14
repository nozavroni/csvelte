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
use CSVelte\Sniffer\SniffLineTerminatorByCount;
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
        /**
         * @var array An array of pattern matches
         */
        $matches = null;
        /**
         * @var array An array of patterns (regex)
         */
        $patterns = [];
        // delim can be anything but line breaks, quotes, alphanumeric, underscore, backslash, or any type of spaces
        $antidelims = implode(["\r", "\n", "\w", preg_quote('"', '/'), preg_quote("'", '/'), preg_quote(chr(self::SPACE), '/')]);
        $delim      = "(?P<delim>[^{$antidelims}])";
        $quote      = "(?P<quoteChar>\"|'|`)"; // @todo I think MS Excel uses some strange encoding for fancy open/close quotes
        $patterns[] = "/{$delim} ?{$quote}.*?\2\1/ms"; // ,"something", - anything but whitespace or quotes followed by a possible space followed by a quote followed by anything followed by same quote, followed by same anything but whitespace
        $patterns[] = "/(?:^|{$lineTerminator}){$quote}.*?\1{$delim} ?/ms"; // 'something', - beginning of line or line break, followed by quote followed by anything followed by quote followed by anything but whitespace or quotes
        $patterns[] = "/{$delim} ?{$quote}.*?\2(?:^|{$lineTerminator})/ms"; // ,'something' - anything but whitespace or quote followed by possible space followed by quote followed by anything followed by quote, followed by end of line
        $patterns[] = "/(?:^|{$lineTerminator}){$quote}.*?\2(?:$|{$lineTerminator})/ms"; // 'something' - beginning of line followed by quote followed by anything followed by quote followed by same quote followed by end of line
        foreach ($patterns as $pattern) {
            // @todo I had to add the error suppression char here because it was
            //     causing undefined offset errors with certain data sets. strange...
            if (@preg_match_all($pattern, $data, $matches) && $matches) {
                break;
            }
        }
        if ($matches) {
            $qcad = collect($matches)->kintersect(array_flip(['quoteChar', 'delim']));
            try {
                return $qcad->map(function($val) {
                    return collect($val)->frequency()->sort()->reverse()->getKeyAt(1);
                    })
                    ->ksort()
                    ->reverse()
                    ->values()
                    ->toArray();
            } catch (RuntimeException $e) {
                // eat this exception and let the taster exception below be thrown instead...
            }
        }
        throw new SnifferException('quoteChar and delimiter cannot be determined', SnifferException::ERR_QUOTE_AND_DELIM);
    }

    /**
     * @todo To make this class more oop and test-friendly, implement strategy pattern here with each delim sniffing method implemented in its own strategy class.
     */
    protected function sniffDelimiter($data, $eol)
    {
        // build a table of characters and their frequencies for each line. We
        // will use this frequency table to then build a table of frequencies of
        // each frequency (in 10 lines, "tab" occurred 5 times on 7 of those
        // lines, 6 times on 2 lines, and 7 times on 1 line)
        // @todo it would probably make for more consistent results if you popped the last line since it will most likely be truncated due to the arbitrary nature of the sample size
        $lines = collect(explode($eol, $this->removeQuotedStrings($data)));
        $frequencies = $lines->map(function($line) use ($eol) {
            $preferred = array_flip($this->getPossibleDelimiters());
            return collect($preferred)
                ->map(function() { return 0; })
                ->merge(collect(s($line)->chars())->frequency()->kintersect($preferred))
                ->toArray();
        });

        // now determine the mode for each char to decide the "expected" amount
        // of times a char (possible delim) will occur on each line...
        $modes = collect($this->getPossibleDelimiters())
            ->flip()
            ->map(function($freq, $delim) use ($frequencies) {
                return $frequencies->getColumn($delim)->mode();
            })
            ->filter();

        /** @var Collection $consistencies */
        $consistencies = $frequencies->fold(function(Collection $accum, $freq, $line_no) use ($modes) {

            $modes->each(function($expected, $char) use ($accum, $freq) {
                /** @var Collection $freq */
                if (collect($freq)->get($char) == $expected) {
                    $matches = $accum->get($char, 0);
                    $accum->set($char, ++$matches);
                }
            });
            return $accum;

        }, new Collection)
            ->sort()
            ->reverse();

        $winners = $consistencies->filter(function($freq) use ($consistencies) {
            return $freq === $consistencies->max();
        });

        if ($winners->count() == 1) {
            return $consistencies->getKeyAt(1);
        }

        /**
         * @todo Add a method here to figure out where duplicate best-match
         *     delimiter(s) fall within each line and then, depending on
         *     which one has the best distribution, return that one.
         */
        $decision = $winners->keys()->toArray();
        try {
            return $this->guessDelimByDistribution($data, $decision, $eol);
        } catch (SnifferException $e) {
            // if we still can't come to a decision, just return the first one on the preferred list

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

    /**
     * Replaces all quoted columns with a blank string. I was using this method
     * to prevent explode() from incorrectly splitting at delimiters and newlines
     * within quotes when parsing a file. But this was before I wrote the
     * replaceQuotedSpecialChars method which (at least to me) makes more sense.
     *
     * @param string $data The string to replace quoted strings within
     *
     * @return string The input string with quoted strings removed
     */
    protected function removeQuotedStrings($data)
    {
        return preg_replace($pattern = '/(["\'])(?:(?=(\\\\?))\2.)*?\1/sm', $replace = '', $data);
    }
}
