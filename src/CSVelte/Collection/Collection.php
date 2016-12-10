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

use \InvalidArgumentException;
use function CSVelte\is_traversable;

class Collection extends AbstractCollection
{
    /**
     * Is correct input data type?
     *
     * @param mixed $data The data to assert correct type of
     * @return bool
     */
    protected function isConsistentDataStructure($data)
    {
        return is_traversable($data);
    }
}