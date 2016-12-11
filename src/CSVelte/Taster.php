<?php

/*
 * CSVelte: Slender, elegant CSV for PHP
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @version   {version}
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace CSVelte;

use CSVelte\Collection\AbstractCollection;
use CSVelte\Collection\Collection;
use CSVelte\Collection\MultiCollection;
use CSVelte\Collection\NumericCollection;
use CSVelte\Collection\TabularCollection;
use CSVelte\Contract\Streamable;
use CSVelte\Exception\TasterException;

use DateTime;
use Exception;
use OutOfBoundsException;

use function CSVelte\collect;

/**
 * CSVelte\Taster
 * Given CSV data, Taster will "taste" the data and provide its buest guess at
 * its "flavor". In other words, this class inspects CSV data and attempts to
 * auto-detect various CSV attributes such as line endings, quote characters, etc..
 *
 * @package   CSVelte
 *
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 *
 * @todo      There are a ton of improvements that could be made to this class.
 *            I'll do a refactor on this fella once I get at least one test
 *            passing for each of its public methods.
 * @todo      Should I have a lickEscapeChar method? The python version doesn't
 *            have one. But then why does it even bother including one in its
 *            flavor class?
 * @todo      Examine each of the public methods in this class and determine
 *            whether it makes sense to ask for the data as a param rather than
 *            just pulling it from source. I don't think it makes sense... it
 *            was just easier to write the methods that way during testing.
 * @todo      There are at least portions of this class that could use the
 *            Reader class rather than working directly with data.
 * @todo      Refactor all of the anonymous functions used as callbacks. Rather
 *            than passing $this all over, use $closure->bindTo() instead...
 *            Actually, write a method called getBoundClosure() or something...
 *            maybe even make it a trait I don't know yet. But here it would
 *            allow me to bind any anon function to $this and give me a certain
 *            set of commonly needed values ($delim, $eol, etc.)
 */
class Taster
{
    /**
     * End-of-line constants.
     */
    const EOL_UNIX    = 'lf';
    const EOL_TRS80   = 'cr';
    const EOL_WINDOWS = 'crlf';

    /**
     * ASCII character codes for "invisibles".
     */
    const HORIZONTAL_TAB  = 9;
    const LINE_FEED       = 10;
    const CARRIAGE_RETURN = 13;
    const SPACE           = 32;

    /**
     * Data types -- Used within the lickQuotingStyle method.
     */
    const DATA_NONNUMERIC = 'nonnumeric';
    const DATA_SPECIAL    = 'special';
    const DATA_UNKNOWN    = 'unknown';

    /**
     * Placeholder strings -- hold the place of newlines and delimiters contained
     * within quoted text so that the explode method doesn't split incorrectly.
     */
    const PLACEHOLDER_NEWLINE = '[__NEWLINE__]';
    const PLACEHOLDER_DELIM   = '[__DELIM__]';

    /**
     * Recommended data sample size.
     */
    const SAMPLE_SIZE = 2500;

    /**
     * Column data types -- used within the lickHeader method to determine
     * whether the first row contains different types of data than the rest of
     * the rows (and thus, is likely a header row).
     */
    // +-987
    const TYPE_NUMBER = 'number';
    // +-12.387
    const TYPE_DOUBLE = 'double';
    // I am a string. I can contain all kinds of stuff.
    const TYPE_STRING = 'string';
    // 2010-04-23 04:23:00
    const TYPE_DATETIME = 'datetime';
    // 10-Jul-15, 9/1/2007, April 1st, 2006, etc.
    const TYPE_DATE = 'date';
    // 10:00pm, 5pm, 13:08, etc.
    const TYPE_TIME = 'time';
    // $98.96, ¥12389, £6.08, €87.00
    const TYPE_CURRENCY = 'currency';
    // 12ab44m1n2_asdf
    const TYPE_ALNUM = 'alnum';
    // abababab
    const TYPE_ALPHA = 'alpha';

