<?php namespace CSVelte;

use CSVelte\Input\InputInterface;
// use CSVelte\Utils;

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
        $data = preg_replace_callback('/([\'"])(.*)\1/imsU', function($matches) use ($delim) {
            $ret = preg_replace("/([\r\n])/", self::PLACEHOLDER_NEWLINE, $matches[0]);
            $ret = str_replace($delim, self::PLACEHOLDER_DELIM, $ret);
            return $ret;
        }, $data);

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
     *     - alpha - only letters
     *     - alphanumeric - letters and numbers
     *     - numeric - only numbers
     *     - special - contains characters that could potentially need to be quoted (possible delimiter characters)
     *     - quotes - contains quote characters
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

    public function  lickHeader()
    {
        # Creates a dictionary of types of data in each column. If any
        # column is of a single type (say, integers), *except* for the first
        # row, then the first row is presumed to be labels. If the type
        # can't be determined, it is assumed to be a string in which case
        # the length of the string is the determining factor: if all of the
        # rows except for the first are the same length, it's a header.
        # Finally, a 'vote' is taken at the end for each column, adding or
        # subtracting from the likelihood of the first row being a header.

    }
}
