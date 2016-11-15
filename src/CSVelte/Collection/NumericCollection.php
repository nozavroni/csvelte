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

class NumericCollection extends AbstractCollection
{
    protected function isConsistentDataStructure($data)
    {
        if (!is_traversable($data)) {
            return false;
        }
        foreach ($data as $val) {
            if (!is_numeric($val)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Increment an item.
     *
     * Increment the item specified by $key by one value. Intended for integers
     * but also works (using this term loosely) for letters. Any other data type
     * it may modify is unintended behavior at best.
     *
     * This method modifies its internal data array rather than returning a new
     * collection.
     *
     * @param  mixed $key The key of the item you want to increment.
     * @param int $interval The interval that $key should be incremented by
     * @return $this
     */
    public function increment($key, $interval = 1)
    {
        $val = $this->get($key, null, true);
        for ($i = 0; $i < $interval; $i++) {
            $val++;
        }
        $this->set($key, $val);
        return $this;
    }

    /**
     * Decrement an item.
     *
     * Frcrement the item specified by $key by one value. Intended for integers.
     * Does not work for letters and if it does anything to anything else, it's
     * unintended at best.
     *
     * This method modifies its internal data array rather than returning a new
     * collection.
     *
     * @param mixed $key The key of the item you want to decrement.
     * @param int $interval The interval that $key should be decremented by
     * @return $this
     */
    public function decrement($key, $interval = 1)
    {
        $val = $this->get($key, null, true);
        for ($i = 0; $i < $interval; $i++) {
            $val--;
        }
        $this->set($key, $val);
        return $this;
    }

}