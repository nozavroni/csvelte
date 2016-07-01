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

function dd($input, $exit = true)
{
    var_dump($input);
    if ($exit) exit;
}

function with($obj) { return $obj; }
