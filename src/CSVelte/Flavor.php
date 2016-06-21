<?php namespace CSVelte;
/**
 * CSVelte\Flavor
 * Represents a particular CSV format
 *
 * @package   CSVelte
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class Flavor
{
    protected $attributes = [
        'delimiter' => ',',
        'quoteChar' => '"',
        'escapeChar' => '\\',
        'lineTerminator' => "\n"
    ];

    public function __construct()
    {

    }

    public function __get($attr)
    {
        if (array_key_exists($attr, $this->attributes)) return $this->attributes[$attr];
        // $trace = debug_backtrace();
        // trigger_error(
        //     'Undefined property via __get(): ' . $name .
        //     ' in ' . $trace[0]['file'] .
        //     ' on line ' . $trace[0]['line'],
        //     E_USER_NOTICE);
        // return null;
    }
}