    /** @var Contract\Streamable The source of data to examine */
    protected $input;

    /** @var string Sample of CSV data to use for tasting (determining CSV flavor) */
    protected $sample;

    /** @var array Possible delimiter characters in (roughly) the order of likelihood */
    protected $delims = [',', "\t", ';', '|', ':', '-', '_', '#', '/', '\\', '$', '+', '=', '&', '@'];

    /**
     * Class constructor--accepts a CSV input source.
     *
     * @param Contract\Streamable The source of CSV data
     *
     * @throws TasterException
     *
     * @todo It may be a good idea to skip the first line or two for the sample
     *     so that the header line(s) don't throw things off (with the exception
     *     of lickHeader() obviously)
     */
    public function __construct(Streamable $input)
    {
        $this->delims = collect($this->delims);
        $this->input = $input;
        if (!$this->sample = $input->read(self::SAMPLE_SIZE)) {
            throw new TasterException('Invalid input, cannot read sample.', TasterException::ERR_INVALID_SAMPLE);
        }
    }

    /**
     * "Invoke" magic method.
     *
     * Called when an object is invoked as if it were a function. So, for instance,
     * This is simply an alias to the lick method.
     *
     * @throws TasterException
     *
     * @return Flavor A flavor object
     */
    public function __invoke()
    {
        return $this->lick();
    }

    /**
     * Examine the input source and determine what "Flavor" of CSV it contains.
     * The CSV format, while having an RFC (https://tools.ietf.org/html/rfc4180),
     * doesn't necessarily always conform to it. And it doesn't provide meta such as the delimiting character, quote character, or what types of data are quoted.
     * such as the delimiting character, quote character, or what types of data are quoted.
     * are quoted.
     *
     * @throws TasterException
     *
     * @return Flavor The metadata that the CSV format doesn't provide
     *
     * @todo Implement a lickQuote method for when lickQuoteAndDelim method fails
     * @todo Should there bea lickEscapeChar method? the python module that inspired
     *     this library doesn't include one...
     * @todo This should cache the results and only regenerate if $this->sample
     *     changes (or $this->input)
     */
    public function lick()
    {
        $lineTerminator = $this->lickLineEndings();
        try {
            list($quoteChar, $delimiter) = $this->lickQuoteAndDelim();
        } catch (TasterException $e) {
            if ($e->getCode() !== TasterException::ERR_QUOTE_AND_DELIM) {
                throw $e;
            }
            $quoteChar = '"';
            $delimiter = $this->lickDelimiter($lineTerminator);
        }
        /**
         * @todo Should this be null? Because doubleQuote = true means this = null
         */
        $escapeChar = '\\';
        $quoteStyle = $this->lickQuotingStyle($delimiter, $lineTerminator);
        $header     = $this->lickHeader($delimiter, $lineTerminator);

        return new Flavor(compact('quoteChar', 'escapeChar', 'delimiter', 'lineTerminator', 'quoteStyle', 'header'));
    }

