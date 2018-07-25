<?php
/**
 * CSVelte: Slender, elegant CSV for PHP
 *
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @copyright Copyright (c) 2018 Luke Visinoni
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   See LICENSE file (MIT license)
 */
namespace CSVelteTest\IO;

use CSVelteTest\UnitTestCase;
use CSVelteTest\StreamWrapper\HttpStreamWrapper;

class IOTest extends UnitTestCase
{
    public function setUp()
    {
        parent::setUp();
        stream_wrapper_unregister('http');
        stream_wrapper_register(
            'http',
            HttpStreamWrapper::class
        ) or die('Failed to register protocol');
    }
    public function tearDown()
    {
        parent::tearDown();
        stream_wrapper_restore('http');
    }
}