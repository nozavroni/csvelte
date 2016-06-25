<?php namespace CSVelte;

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
     * The delimiter /should/ occur the same number of times on
     * each row. However, due to malformed data, it may not. We don't want
     * an all or nothing approach, so we allow for small variations in this
     * number.
     *   1) build a table of the frequency of each character on every line.
     *   2) build a table of frequencies of this frequency (meta-frequency?),
     *      e.g.  'x occurred 5 times in 10 rows, 6 times in 1000 rows,
     *      7 times in 2 rows'
     *   3) use the mode of the meta-frequency to determine the /expected/
     *      frequency for that character
     *   4) find out how often the character actually meets that goal
     *   5) the character that best meets its goal is the delimiter
     * For performance reasons, the data is evaluated in chunks, so it can
     * try and evaluate the smallest portion of the data possible, evaluating
     * additional chunks as necessary.
     */
    // public function lickDelimiter($data, $eol, $delimiters = null)
    // {
    //     $lines = explode($eol, $data);
    //
    //     $ascii = array();
    //     foreach (range(1, 127) as $c) $ascii[] = chr($c);
    //
    //     // build frequency tables
    //     $chunkLength = min(10, count($lines));
    //     $i = 0;
    //     $charFrequency = array();
    //     $modes = array();
    //     $delims = array();
    //     $start = 0;
    //     // @todo this doesn't make sense, why not just assign chunkLength?
    //     $end = min($chunkLength, count($lines));
    //     while ($start < count($lines)) {
    //         $i++;
    //         foreach ($lines as $line) {
    //             foreach ($ascii as $char) {
    //                 $metaFrequency = array_get($charFrequency, $char, array());
    //                 // must count even if frequency is 0
    //                 $freq = substr_count($line, $char);
    //                 // value is the mode
    //                 $metaFrequency[$freq] = array_get($metaFrequency, $freq, 0) + 1;
    //                 $charFrequency[$char] = $metaFrequency;
    //             }
    //         }
    //
    //         foreach (array_keys($charFrequency) as $char) {
    //             $items = array_items($charFrequency[$char]);
    //             if (count($items) == 1 && $items[0][0] == 0) continue;
    //             // get the mode of the frequencies
    //             if (count($items) > 1) {
    //                 $modes[$char] = array_reduce($items, function($a, $b) {
    //                     return ($a[1] > $b[1] && ($a || $b));
    //                 });
    //                 // adjust the mode - subtract the sum of all other frequencies
    //                 //array_remove($items, $modes[$char]);
    //                 $r = array_reduce($items, function($a, $b){ return array(0, $a[1] + $b[1]); });
    //                 $modes[$char] = array($modes[$char][0], $modes[$char][1] - $r[1]);
    //             } else {
    //                 $modes[$char] = $items[0];
    //             }
    //         }
    //
    //         // dd($modes);
    //
    //         // build a list of possible delimiters
    //     }
    // }

    /**
     * Take a list of likely delimiter characters and fine the one that occurs
     * the most consistent amount of times in the data.
     */
    public function lickDelimiter($data, $eol, $delimiters)
    {
        $lines = explode($eol, $this->removeQuotedStrings($data));
        $lines[] = 'Nort;h; ; Mi|lwaukee State :Ba:nk,Milwaukee,WI,20364,First-Citizens Bank & Trust Company,11-Mar-16';
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
        $averages = array_average($charFrequency);
        $modes = array_mode($charFrequency);
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
}
