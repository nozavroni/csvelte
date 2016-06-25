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
}