    /**
     * Examines the contents of the CSV data to make a determination of whether
     * or not it contains a header row. To make this determination, it creates
     * an array of each column's (in each row)'s data type and length and then
     * compares them. If all of the rows except the header look similar, it will
     * return true. This is only a guess though. There is no programmatic way to
     * determine 100% whether a CSV file has a header. The format does not
     * provide metadata such as that.
     *
     * @param string $delim The CSV data's delimiting char (can be a variety of chars but)
     *                      typically is either a comma or a tab, sometimes a pipe)
     * @param string $eol   The CSV data's end-of-line char(s) (\n \r or \r\n)
     *
     * @return bool True if the data (most likely) contains a header row
     *
     * @todo This method needs a total refactor. It's not necessary to loop twice
     *     You could get away with one loop and that would allow for me to do
     *     something like only examining enough rows to get to a particular
     *     "hasHeader" score (+-100 for instance) & then just return true|false
     * @todo Also, break out of the first loop after a certain (perhaps even a
     *     configurable) amount of lines (you only need to examine so much data )
     *     to reliably make a determination and this is an expensive method)
     * @todo I could remove the need for quote, delim, and eol by "licking" the
     *     data sample provided in the first argument. Also, I could actually
     *     create a Reader object to read the data here.
     */
    public function lickHeader($delim, $eol)
    {
        // this will be filled with the type and length of each column and each row
        $types = new TabularCollection();

        // callback to build the aforementioned collection
        $buildTypes = function ($line, $line_no) use ($types, $delim, $eol) {

            if ($line_no > 2) return;
            $line = str_replace(self::PLACEHOLDER_NEWLINE, $eol, $line);
            $getType = function ($field, $colpos) use ($types, $line, $line_no, $delim) {
                $field = str_replace(self::PLACEHOLDER_DELIM, $delim, $field);
                $fieldMeta = [
                    "value" => $field,
                    "type" => $this->lickType($this->unQuote($field)),
                    "length" => strlen($field),
                ];
                // @todo TabularCollection should have a way to set a value using [row,column]
                try {
                    $row = $types->get($line_no);
                } catch (OutOfBoundsException $e) {
                    $row = [];
                }
                $row[$colpos] = $fieldMeta;
                $types->set($line_no, $row);
            };
            collect(explode($delim, $line))->walk($getType->bindTo($this));

        };

        collect(explode(
            $eol,
            $this->replaceQuotedSpecialChars($this->sample, $delim)
        ))
        ->walk($buildTypes->bindTo($this));

        $hasHeader = new NumericCollection();
        $possibleHeader = collect($types->shift());
        $types->walk(function (AbstractCollection $row) use ($hasHeader, $possibleHeader) {
            $row->walk(function (AbstractCollection $fieldMeta, $col_no) use ($hasHeader, $possibleHeader) {
                try {
                    $col = collect($possibleHeader->get($col_no, null, true));
                    if ($fieldMeta->get('type') == self::TYPE_STRING) {
                        // use length
                        if ($fieldMeta->get('length') != $col->get('length')) {
                            $hasHeader->push(1);
                        } else {
                            $hasHeader->push(-1);
                        }
                    } else {
                        // use data type
                        if ($fieldMeta->get('type') != $col->get('type')) {
                            $hasHeader->push(1);
                        } else {
                            $hasHeader->push(-1);
                        }
                    }
                } catch (OutOfBoundsException $e) {
                    // failure...
                    return;
                }
            });
        });

        return $hasHeader->sum() > 0;
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
     *
     * @todo Replace code that uses this method with the replaceQuotedSpecialChars
     *     method instead. I think it's cleaner.
     */
    protected function removeQuotedStrings($data)
    {
        return preg_replace($pattern = '/(["\'])(?:(?=(\\\\?))\2.)*?\1/sm', $replace = '', $data);
    }

    /**
     * Examine the input source to determine which character(s) are being used
     * as the end-of-line character.
     *
     * @return string The end-of-line char for the input data
     * @credit pulled from stackoverflow thread *tips hat to username "Harm"*
     *
     * @todo This should throw an exception if it cannot determine the line ending
     * @todo I probably will make this method protected when I'm done with testing...
     * @todo If there is any way for this method to fail (for instance if a file )
     *       is totally empty or contains no line breaks), then it needs to throw
     *       a relevant TasterException
     * @todo Use replaceQuotedSpecialChars rather than removeQuotedStrings()
     */
    protected function lickLineEndings()
    {
        $str  = $this->removeQuotedStrings($this->sample);
        $eols = [
            self::EOL_WINDOWS => "\r\n",  // 0x0D - 0x0A - Windows, DOS OS/2
            self::EOL_UNIX    => "\n",    // 0x0A -      - Unix, OSX
            self::EOL_TRS80   => "\r",    // 0x0D -      - Apple ][, TRS80
        ];

        $curCount = 0;
        // @todo This should return a default maybe?
        $curEol = PHP_EOL;
        foreach ($eols as $k => $eol) {
            if (($count = substr_count($str, $eol)) > $curCount) {
                $curCount = $count;
                $curEol   = $eol;
            }
        }

        return $curEol;
    }

    /**
     * The best way to determine quote and delimiter characters is when columns
     * are quoted, often you can seek out a pattern of delim, quote, stuff, quote, delim
     * but this only works if you have quoted columns. If you don't you have to
     * determine these characters some other way... (see lickDelimiter).
     *
     * @throws TasterException
     *
     * @return array A two-row array containing quotechar, delimchar
     *
     * @todo make protected
     * @todo This should throw an exception if it cannot determine the delimiter
     *     this way.
     * @todo This should check for any line endings not just \n
     */
    protected function lickQuoteAndDelim()
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
        $delim      = '(?P<delim>[^' . $antidelims . '])';
        $quote      = '(?P<quoteChar>"|\'|`)'; // @todo I think MS Excel uses some strange encoding for fancy open/close quotes
        $patterns[] = '/' . $delim . ' ?' . $quote . '.*?\2\1/ms'; // ,"something", - anything but whitespace or quotes followed by a possible space followed by a quote followed by anything followed by same quote, followed by same anything but whitespace
        $patterns[] = '/(?:^|\n)' . $quote . '.*?\1' . $delim . ' ?/ms'; // 'something', - beginning of line or line break, followed by quote followed by anything followed by quote followed by anything but whitespace or quotes
        $patterns[] = '/' . $delim . ' ?' . $quote . '.*?\2(?:^|\n)/ms'; // ,'something' - anything but whitespace or quote followed by possible space followed by quote followed by anything followed by quote, followed by end of line
        $patterns[] = '/(?:^|\n)' . $quote . '.*?\2(?:$|\n)/ms'; // 'something' - beginning of line followed by quote followed by anything followed by quote followed by same quote followed by end of line
        foreach ($patterns as $pattern) {
            // @todo I had to add the error suppression char here because it was
            //     causing undefined offset errors with certain data sets. strange...
            if (@preg_match_all($pattern, $this->sample, $matches) && $matches) {
                break;
            }
        }
        if ($matches) {
            $qcad = array_intersect_key($matches, array_flip(['quoteChar','delim']));
            if (!empty($matches['quoteChar']) && !empty($matches['delim'])) {
                try {
                    return [
                        collect($qcad['quoteChar'])->frequency()->sort()->reverse()->getKeyAtPosition(0),
                        collect($qcad['delim'])->frequency()->sort()->reverse()->getKeyAtPosition(0),
                    ];
                } catch (OutOfBoundsException $e) {
                    // eat this exception and let the taster exception below be thrown instead...
                }
            }
        }
        throw new TasterException('quoteChar and delimiter cannot be determined', TasterException::ERR_QUOTE_AND_DELIM);
    }

