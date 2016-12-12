<?php

/*
 * CSVelte: Slender, elegant CSV for PHP
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @version   {version}
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace CSVelte\Collection;

use BadMethodCallException;
use OutOfBoundsException;

class TabularCollection extends MultiCollection
{
    /**
     * Tabular Where Search.
     *
     * Search for values of a certain key that meet a particular search criteria
     * using either one of the "Collection\Criteria::" class constants, or its string
     * counterpart.
     *
     * Warning: Only works for tabular collections (2-dimensional data array)
     *
     * @param string $key The key to compare to $val
     * @param mixed|Callable $val Either a value to test against or a callable to
     *     run your own custom "where comparison logic"
     * @param string $comp The type of comparison operation ot use (such as "=="
     *     or "instanceof"). Must be one of the self::* constants' values
     *     listed at the top of this class.
     * @return TabularCollection A collection of rows that meet the criteria
     *     specified by $key, $val, and $comp
     */
    public function where($key, $val, $comp = null)
    {
        $data = [];
        if ($this->hasColumn($key)) {
            if (is_callable($val)) {
                foreach ($this->data as $ln => $row) {
                    if ($val($row[$key], $key)) {
                        $data[$ln] = $row;
                    }
                }
            } else {
                foreach ($this->data as $ln => $row) {
                    $fieldval = $row[$key];
                    switch (strtolower($comp)) {
                        case Criteria::ID:
                            $comparison = $fieldval === $val;
                            break;
                        case Criteria::NID:
                            $comparison = $fieldval !== $val;
                            break;
                        case Criteria::LT:
                            $comparison = $fieldval < $val;
                            break;
                        case Criteria::LTE:
                            $comparison = $fieldval <= $val;
                            break;
                        case Criteria::GT:
                            $comparison = $fieldval > $val;
                            break;
                        case Criteria::GTE:
                            $comparison = $fieldval >= $val;
                            break;
                        case Criteria::LIKE:
                            $comparison = strtolower($fieldval) == strtolower($val);
                            break;
                        case Criteria::NLIKE:
                            $comparison = strtolower($fieldval) != strtolower($val);
                            break;
                        case Criteria::TOF:
                            $comparison = (strtolower(gettype($fieldval)) == strtolower($val));
                            break;
                        case Criteria::NTOF:
                            $comparison = (strtolower(gettype($fieldval)) != strtolower($val));
                            break;
                        case Criteria::NEQ:
                            $comparison = $fieldval != $val;
                            break;
                        case Criteria::MATCH:
                            $match = preg_match($val, $fieldval);
                            $comparison = $match === 1;
                            break;
                        case Criteria::NMATCH:
                            $match = preg_match($val, $fieldval);
                            $comparison = $match === 0;
                            break;
                        case Criteria::EQ:
                        default:
                            $comparison = $fieldval == $val;
                            break;
                    }
                    if ($comparison) {
                        $data[$ln] = $row;
                    }
                }
            }
        }
        return new TabularCollection($data);
    }

    /**
     * Magic method call.
     *
     * @param string $method The name of the method
     * @param array  $args   The argument list
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
        throw new BadMethodCallException('Method does not exist: ' . __CLASS__ . "::{$method}()");
    }

    /**
     * Does this collection have specified column?
     *
     * @param mixed $column The column index
     *
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
     * Get column as collection.
     *
     * @param mixed $column The column index
     * @param bool  $throw  Throw an exception on failure
     *
     * @return AbstractCollection|false
     */
    public function getColumn($column, $throw = true)
    {
        $values = array_column($this->data, $column);
        if (count($values)) {
            return static::factory($values);
        }
        if ($throw) {
            throw new OutOfBoundsException(__CLASS__ . ' could not find column: ' . $column);
        }

        return false;
    }

    /**
     * Does this collection have a row at specified index?
     *
     * @param int $offset The column index
     *
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
     *
     * @return AbstractCollection|false
     */
    public function getRow($offset)
    {
        return $this->getValueAtPosition($offset);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function walk(callable $callback, $extraContext = null)
    {
        foreach ($this as $offset => $row) {
            $callback(static::factory($row), $offset, $extraContext);
        }

        return $this;
    }

    /**
     * Is input data structure valid?
     *
     * In order to determine whether a given data structure is valid for a
     * particular collection type (tabular, numeric, etc.), we have this method.
     *
     * @param mixed $data The data structure to check
     *
     * @return bool True if data structure is tabular
     */
    protected function isConsistentDataStructure($data)
    {
        return static::isTabular($data);
    }
}
