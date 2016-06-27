<?php namespace CSVelte;

use Carbon\Carbon;
use CSVelte\Input\InputInterface;

/**
 * CSVelte\Taster
 * Given CSV data, Taster will "taste" the data and provide its buest guess at
 * its "flavor". In other words, this class inspects CSV data and attempts to
 * auto-detect various CSV attributes such as line endings, quote characters, etc..
 *
 * @package   CSVelte
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class Taster
{
    const EOL_UNIX    = 'lf';
    const EOL_TRS80   = 'cr';
    const EOL_WINDOWS = 'crlf';

    const LINE_FEED = 10;
    const CARRIAGE_RETURN = 13;
    const SPACE = 32;

    const DATA_NONNUMERIC = 'nonnumeric';
    const DATA_SPECIAL = 'special';
    const DATA_UNKNOWN = 'unknown';

    const PLACEHOLDER_NEWLINE = '[__NEWLINE__]';
    const PLACEHOLDER_DELIM = '[__DELIM__]';

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
     * @var CSVelte\InputInterface
     */
    protected $input;

    /**
     * Class constructor
     * accepts a CSV input source
     */
    public function __construct(InputInterface $input)
    {
        $this->input = $input;
    }

    public function lick()
    {
        return new Flavor;
    }

    protected function removeQuotedStrings($data)
    {
        return preg_replace($pattern = '/(["\'])(?:(?=(\\\\?))\2.)*?\1/sm', $replace = '', $data);
    }

    // pulled from stackoverflow thread *tips hat to username "Harm"*
    // @todo I probably will make this method protected when I'm done with testing...
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
     * determine these characters some other way...
     * @todo make protected
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
        if (!$matches) return array("", null); // couldn't guess quote or delim
        $quotes = array_count_values($matches[2]);
        arsort($quotes);
        $quotes = array_flip($quotes);
        if ($quote = array_shift($quotes)) {
            $delims = array_count_values($matches[1]);
            arsort($delims);
            $delims = array_flip($delims);
            $delim = array_shift($delims);
        } else {
            $quote = "";
            $delim = null;
        }
        return array($quote, $delim);
    }

    /**
     * Take a list of likely delimiter characters and fine the one that occurs
     * the most consistent amount of times in the data.
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
        arsort($consistencies);
        return key($consistencies);
    }

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

    protected function unQuote($data)
    {
        return preg_replace('/^(["\'])(.*)\1$/', '\2', $data);
    }

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

    public function  lickHeader($data, $quote, $delim, $eol)
    {
        # Creates an array of types of data in each column. If any
        # column is of a single type (say, integers), *except* for the first
        # row, then the first row is presumed to be labels. If the type
        # can't be determined, it is assumed to be a string in which case
        # the length of the string is the determining factor: if all of the
        # rows except for the first are the same length, it's a header.
        # Finally, a 'vote' is taken at the end for each column, adding or
        # subtracting from the likelihood of the first row being a header.
        # NOTE: Maybe I should assign two points for same data type and one
        # point for same length... then maybe three points for same type AND
        # same length... ?
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
