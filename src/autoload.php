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
namespace CSVelte;

/**
 * Add this file's parent directory to list of search paths and register autoloader.
 *
 * @var Autoloader used to autoload classes for users not taking advantage of the
 *                 benefits of either Composer (see getcomposer.org) or PSR-4 (see php-fig.org)
 *
 * @since v0.2 This file was added in version 0.2, after removing it from the
 *      Autoloader class definition file. A file should contain either function/
 *      class definitions or it should contain code such as this. Not both.
 */
$autoloader = new Autoloader();
$autoloader->addPath(__DIR__);
$autoloader->register();
