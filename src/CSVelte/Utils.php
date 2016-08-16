<?php
/**
 * CSVelte: Slender, elegant CSV for PHP
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @version   v0.1
 *
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 *
 * @internal
 */
namespace CSVelte;

/**
 * CSVelte Utility Tool Belt.
 *
 * This is a heinously ugly class full of static methods for performing various
 * useful functions such as removing an element from an array by value, averaging
 * the values of an erray, etc.
 *
 * *Note:* Don't get used to this class, it is almost certainly going away eventuallly
 *
 * @since v0.1
 *
 * @internal
 *
 * @todo Either clean this up immensely, maybe by turning it into a collection
 *     object class or move them to namespaced functions. Or, if you can stomach
 *     the idea of adding another dependency, just use an existing utility
 *     library such as Underscore or something.
 */
class Utils
{
    public static function array_get($arr, $key, $default = null, $throwException = false)
    {
        if (array_key_exists($key, $arr)) {
            return $arr[$key];
        } else {
            if ($throwException) {
                // @todo is this the correct exception to throw?
                throw new \OutOfBoundsException('Unknown array index: '.$key);
            }
        }

        return $default;
    }

    public static function array_items($arr)
    {
        $items = [];
        foreach ($arr as $key => $val) {
            $items[] = [$key, $val];
        }

        return $items;
    }

    public static function average($arr)
    {
        if (!is_array($arr)) {
            throw new \InvalidArgumentException('"average" function expected array, got '.gettype($arr));
        }

        return array_sum($arr) / count($arr);
    }

    public static function array_average($arr)
    {
        $return = [];
        foreach ($arr as $key => $val) {
            $return[$key] = self::average($val);
        }

        return $return;
    }

    public static function mode($arr)
    {
        if (!is_array($arr)) {
            throw new \InvalidArgumentException('"mode" function expected array, got '.gettype($arr));
        }
        $vals = [];
        foreach ($arr as $key => $val) {
            $vals[$val] = self::array_get($vals, $val, 0) + 1;
        }
        arsort($vals);

        return key($vals);
    }

    public static function array_mode($arr)
    {
        $return = [];
        foreach ($arr as $key => $val) {
            $return[$key] = self::mode($val);
        }

        return $return;
    }

    /**
     * Uses array_map to apply a callback to each character in a string.
     */
    public static function string_map($str, callable $callable)
    {
        return implode(array_map($callable, str_split($str)));
    }
}
