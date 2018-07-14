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
     */
    protected function replaceQuotedSpecialChars($data, $delim = null, $eol = null)
    {
        if (is_null($eol)) {
            $eol = "\r\n|\r|\n";
        }
        return preg_replace_callback('/([\'"])(.*)\1/imsU', function ($matches) use ($delim, $eol) {
            $ret = preg_replace("/({$eol})/", self::PLACEHOLDER_NEWLINE, $matches[0]);
            if (!is_null($delim)) {
                $ret = str_replace($delim, self::PLACEHOLDER_DELIM, $ret);
            }
            return $ret;
        }, $data);
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