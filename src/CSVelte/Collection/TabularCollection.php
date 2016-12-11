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

use BadMethodCallException;
use OutOfBoundsException;
use function CSVelte\is_traversable;
use function CSVelte\collect;

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
        return static::isTabular($data);
    }

    /**
     * Does this collection have specified column?
     *
     * @param mixed $column The column index
     * @return bool
     */
    public function hasColumn($column)
    {
        try {
            $this->getColumn($column);
            return true;
        } catch (OutOfBoundsException $e) {
            return false;
        }
    }

    /**
     * Get column as collection
     *
     * @param mixed $column The column index
     * @param bool $throw Throw an exception on failure
     * @return AbstractCollection|false
     */
    public function getColumn($column, $throw = true)
    {
        $values = array_column($this->data, $column);
        if (count($values)) {
            return static::factory($values);
        }
        if ($throw) {
            throw new OutOfBoundsException(__CLASS__ . " could not find column: " . $column);
        }
        return false;
    }

    /**
     * Does this collection have a row at specified index?
     *
     * @param int $offset The column index
     * @return bool
     */
    public function hasRow($offset)
    {
        try {
            $this->getRow($offset);
            return true;
        } catch (OutOfBoundsException $e) {
            return false;
        }
    }

    /**
     * Get row at specified index.
     *
     * @param int $offset The row offset (starts from 0)
     * @param bool $throw Whether or not to throw an exception if row does not exist
     *
     * @return AbstractCollection|false
     */
    public function getRow($offset, $throw = true)
    {
        return $this->getValueAtPosition($offset);
    }

    /**
     * @inheritdoc
     */
    public function map(callable $callback)
    {
        $ret = [];
        foreach ($this->data as $key => $row) {
            $ret[$key] = $callback(static::factory($row));
        }
        return static::factory($ret);
    }

    /**
     * @inheritdoc
     */
    public function walk(callable $callback, $extraContext = null)
    {
        foreach ($this as $offset => $row) {
            $callback(static::factory($row), $offset, $extraContext);
        }
        return $this;
    }

//    public function average($column)
//    {
//        $coll = $this->getColumnAsCollection($column);
//        return $coll->sum() / $coll->count();
//    }
//
//    public function mode($column)
//    {
//        return $this->getColumnAsCollection($column)->mode();
//    }
//
//    public function sum($column)
//    {
//        return $this->getColumnAsCollection($column)->sum();
//    }
//
//    public function median($column)
//    {
//        return $this->getColumnAsCollection($column)->median();
//    }
//
//    protected function getColumnAsCollection($column)
//    {
//        return static::factory(array_column($this->data, $column));
//    }

    /**
     * Magic method call
     *
     * @param string $method The name of the method
     * @param array $args The argument list
     *
     * @throws BadMethodCallException If no method exists
     *
     * @return mixed
     *
     * @todo Add phpdoc comments for dynamic methods
     * @todo throw BadMethodCallException
     */
    public function __call($method, $args)
    {
        $argc = count($args);
        if ($argc == 1 && $this->hasColumn($index = array_pop($args))) {
            $column = $this->getColumn($index);
            if (method_exists($column, $method)) {
                return call_user_func_array([$column, $method], $args);
            }
        }
        throw new BadMethodCallException("Method does not exist: " . __CLASS__ . "::{$method}()");
    }
}