    /**
     * Take a list of likely delimiter characters and find the one that occurs
     * the most consistent amount of times within the provided data.
     *
     * @param string $eol The character(s) used for newlines
     *
     * @return string One of four Flavor::QUOTING_* constants
     *
     * @see Flavor for possible quote style constants
     *
     * @todo Refactor this method--It needs more thorough testing against a wider
     *     variety of CSV data to be sure it works reliably. And I'm sure there
     *     are many performance and logic improvements that could be made. This
     *     is essentially a first draft.
     * @todo Can't use replaceQuotedSpecialChars rather than removeQuotedStrings
     *     because the former requires u to know the delimiter
     */
    protected function lickDelimiter($eol = "\n")
    {
        $frequencies   = collect();
        $consistencies = new NumericCollection();

        // build a table of characters and their frequencies for each line. We
        // will use this frequency table to then build a table of frequencies of
        // each frequency (in 10 lines, "tab" occurred 5 times on 7 of those
        // lines, 6 times on 2 lines, and 7 times on 1 line)
        collect(explode($eol, $this->removeQuotedStrings($this->sample)))
            ->walk(function ($line, $line_no) use ($frequencies) {
                collect(str_split($line))
                    ->filter(function ($c) {
                        return collect($this->delims)->contains($c);
                    })
                    ->frequency()
                    ->sort()
                    ->reverse()
                    ->walk(function ($count, $char) use ($frequencies, $line_no) {
                        try {
                            $char_counts = $frequencies->get($char, null, true);
                        } catch (OutOfBoundsException $e) {
                            $char_counts = [];
                        }
                        $char_counts[$line_no] = $count;
                        $frequencies->set($char, $char_counts);
                    });
            })
            // the above only finds frequencies for characters if they exist in
            // a given line. This will go back and fill in zeroes where a char
            // didn't occur at all in a given line (needed to determine mode)
            ->walk(function ($line, $line_no) use ($frequencies) {
                $frequencies->walk(function ($counts, $char) use ($line_no, $frequencies) {
                    try {
                        $char_counts = $frequencies->get($char, null, true);
                    } catch (OutOfBoundsException $e) {
                        $char_counts = [];
                    }
                    if (!array_key_exists($line_no, $char_counts)) {
                        $char_counts[$line_no] = 0;
                    }
                    $frequencies->set($char, $char_counts);
                });
            });

        // now determine the mode for each char to decide the "expected" amount
        // of times a char (possible delim) will occur on each line...
        $freqs = $frequencies;
        $modes = new NumericCollection([]);
        foreach ($freqs as $char => $freq) {
            $modes->set($char, collect($freq)->mode());
        }
        $freqs->walk(function ($f, $chr) use ($modes, $consistencies) {
            collect($f)->walk(function ($num) use ($modes, $chr, $consistencies) {
                if ($expected = $modes->get($chr)) {
                    if ($num == $expected) {
                        // met the goal, yay!
                        $cc = $consistencies->get($chr, 0);
                        $consistencies->set($chr, ++$cc);
                    }
                }
            });
        });

        $delims = $consistencies;
        $max    = $delims->max();
        $dups   = $delims->duplicates();
        if ($dups->has($max)) {
            // if more than one candidate, then look at where the character appeared
            // in the data. Was it relatively evenly distributed or was there a
            // specific area that the character tended to appear? Dates will have a
            // consistent format (e.g. 04-23-1986) and so may easily provide a false
            // positive for delimiter. But the dash will be focused in that one area,
            // whereas the comma character is spread out. You can determine this by
            // finding out the number of chars between each occurrence and getting
            // the average. If the average is wildly different than any given distance
            // than bingo you probably aren't working with a delimiter there...

            // another option to find the delimiter if there is a tie, is to build
            // a table of character position within each line. Then use that to
            // determine if one character is consistently in the same position or
            // at least the same general area. Use the delimiter that is the most
            // consistent in that way...

            /**
             * @todo Add a method here to figure out where duplicate best-match
             *     delimiter(s) fall within each line and then, depending on
             *     which one has the best distribution, return that one.
             */
            $decision = $dups->get($max);
            try {
                return $this->guessDelimByDistribution($decision, $eol);
            } catch (TasterException $e) {
                // if somehow we STILL can't come to a consensus, then fall back to a
                 // "preferred delimiters" list...
                 foreach ($this->delims as $key => $val) {
                     if ($delim = array_search($val, $decision)) {
                         return $delim;
                     }
                 }
            }
        }

        return $delims
            ->sort()
            ->reverse()
            ->getKeyAtPosition(0);
    }

