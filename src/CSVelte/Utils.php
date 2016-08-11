<?php
/**
 * CSVelte: Slender, elegant CSV for PHP
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @version   v0.1
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 * @internal 
 */
namespace CSVelte;
/**
 * CSVelte Utility Tool Belt
 *
 * This is a heinously ugly class full of static methods for performing various
 * useful functions such as removing an element from an array by value, averaging
 * the values of an erray, etc.
 *
 * *Note:* Don't get used to this class, it is almost certainly going away eventuallly
 *
 * @package CSVelte
 * @subpackage Utilities
 * @since v0.1
 * @internal
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
                throw new \OutOfBoundsException('Unknown array index: ' . $key);
            }
        }
        return $default;
    }

    public static function array_items($arr)
    {
        $items = array();
        foreach ($arr as $key => $val) {
            $items[] = array($key, $val);
        }
        return $items;
    }

   /**
    * @todo Find where I'm using this... I don't see how it could be working
    *     considering $arr isn't passed by reference and it doesn't return anything
    * @note I commented this function out and then reran my tests and nothing
    *     seemed to break so I'm assuming I wrote it for something I no longer
    *     use. I've rewritten it below to repurpose it for something else. Leaving
    *     this here for no in case something pops up
    */
    // public static function array_remove($arr, $item)
    // {
    //     $unset = null;
    //     foreach ($arr as $key => $val) {
    //         if ($item == $val) {
    //             unset($arr[$key]);
    //             return;
    //         }
    //     }
    //     // @todo Not sure if this is the right exception
    //     throw new \OutOfBoundsException("array_remove: cannot find item within array");
    // }

    /**
     * Copy an array, leaving out specified key or value.
     * I am fully aware that you can simply unset($array[$key]) to remove an elem
     * from an array by key, but removing an item from an array by value is a tad
     * trickier. Also, this function doesn't actually affect the array, it passes
     * back a copy with the specified element removed. Unless otherwise specified
     */
    public static function array_remove(&$arr, $item, $bykey = false, $copy = true)
    {
        if ($copy) {
            dd($arr, false, "arr");
            $arrcopy = self::array_copy($arr);
            dd($arr, false, "arr before"); dd($arrcopy, false, "arrcopy before");
            unset($arr);
            dd($arr, false, "arr after"); dd($arrcopy, false, "arrcopy after");
        }
        // foreach ($arr as $key => $val) {
        //     if ($item == $val) {
        //         unset($arr[$key]);
        //         return;
        //     }
        // }
        // // @todo Not sure if this is the right exception
        // throw new \OutOfBoundsException("array_remove: cannot find item within array");
    }

    public static function array_copy(array $arr, $preserve_keys = true)
    {
        $copy = array();
        foreach ($arr as $k => $v) {
            $copy[$k] = $v;
        }
        return $copy;
    }

    public static function average($arr)
    {
        if (!is_array($arr)) throw new \InvalidArgumentException('"average" function expected array, got ' . gettype($arr));
        return array_sum($arr) / count($arr);
    }

    public static function array_average($arr)
    {
        $return = array();
        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                $return[$key] = self::average($val);
            }
        }
        return $return;
    }

    public static function mode($arr)
    {
        if (!is_array($arr)) throw new \InvalidArgumentException('"mode" function expected array, got ' . gettype($arr));
        $vals = array();
        foreach ($arr as $key => $val) {
            $vals[$val] = self::array_get($vals, $val, 0) + 1;
        }
        arsort($vals);
        return key($vals);
    }

    public static function array_mode($arr)
    {
        $return = array();
        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                $return[$key] = self::mode($val);
            }
        }
        return $return;
    }

    /**
     * Uses array_map to apply a callback to each character in a string
     */
    public static function string_map($str, Callable $callable)
    {
        return join(array_map($callable, str_split($str)));
    }

    // if (!function_exists('array_column')) {
    //     throw new \Exception("Need to implement this");
    // }
}
