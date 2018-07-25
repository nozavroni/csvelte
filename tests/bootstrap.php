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

use Symfony\Component\VarDumper\VarDumper;

if (!function_exists('dd')) {
    function dd($input, $exit = true)
    {
        VarDumper::dump($input);
        if ($exit) exit;
    }
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
function si($in, $exit = true)
{
    dd(json_encode($in), $exit);
}

