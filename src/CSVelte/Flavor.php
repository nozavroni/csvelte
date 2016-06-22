<?php namespace CSVelte;

use CSVelte\Exception\UnknownAttributeException;
use CSVelte\Exception\ImmutableException;

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
        'lineTerminator' => "\r\n"
    ];

    public function __construct($attributes = null)
    {
        if (!is_null($attributes)) {
            if (!is_array($attributes)) {
                // @todo throw exception?
                return;
            }
            foreach ($attributes as $attr => $val) {
                $this->assertValidAttribute($attr);
                $this->attributes[$attr] = $val;
            }
        }
    }

    protected function assertValidAttribute($attr)
    {
        if (!array_key_exists($attr, $this->attributes))
            throw new UnknownAttributeException("Unknown attribute: " . $attr);
    }

    public function __get($attr)
    {
        $this->assertValidAttribute($attr);
        return $this->attributes[$attr];
    }

    public function __set($attr, $val)
    {
        throw new ImmutableException("Cannot change attributes on an immutable object: " . self::class);
    }

}
