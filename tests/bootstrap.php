<?php
/**
 * PHPUnit Bootstrap
 * The CLI test-runner calls this code before running its tests.
 *
 * @package   CSVelte
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/CSVelte/TestFiles.php';

function dd($input, $exit = true, $label = null)
{
    if ($label) echo $label . "\n" . implode(array_map(function($c){ return "-"; }, str_split($label))) . "\n";
    var_dump($input);
    echo "\n";
    if ($exit) exit;
}

/**
 * Show Invisibles
 * This is used for displaying invisible characters while testing. When I need to
 * dump a string or array of strings and it contains newlines, there's no way for
 * me to reliably know whether they are \n \r or \r\n. This will split strings up
 * and add a ("\r") next to each invisible character (at least the ones I use )
 * frequently within this library).
 *
 * @note I was going to write out a function that replaces these characters with
 *     visible versions, but it appears that json_encode does this pretty well
 *     for me. Neato!
 */
function si($in, $exit = true, $dump = true)
{
    $out = json_encode($in);
    if ($dump) return var_dump($out);
    else {
        if ($exit) exit($out);
    }
    return $out;
}

function with($obj) { return $obj; }
