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

use function CSVelte\is_traversable;

class TabularCollection extends MultiCollection
{
    /**
     * Is input data structure valid?
     *
     * In order to determine whether a given data structure is valid for a
     * particular collection type (tabular, numeric, etc.), we have this method.
     *
     * @param mixed $data The data structure to check
     * @return boolean True if data structure is tabular
     */
    protected function isConsistentDataStructure($data)
    {
        /*
        $keys = array_map(function($value) {
            if (is_traversable($value)) {
                if (!is_array($value)) {
                    $value = iterator_to_array($value);
                }
                return array_keys($value);
            }
        }, $data);
        $unique = array_unique($keys);
        if (count($unique) == 1) {
            return true;
        }
        return false;
        */

        return static::isTabular($data);
    }

    public function map(callable $callback)
    {
        $ret = [];
        foreach ($this->data as $key => $row) {
            $ret[$key] = $callback(static::factory($row));
        }
        return static::factory($ret);
    }
}