    /**
     * Compare positional consistency of several characters to determine the
     * probable delimiter character. The idea behind this is that the delimiter
     * character is likely more consistently distributed than false-positive
     * delimiter characters produced by lickDelimiter(). For instance, consider
     * a series of rows similar to the following:.
     *
     * 1,luke,visinoni,luke.visinoni@gmail.com,(530) 413-3076,04-23-1986
     *
     * The lickDelimiter() method will often not be able to determine whether the
     * delimiter is a comma or a dash because they occur the same number of times
     * on just about every line (5 for comma, 3 for dash). The difference is
     * obvious to you, no doubt. But us humans are pattern-recognition machines!
     * The difference between the comma and the dash are that the comma is dist-
     * ributed almost evenly throughout the line. The dash characters occur
     * entirely at the end of the line. This method accepts any number of possible
     * delimiter characters and returns the one that is distributed
     *
     * If delim character cannot be determined by lickQuoteAndDelim(), taster
     * tries lickDelimiter(). When that method runs into a tie, it will use this
     * as a tie-breaker.
     *
     * @param array  $delims Possible delimiter characters (method chooses from
     *                       this array of characters)
     * @param string $eol    The end-of-line character (or set of characters)
     *
     * @throws TasterException
     *
     * @return string The probable delimiter character
     */
    protected function guessDelimByDistribution(array $delims, $eol = "\n")
    {
        try {
            // @todo Write a method that does this...
            $lines = collect(explode($eol, $this->removeQuotedStrings($this->sample)));

            return $delims[collect($delims)->map(function ($delim) use (&$distrib, $lines) {
                $linedist = collect();
                $lines->walk(function ($line, $line_no) use (&$linedist, $delim) {
                    if (!strlen($line)) {
                        return;
                    }
                    $sectstot = 10;
                    $sectlen = (int) (strlen($line) / $sectstot);
                    $sections = collect(str_split($line, $sectlen))
                        ->map(function ($section) use ($delim) {
                            return substr_count($section, $delim);
                        })
                        ->filter(function ($count) {
                            return (bool) $count;
                        });
                    if (is_numeric($count = $sections->count())) {
                        $linedist->set($line_no, $count / $sectstot);
                    }
                });

                return $linedist;
            })->map(function ($dists) {
                return $dists->average();
            })->sort()
              ->reverse()
              ->getKeyAtPosition(0)];
        } catch (Exception $e) {
            throw new TasterException('delimiter cannot be determined by distribution', TasterException::ERR_DELIMITER);
        }
    }

