<?php
/**
 * PHPUnit Bootstrap
 * The CLI test-runner calls this code before running its tests.
 *
 * @package   CSVelte
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
require __DIR__ . '/../vendor/autoload.php';

function dd($input)
{
    var_dump($input);
    exit;
}

function array_get($arr, $key, $default)
{
    return array_key_exists($key, $arr) ? $arr[$key] : $default;
}

function array_items($arr)
{
    $items = array();
    foreach ($arr as $key => $val) {
        $items[] = array($key, $val);
    }
    return $items;
}

function array_remove($arr, $item)
{
    $unset = null;
    foreach ($arr as $key => $val) {
        if ($item == $val) {
            unset($arr[$key]);
            return;
        }
    }
    // @todo Not sure if this is the right exception
    throw new \OutOfBoundsException("array_remove: cannot find item within array");
}

function average($arr)
{
    if (!is_array($arr)) throw new \InvalidArgumentException('"average" function expected array, got ' . gettype($arr));
    return array_sum($arr) / count($arr);
}

function array_average($arr)
{
    $return = array();
    foreach ($arr as $key => $val) {
        if (is_array($val)) {
            $return[$key] = average($val);
        }
    }
    return $return;
}

function mode($arr)
{
    if (!is_array($arr)) throw new \InvalidArgumentException('"mode" function expected array, got ' . gettype($arr));
    $vals = array();
    foreach ($arr as $key => $val) {
        $vals[$val] = array_get($vals, $val, 0) + 1;
    }
    arsort($vals);
    return key($vals);
}

function array_mode($arr)
{
    $return = array();
    foreach ($arr as $key => $val) {
        if (is_array($val)) {
            $return[$key] = mode($val);
        }
    }
    return $return;
}

if (!function_exists('array_column')) {
    throw new \Exception("Need to implement this");
}
