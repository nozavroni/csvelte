<?php namespace CSVelte;

use CSVelte\Exception\UnknownAttributeException;

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
        throw new UnknownAttributeException("Unknown attribute: " . $attr);
    }
}
