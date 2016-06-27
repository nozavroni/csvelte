<?php namespace CSVelte;

use Carbon\Carbon;
use CSVelte\Input\InputInterface;
use CSVelte\Exception\TasteQuoteAndDelimException;
use CSVelte\Exception\TasteDelimiterException;

/**
 * CSVelte\Taster
 * Given CSV data, Taster will "taste" the data and provide its buest guess at
 * its "flavor". In other words, this class inspects CSV data and attempts to
 * auto-detect various CSV attributes such as line endings, quote characters, etc..
 *
 * @package   CSVelte
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
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
 */
class Taster
{
    /**
     * End-of-line constants
     */
    const EOL_UNIX    = 'lf';
    const EOL_TRS80   = 'cr';
    const EOL_WINDOWS = 'crlf';

    /**
     * ASCII character codes for "invisibles"
     */
    const HORIZONTAL_TAB = 9;
    const LINE_FEED = 10;
    const CARRIAGE_RETURN = 13;
    const SPACE = 32;

    /**
     * Data types -- Used within the lickQuotingStyle method
     */
    const DATA_NONNUMERIC = 'nonnumeric';
    const DATA_SPECIAL = 'special';
    const DATA_UNKNOWN = 'unknown';

    /**
     * Placeholder strings -- hold the place of newlines and delimiters contained
     * within quoted text so that the explode method doesn't split incorrectly
     */
    const PLACEHOLDER_NEWLINE = '[__NEWLINE__]';
    const PLACEHOLDER_DELIM = '[__DELIM__]';

    /**
     * Column data types -- used within the lickHeader method to determine
     * whether the first row contains different types of data than the rest of
     * the rows (and thus, is likely a header row)
     */
    // +-987
    const TYPE_NUMBER = 'number';
    // +-12.387
    const TYPE_DOUBLE = 'double';
    // I am a string. I can contain all kinds of stuff.
    const TYPE_STRING = 'string';
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

    /**
     * @var CSVelte\InputInterface The source of data to examine
     * @access protected
     */
    protected $input;

    /**
     * Class constructor--accepts a CSV input source
     *
     * @param CSVelte\Input\InputInterface The source of CSV data
     * @return void
     * @access public
     */
    public function __construct(InputInterface $input)
    {
        $this->input = $input;
    }

    /**
     * Examine the input source and determine what "Flavor" of CSV it contains.
     * The CSV format, while having an RFC (https://tools.ietf.org/html/rfc4180),
     * doesn't necessarily always conform to it. And it doesn't provide meta such as the delimiting character, quote character, or what types of data are quoted.
     * such as the delimiting character, quote character, or what types of data are quoted.
     * are quoted.
     *
     * @return CSVelte\Flavor The metadata that the CSV format doesn't provide
     * @access public
     * @todo Implement a lickQuote method for when lickQuoteAndDelim method fails
     * @todo Should there bea lickEscapeChar method? the python module that inspired
     *     this library doesn't include one...
     */
    public function lick()
    {
        $data = $this->input->read(2500);
        try {
            list($quoteChar, $delimiter) = $this->lickQuoteAndDelim();
        } catch (TasteQuoteAndDelimException $e) {
            $quoteChar = '"';
            $delimiter = $this->lickDelimiter();
        }
        $escapeChar = '\\';
        $lineTerminator = $this->lickLineEndings();
        $quoteStyle = $this->lickQuotingStyle($data, $quoteChar, $delimiter, $lineTerminator);
        return new Flavor(compact('quoteChar', 'escapeChar', 'delimiter', 'lineTerminator', 'quoteStyle'));
    }

    /**
     * Replaces all quoted columns with a blank string. I was using this method
     * to prevent explode() from incorrectly splitting at delimiters and newlines
     * within quotes when parsing a file. But this was before I wrote the
     * replaceQuotedSpecialChars method which (at least to me) makes more sense.
     *
     * @param string The string to replace quoted strings within
     * @return string The input string with quoted strings removed
     * @access protected
     * @todo Replace code that uses this method with the replaceQuotedSpecialChars
     *     method instead. I think it's cleaner.
     */
    protected function removeQuotedStrings($data)
    {
        return preg_replace($pattern = '/(["\'])(?:(?=(\\\\?))\2.)*?\1/sm', $replace = '', $data);
    }