    /**
     * Determine the "style" of data quoting. The CSV format, while having an RFC
     * (https://tools.ietf.org/html/rfc4180), doesn't necessarily always conform
     * to it. And it doesn't provide metadata such as the delimiting character,
     * quote character, or what types of data are quoted. So this method makes a
     * logical guess by finding which columns have been quoted (if any) and
     * examining their data type. Most often, CSV files will only use quotes
     * around columns that contain special characters such as the dilimiter,
     * the quoting character, newlines, etc. (we refer to this style as )
     * QUOTE_MINIMAL), but some quote all columns that contain nonnumeric data
     * (QUOTE_NONNUMERIC). Then there are CSV files that quote all columns
     * (QUOTE_ALL) and those that quote none (QUOTE_NONE).
     *
     * @param string $delim The character used as the column delimiter
     * @param string $eol   The character used for newlines
     *
     * @return string One of four "QUOTING_" constants defined above--see this
     *                method's description for more info.
     *
     * @todo Refactor this method--It needs more thorough testing against a wider
     *     variety of CSV data to be sure it works reliably. And I'm sure there
     *     are many performance and logic improvements that could be made. This
     *     is essentially a first draft.
     */
    protected function lickQuotingStyle($delim, $eol)
    {
        $quoting_styles = collect([
            Flavor::QUOTE_ALL        => true,
            Flavor::QUOTE_NONE       => true,
            Flavor::QUOTE_MINIMAL    => true,
            Flavor::QUOTE_NONNUMERIC => true,
        ]);

        $lines = collect(explode($eol, $this->replaceQuotedSpecialChars($this->sample, $delim)));
        $freq  = collect()
            ->set('quoted', collect())
            ->set('unquoted', collect());

        // walk through each line from the data sample to determine which fields
        // are quoted and which aren't
        $qsFunc = function ($line) use (&$quoting_styles, &$freq, $eol, $delim) {
            $line     = str_replace(self::PLACEHOLDER_NEWLINE, $eol, $line);
            $qnqaFunc = function ($field) use (&$quoting_styles, &$freq, $delim) {
                $field = str_replace(self::PLACEHOLDER_DELIM, $delim, $field);
                if ($this->isQuoted($field)) {
                    $field = $this->unQuote($field);
                    $freq->get('quoted')->push($this->lickDataType($field));
                    // since we know there's at least one quoted field,
                    // QUOTE_NONE can be ruled out
                    $quoting_styles->set(Flavor::QUOTE_NONE, false);
                } else {
                    $freq->get('unquoted')->push($this->lickDataType($field));
                    // since we know there's at least one unquoted field,
                    // QUOTE_ALL can be ruled out
                    $quoting_styles->set(Flavor::QUOTE_ALL, false);
                }
            };
            collect(explode($delim, $line))
                ->walk($qnqaFunc->bindTo($this));
        };
        $lines->walk($qsFunc->bindTo($this));

        $types          = $freq->get('quoted')->unique();
        $quoting_styles = $quoting_styles->filter(function ($val) {
            return (bool) $val;
        });
        // if quoting_styles still has QUOTE_ALL or QUOTE_NONE, then return
        // whichever of them it is, we don't need to do anything else
        if ($quoting_styles->has(Flavor::QUOTE_ALL)) {
            return Flavor::QUOTE_ALL;
        }
        if ($quoting_styles->has(Flavor::QUOTE_NONE)) {
            return Flavor::QUOTE_NONE;
        }
        if (count($types) == 1) {
            $style = $types->getValueAtPosition(0);
            if ($quoting_styles->has($style)) {
                return $style;
            }
        } else {
            if ($types->contains(self::DATA_NONNUMERIC)) {
                // allow for a SMALL amount of error here
                $counts = collect([self::DATA_SPECIAL => 0, self::DATA_NONNUMERIC => 0]);
                $freq->get('quoted')->walk(function ($type) use (&$counts) {
                    $counts->increment($type);
                });
                // @todo is all this even necessary? seems unnecessary to me...
                if ($most = $counts->max()) {
                    $least      = $counts->min();
                    $err_margin = $least / $most;
                    if ($err_margin < 1) {
                        return Flavor::QUOTE_NONNUMERIC;
                    }
                }
            }
        }

        return Flavor::QUOTE_MINIMAL;
    }

