<?php
namespace CSVelteTest\IO;
/**
 * Testing new Input/Output classes for v0.2.
 *
 * Version 0.2 intends to refactor how CSVelte deails with I/O to make it neater,
 * cleaner, more intuitive, etc. It intends to use the SplFileObject and its ilk.
 * This will test all the new shit. Will most likely replace the following:
 *
 *     * InputStringTest
 *     * InputTest
 *     * WritableTest
 *
 * It will also most likely effect many of the tests and code in the following:
 *
 *     * CSVelteTest
 *     * ReaderTest
 *     * TasterTest
 *     * TestFiles
 *     * WriterTest
 *
 * Also will likely require quite a bit of refactoring in the reader, writer,
 * and the taster. And probably the factory/facade as well.
 */
use CSVelteTest\UnitTestCase;
use CSVelteTest\StreamWrapper\HttpStreamWrapper;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\visitor\vfsStreamPrintVisitor;
/**
 * CSVelte\IO Tests
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
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
