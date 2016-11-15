<?php
/**
 * CSVelte: Slender, elegant CSV for PHP
 *
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @version   v${CSVELTE_DEV_VERSION}
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace CSVelte\Collection;

class CharCollection extends AbstractCollection
{
    /**
     * Returns a string containing all the characters in the set.
     *
     * @return string
     */
    public function __toString()
    {
        return implode("", $this->data);
    }

    /**
     * Convert input data to an array.
     *
     * Convert the input data to an array that can be worked with by a collection.
     *
     * @param mixed $data The input data
     * @return array
     */
    protected function prepareData($data)
    {
        if (!is_string($data)) {
            $data = (string) $data;
        }
        return str_split($data);
    }

    /**
     * Is data consistent with this collection type?
     *
     * @param mixed $data The data to check
     * @return bool
     */
    protected function isConsistentDataStructure($data)
    {
        return static::isCharacterSet($data);
    }

}