    /**
     * Remove quotes around a piece of text (if there are any).
     *
     * @param string $data The data to "unquote"
     *
     * @return string The data passed in, only with quotes stripped (off the edges)
     */
    protected function unQuote($data)
    {
        return preg_replace('/^(["\'])(.*)\1$/', '\2', $data);
    }

    /**
     * Determine whether a particular string of data has quotes around it.
     *
     * @param string $data The data to check
     *
     * @return bool Whether the data is quoted or not
     */
    protected function isQuoted($data)
    {
        return preg_match('/^([\'"])[^\1]*\1$/', $data);
    }

    /**
     * Determine what type of data is contained within a variable
     * Possible types:
     *     - nonnumeric - only numbers
     *     - special - contains characters that could potentially need to be quoted (possible delimiter characters)
     *     - unknown - everything else
     * This method is really only used within the "lickQuotingStyle" method to
     * help determine whether a particular column has been quoted due to it being
     * nonnumeric or because it has some special character in it such as a delimiter
     * or newline or quote.
     *
     * @param string $data The data to determine the type of
     *
     * @return string The type of data (one of the "DATA_" constants above)
     *
     * @todo I could probably eliminate this method and use an anonymous function
     *     instead. It isn't used anywhere else and its name could be misleading.
     *     Especially since I also have a lickType method that is used within the
     *     lickHeader method.
     */
    protected function lickDataType($data)
    {
        // @todo make this check for only the quote and delim that are actually being used
        // that will make the guess more accurate
        if (preg_match('/[\'",\t\|:;-]/', $data)) {
            return self::DATA_SPECIAL;
        } elseif (preg_match('/[^0-9]/', $data)) {
            return self::DATA_NONNUMERIC;
        }

        return self::DATA_UNKNOWN;
    }