    /**
     * Examine the input source to determine which character(s) are being used
     * as the end-of-line character
     *
     * @return char The end-of-line char for the input data
     * @access public
     * @credit pulled from stackoverflow thread *tips hat to username "Harm"*
     * @todo make protected
     * @todo This should throw an exception if it cannot determine the line ending
     * @todo I probably will make this method protected when I'm done with testing...
     * @todo If there is any way for this method to fail (for instance if a file )
     *       is totally empty or contains no line breaks), then it needs to throw
     *       a relevant TasterException
     * @todo Use replaceQuotedSpecialChars rather than removeQuotedStrings()
     */
    public function lickLineEndings()
    {
        $str = $this->removeQuotedStrings($this->input->read(2500));
        $eols = [
            self::EOL_WINDOWS => "\r\n",  // 0x0D - 0x0A - Windows, DOS OS/2
            self::EOL_UNIX    => "\n",    // 0x0A -      - Unix, OSX
            self::EOL_TRS80   => "\r",    // 0x0D -      - Apple ][, TRS80
        ];

        $curCount = 0;
        $curEol = '';
        foreach($eols as $k => $eol) {
            if( ($count = substr_count($str, $eol)) > $curCount) {
                $curCount = $count;
                $curEol = $eol;
            }
        }
        return $curEol;
    }

