<?php

/*
 * CSVelte: Slender, elegant CSV for PHP
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @version   v0.2.3
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace CSVelte\Collection;

use function CSVelte\is_traversable;

class MultiCollection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    public function contains($value, $index = null)
    {
        if (parent::contains($value, $index)) {
            return true;
        }
        foreach ($this->data as $key => $arr) {
            if (is_traversable($arr)) {
                $coll = static::factory($arr);
                if ($coll->contains($value, $index)) {
                    return true;
                }
            }
        }
        
        return false;
    }

    protected function isConsistentDataStructure($data)
    {
        return static::isMultiDimensional($data);
    }
}
