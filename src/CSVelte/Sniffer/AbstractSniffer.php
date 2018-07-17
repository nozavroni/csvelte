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
namespace CSVelte\Sniffer;

abstract class AbstractSniffer
{
    /**
     * Placeholder strings -- hold the place of newlines and delimiters contained
     * within quoted text so that the explode method doesn't split incorrectly.
     */
    const PLACEHOLDER_NEWLINE = '[__NEWLINE__]';
    const PLACEHOLDER_DELIM   = '[__DELIMIT__]';

    protected $options = [];

    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    protected function setOptions(array $options)
    {
        $this->options = array_merge($this->options, $options);
        return $this;
    }

    protected function setOption($option, $value)
    {
        if (array_key_exists($option, $this->options)) {
            $this->options[$option] = $value;
        }
        return $this;
    }

    protected function getOption($option)
    {
        if (array_key_exists($option, $this->options)) {
            return $this->options[$option];
        };
    }

    /**
     * Replace all instances of newlines and whatever character you specify (as
     * the delimiter) that are contained within quoted text. The replacements are
     * simply a special placeholder string.
     *
     * @param string $data  The string to do the replacements on
     * @param string $delim The delimiter character to replace
     *
     * @return string The data with replacements performed
     */
    protected function replaceQuotedSpecialChars($data, $delim = null, $eol = null)
    {
        if (is_null($eol)) {
            $eol = "\r\n|\r|\n";
        }
        return preg_replace_callback('/([\'"])(.*)\1/imsU', function ($matches) use ($delim, $eol) {
            $ret = preg_replace("/({$eol})/", static::PLACEHOLDER_NEWLINE, $matches[0]);
            if (!is_null($delim)) {
                $ret = str_replace($delim, static::PLACEHOLDER_DELIM, $ret);
            }
            return $ret;
        }, $data);
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

    protected function unQuote($string)
    {
        return preg_replace('/^(["\'])(.*)\1$/', '\2', (string) $string);
    }

    /**
     * Analyze data (sniff)
     *
     * @param string $data The data to analyze (sniff)
     *
     * @return string|string[]
     */
    abstract public function sniff($data);
}