    /**
     * The best way to determine quote and delimiter characters is when columns
     * are quoted, often you can seek out a pattern of delim, quote, stuff, quote, delim
     * but this only works if you have quoted columns. If you don't you have to
     * determine these characters some other way... (see lickDelimiter)
     *
     * @return array A two-row array containing quotechar, delimchar
     * @access public
     * @todo make protected
     * @todo This should throw an exception if it cannot determine the delimiter
     *     this way.
     */
    public function lickQuoteAndDelim()
    {
        $data = $this->input->read(2500);
        $patterns = array();
        // delim can be anything but line breaks, quotes, or any type of spaces
        $delim = '([^\r\n\w"\'' . chr(self::SPACE) . '])';
        $patterns[] = '/' . $delim . ' ?(["\']).*?(\2)(\1)/'; // ,"something", - anything but whitespace or quotes followed by a possible space followed by a quote followed by anything followed by same quote, followed by same anything but whitespace
        $patterns[] = '/(?:^|\n)(["\']).*?(\1)' . $delim . ' ?/'; // 'something', - beginning of line or line break, followed by quote followed by anything followed by quote followed by anything but whitespace or quotes
        $patterns[] = '/' . $delim . ' ?(["\']).*?(\2)(?:^|\n)/'; // ,'something' - anything but whitespace or quote followed by possible space followed by quote followed by anything followed by quote, followed by end of line
        $patterns[] = '/(?:^|\n)(["\']).*?(\2)(?:$|\n)/'; // 'something' - beginning of line followed by quote followed by anything followed by quote followed by same quote followed by end of line
        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $data, $matches) && $matches) break;
        }
        if ($matches) {
            $quotes = array_count_values($matches[2]);
            arsort($quotes);
            $quotes = array_flip($quotes);
            if ($quote = array_shift($quotes)) {
                $delims = array_count_values($matches[1]);
                arsort($delims);
                $delims = array_flip($delims);
                $delim = array_shift($delims);
                return array($quote, $delim);
            }
        }
        throw new TasteQuoteAndDelimException("quoteChar and delimiter cannot be determined");
    }

     /**
      * Take a list of likely delimiter characters and find the one that occurs
      * the most consistent amount of times within the provided data.
      *
      * @param string The data to examime for "quoting style"
      * @param char The type of quote character being used (single or double)
      * @param char The character used as the column delimiter
      * @param char The character used for newlines
      * @return string One of four "QUOTING_" constants defined above--see this
      *     method's description for more info.
      * @access public
      * @todo Refactor this method--It needs more thorough testing against a wider
      *     variety of CSV data to be sure it works reliably. And I'm sure there
      *     are many performance and logic improvements that could be made. This
      *     is essentially a first draft.
      * @todo Use replaceQuotedSpecialChars rather than removeQuotedStrings
      */
    public function lickDelimiter($data, $eol, $delimiters)
    {
        $lines = explode($eol, $this->removeQuotedStrings($data));
        $modes = array();
        $start = 0;
        $charFrequency = array();
        while ($start < count($lines)) {
            foreach ($lines as $key => $line) {
                if (!trim($line)) continue;
                foreach ($delimiters as $char) {
                    $freq = substr_count($line, $char);
                    $charFrequency[$char][$key] = $freq;
                }
            }
            $start++;
        }
        $averages = Utils::array_average($charFrequency);
        $modes = Utils::array_mode($charFrequency);
        $consistencies = array();
        foreach ($averages as $achar => $avg) {
            foreach ($modes as $mchar => $mode) {
                if ($achar == $mchar) {
                    if ($mode) {
                        $consistencies[$achar] = $avg / $mode;
                    } else {
                        $consistencies[$achar] = 0;
                    }
                    break;
                }
            }
        }
        if (empty($consistencies)) {
            throw new TasteDelimiterException('Cannot determine delimiter character');
        }
        arsort($consistencies);
        return key($consistencies);
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
     * @param string The data to examime for "quoting style"
     * @param char The type of quote character being used (single or double)
     * @param char The character used as the column delimiter
     * @param char The character used for newlines
     * @return string One of four "QUOTING_" constants defined above--see this
     *     method's description for more info.
     * @access public
     * @todo Refactor this method--It needs more thorough testing against a wider
     *     variety of CSV data to be sure it works reliably. And I'm sure there
     *     are many performance and logic improvements that could be made. This
     *     is essentially a first draft.
     */
    public function lickQuotingStyle($data, $quote, $delim, $eol)
    {
        $data = $this->replaceQuotedSpecialChars($data, $delim);

        $quoting_styles = array(
            Flavor::QUOTE_ALL => 0,
            Flavor::QUOTE_NONE => 0,
            Flavor::QUOTE_MINIMAL => 0,
            Flavor::QUOTE_NONNUMERIC => 0,
        );

        $lines = explode($eol, $data);
        $freq = array(
            'quoted' => array(),
            'unquoted' => array()
        );

        foreach ($lines as $key => $line) {
            // now we can sub back in the correct newlines
            $line = str_replace(self::PLACEHOLDER_NEWLINE, $eol, $line);
            $cols = explode($delim, $line);
            foreach ($cols as $colkey => $col) {
                // now we can sub back in the correct delim characters
                $col = str_replace(self::PLACEHOLDER_DELIM, $delim, $col);
                if ($isQuoted = $this->isQuoted($col)) {
                    $col = $this->unQuote($col);
                    $type = $this->lickDataType($col);
                    // we can remove this guy all together since at lease one column is quoted
                    unset($quoting_styles[Flavor::QUOTE_NONE]);
                    $freq['quoted'][] = $type;
                } else {
                    $type = $this->lickDataType($col);
                    // we can remove this guy all together since at lease one column is unquoted
                    unset($quoting_styles[Flavor::QUOTE_ALL]);
                    $freq['unquoted'][] = $type;
                }
            }
        }
        $types = array_unique($freq['quoted']);
        // if quoting_styles still has QUOTE_ALL or QUOTE_NONE, then that's the one to return
        if (array_key_exists(Flavor::QUOTE_ALL, $quoting_styles)) return Flavor::QUOTE_ALL;
        if (array_key_exists(Flavor::QUOTE_NONE, $quoting_styles)) return Flavor::QUOTE_NONE;
        if (count($types) == 1) {
            if (current($types) == self::DATA_SPECIAL) return Flavor::QUOTE_MINIMAL;
            elseif (current($types) == self::DATA_NONNUMERIC) return Flavor::QUOTE_NONNUMERIC;
        } else {
            if (array_key_exists(self::DATA_NONNUMERIC, array_flip($types))) {
                // allow for a SMALL amount of error here
                $counts = array(self::DATA_SPECIAL => 0, self::DATA_NONNUMERIC => 0);
                array_walk($freq['quoted'], function ($val, $key) use (&$counts) {
                    $counts[$val]++;
                });
                arsort($counts);
                $most = current($counts);
                $least = end($counts);
                $err_margin = $least / $most;
                if ($err_margin < 1) return Flavor::QUOTE_NONNUMERIC;
            }
        }
        return Flavor::QUOTE_MINIMAL;
    }

    /**
     * Remove quotes around a piece of text (if there are any)
     *
     * @param string The data to "unquote"
     * @return string The data passed in, only with quotes stripped (off the edges)
     * @access protected
     */
    protected function unQuote($data)
    {
        return preg_replace('/^(["\'])(.*)\1$/', '\2', $data);
    }

    /**
     * Determine whether a particular string of data has quotes around it.
     *
     * @param string The data to check
     * @return boolean Whether the data is quoted or not
     * @access protected
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
     * @param string The data to determine the type of
     * @return string The type of data (one of the "DATA_" constants above)
     * @access protected
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
     * currently there is no dedicated method for doing so I just use str_replace
     *
     * @param string The string to do the replacements on
     * @param char The delimiter character to replace
     * @return string The data with replacements performed
     * @access protected
     * @todo I could probably pass in (maybe optionally) the newline character I
     *     want to replace as well. I'll do that if I need to.
     */
    protected function replaceQuotedSpecialChars($data, $delim)
    {
        return preg_replace_callback('/([\'"])(.*)\1/imsU', function($matches) use ($delim) {
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
     * @param string The string of data to check the type of
     * @return string One of the TYPE_ string constants above
     * @access protected
     * @uses Carbon/Carbon date/time ilbrary/class
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
        } else {
            try {
                $year = '([01][0-9])?[0-9]{2}';
                $month = '([01]?[0-9]|Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)';
                $day = '[0-3]?[0-9]';
                $sep = '[\/\.\-]?';
                $time = '([0-2]?[0-9](:[0-5][0-9]){1,2}(am|pm)?|[01]?[0-9](am|pm))';
                $date = '(' . $month . $sep . $day . $sep . $year . '|' . $day . $sep . $month . $sep . $year . '|' . $year . $sep . $month . $sep . $day . ')';
                $dt = Carbon::parse($data);
                if ($dt->today()) {
                    // then this is most likely a time string...
                    if (preg_match("/^{$time}$/i", $data)) {
                        return self::TYPE_TIME;
                    }
                }
                if (preg_match("/^{$date}$/i", $data)) {
                    return self::TYPE_DATE;
                } elseif(preg_match("/^{$date} {$time}$/i")) {
                    return self::TYPE_DATETIME;
                }
            } catch (\Exception $e) {
                // now go on checking remaining types
                if (preg_match('/^\w+$/', $data)) {
                    return self::TYPE_ALNUM;
                }
            }
        }
        return self::TYPE_STRING;
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
     * @param string The CSV data to examine (only 20 rows will be examined so )
     *     there is no need to provide any more data than that)
     * @param char The CSV data's quoting char (either double or single quote)
     * @param char The CSV data's delimiting char (can be a variety of chars but)
     *     typically is either a comma or a tab, sometimes a pipe)
     * @param char The CSV data's end-of-line char(s) (\n \r or \r\n)
     * @return boolean True if the data (most likely) contains a header row
     * @access public
     * @todo This method needs a total refactor. It's not necessary to loop twice
     *     You could get away with one loop and that would allow for me to do
     *     something like only examining enough rows to get to a particular
     *     "hasHeader" score (+-100 for instance) & then just return true|false
     * @todo Also, break out of the first loop after a certain (perhaps even a
     *     configurable) amount of lines (you only need to examine so much data )
     *     to reliably make a determination and this is an expensive method)
     */
    public function  lickHeader($data, $quote, $delim, $eol)
    {
        $data = $this->replaceQuotedSpecialChars($data, $delim);
        $lines = explode($eol, $data);
        $types = array();
        foreach ($lines as $line_no => $line) {
            // now we can sub back in the correct newlines
            $line = str_replace(self::PLACEHOLDER_NEWLINE, $eol, $line);
            $cols = explode($delim, $line);
            $col_count = count($cols);
            foreach ($cols as $col_no => $col) {
                // now we can sub back in the correct delim characters
                $col = str_replace(self::PLACEHOLDER_DELIM, $delim, $col);
                $types[$line_no][$col_no] = array(
                    'type' => $this->lickType($this->unQuote($col)),
                    'length' => strlen($col)
                );
            }
        }

        $hasHeader = 0;
        $potential_header = array_shift($types);
        foreach ($types as $line_no => $cols) {
            foreach ($cols as $col_no => $col_info) {
                extract($col_info);
                extract($potential_header[$col_no], EXTR_PREFIX_ALL, "header");
                if ($header_type == self::TYPE_STRING) {
                    // use length
                    if ($length != $header_length) $hasHeader++;
                    else $hasHeader--;
                } else {
                    if ($type != $header_type) $hasHeader++;
                    else $hasHeader--;
                }
            }
        }

        return $hasHeader > 0;

        /**
         * This is just legacy code... it was something I tried that didn't pan
         * out. I just want to test the above code a little more thoroughly
         * before completely removing this...
         */

        // $potential_header = array_shift($types);
        // $potential_header_count = count($potential_header);
        // $scoresheet = array_pad(array(), $potential_header_count, array());
        // foreach ($types as $line_no => $cols) {
        //     $line_cols_count = count($cols);
        //     $col_count_match = ($potential_header_count == $line_cols_count);
        //     foreach ($cols as $col => $info) {
        //         extract($info);
        //         $header_type = $potential_header[$col]['type'];
        //         $header_length = $potential_header[$col]['length'];
        //         if ($header_type == self::TYPE_STRING) {
        //             // if the header column is a string, then its type couldn't
        //             // be determined beyond that it's a string of characters, so
        //             // use its length as a barometer rather than its type
        //             if ($header_length == $length) {
        //                 $scoresheet[$col] []= 1;
        //             }
        //         } else {
        //             if ($header_type == $type) {
        //                 if ($header_length == $length) {
        //                     $scoresheet[$col] []= 3;
        //                 } else {
        //                     $scoresheet[$col] []= 2;
        //                 }
        //             }
        //         }
        //     }
        // }
        //
        // $final_scores = array();
        // array_walk($scoresheet, function($scores, $col) use (&$final_scores) {
        //     $final_scores[$col] = array_sum($scores);
        // });
        //
        // $total_rows = count($lines);
        // dd($total_rows);
        // dd($final_scores);
    }
}