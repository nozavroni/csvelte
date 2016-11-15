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

/**
 * Class NumericCollection
 *
 * @package CSVelte\Collection
 * @todo $this->set('foo', 'bar'); should throw an exception because only
 *     numeric values are allowed. Either that or converted to int.
 */
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

    /**
     * Get the sum.
     *
     * @return mixed The sum of all values in collection
     */
    public function sum()
    {
        return array_sum($this->toArray());
    }

    /**
     * Get the average.
     *
     * @return float|int The average value from the collection
     */
    public function average()
    {
        return $this->sum() / $this->count();
    }

    /**
     * Get the mode.
     *
     * @return float|int The mode
     */
    public function mode()
    {
        $counts = $this->counts()->toArray();
        arsort($counts);
        $mode = key($counts);
        return (strpos($mode, '.')) ? floatval($mode) : intval($mode);
    }

    /**
     * Get the median value.
     *
     * @return float|int The median value
     */
    public function median()
    {
        $count = $this->count();
        $data = $this->toArray();
        natcasesort($data);
        $middle = $count / 2;
        $values = array_values($data);
        if ($count % 2 == 0) {
            // even number, use middle
            $low = $values[$middle - 1];
            $high = $values[$middle];
            return ($low + $high) / 2;
        }
        // odd number return median
        return $values[$middle];
    }

    /**
     * Get the maximum value.
     *
     * @return mixed The maximum
     */
    public function max()
    {
        return max($this->data);
    }

    /**
     * Get the minimum value.
     *
     * @return mixed The minimum
     */
    public function min()
    {
        return min($this->data);
    }

    /**
     * Get the number of times each item occurs in the collection.
     *
     * This method will return a NumericCollection where keys are the
     * values and values are the number of times that value occurs in
     * the original collection.
     *
     * @return Collection
     */
    public function counts()
    {
        return static::factory(array_count_values($this->toArray()));
    }

}