    /**
     * Replace all instances of newlines and whatever character you specify (as
     * the delimiter) that are contained within quoted text. The replacements are
     * simply a special placeholder string. This is done so that I can use the
     * very unsmart "explode" function and not have to worry about it exploding
     * on delimiters or newlines within quotes. Once I have exploded, I typically
     * sub back in the real characters before doing anything else. Although
     * currently there is no dedicated method for doing so I just use str_replace.
     *
     * @param string $data  The string to do the replacements on
     * @param string $delim The delimiter character to replace
     *
     * @return string The data with replacements performed
     *
     * @todo I could probably pass in (maybe optionally) the newline character I
     *     want to replace as well. I'll do that if I need to.
     */
    protected function replaceQuotedSpecialChars($data, $delim)
    {
        return preg_replace_callback('/([\'"])(.*)\1/imsU', function ($matches) use ($delim) {
            $ret = preg_replace("/([\r\n])/", self::PLACEHOLDER_NEWLINE, $matches[0]);
            $ret = str_replace($delim, self::PLACEHOLDER_DELIM, $ret);

            return $ret;
        }, $data);
    }

    /**
     * Determine the "type" of a particular string of data. Used for the lickHeader
     * method to assign a type to each column to try to determine whether the
     * first for is different than a consistent column type.
     *
     * @todo As I'm writing this method I'm beginning ot realize how expensive
     * the lickHeader method is going to end up being since it has to apply all
     * these regexes (potentially) to every column. I may end up writing a much
     * simpler type-checking method than this if it proves to be too expensive
     * to be practical.
     *
     * @param string $data The string of data to check the type of
     *
     * @return string One of the TYPE_ string constants above
     */
    protected function lickType($data)
    {
        if (preg_match('/^[+-]?[\d\.]+$/', $data)) {
            return self::TYPE_NUMBER;
        } elseif (preg_match('/^[+-]?[\d]+\.[\d]+$/', $data)) {
            return self::TYPE_DOUBLE;
        } elseif (preg_match('/^[+-]?[¥£€$]\d+(\.\d+)$/', $data)) {
            return self::TYPE_CURRENCY;
        } elseif (preg_match('/^[a-zA-Z]+$/', $data)) {
            return self::TYPE_ALPHA;
        }
        try {
            $year  = '([01][0-9])?[0-9]{2}';
            $month = '([01]?[0-9]|Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)';
            $day   = '[0-3]?[0-9]';
            $sep   = '[\/\.\-]?';
            $time  = '([0-2]?[0-9](:[0-5][0-9]){1,2}(am|pm)?|[01]?[0-9](am|pm))';
            $date  = '(' . $month . $sep . $day . $sep . $year . '|' . $day . $sep . $month . $sep . $year . '|' . $year . $sep . $month . $sep . $day . ')';
            $dt    = new DateTime($data);
            $dt->setTime(0, 0, 0);
            $now = new DateTime();
            $now->setTime(0, 0, 0);
            $diff     = $dt->diff($now);
            $diffDays = (int) $diff->format('%R%a');
            if ($diffDays === 0) {
                // then this is most likely a time string...
                    if (preg_match("/^{$time}$/i", $data)) {
                        return self::TYPE_TIME;
                    }
            }
            if (preg_match("/^{$date}$/i", $data)) {
                return self::TYPE_DATE;
            } elseif (preg_match("/^{$date} {$time}$/i")) {
                return self::TYPE_DATETIME;
            }
        } catch (\Exception $e) {
            // now go on checking remaining types
                if (preg_match('/^\w+$/', $data)) {
                    return self::TYPE_ALNUM;
                }
        }
        
        return self::TYPE_STRING;
    }
}
