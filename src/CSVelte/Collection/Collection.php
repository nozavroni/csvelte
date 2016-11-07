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
     * @var array The data this collection represents
     */
    protected $data;

    /**
     * Assert input data is traversable.
     *
     * This method has no return value. It will simply throw an exception unless
     * its input variable is traversable.
     *
     * @param mixed $data The data to assert correct type of
     * @throws InvalidArgumentException Unless $data is traversable
     */
    protected function assertCorrectInputDataType($data)
    {
        if (!is_traversable($data)) {
            throw new InvalidArgumentException(__CLASS__ . ' expected traversable data, got: ' . gettype ($data));
        }
    }
}