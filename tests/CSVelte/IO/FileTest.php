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
use CSVelte\IO\File;
/**
 * CSVelte\IO Tests
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @coversDefaultClass CSVelte\IO\File
 */
class FileTest extends IOTest
{
    /**
     * @covers ::__construct()
     */
    public function testInstantiateIOFileCreatesFile()
    {
        $filename = $this->root->url() . '/deleteme.csv';
        $this->assertFileNotExists($filename);
        $file = new File($filename);
        $this->assertFileExists($filename);
    }

    /**
     * @expectedException CSVelte\Exception\FileNotFoundException
     * @expectedExceptionCode 1
     * @covers ::__construct()
     */
    public function testInstantiateIOFileInNonExistantFileThrowsExceptionIfCreateOptionIsFalse()
    {
        $filename = $this->root->url() . '/deletemetoo.csv';
        $file = new File($filename, ['create' => false]);
    }

    /**
     * @expectedException CSVelte\Exception\FileNotFoundException
     * @expectedExceptionCode 2
     * @covers ::__construct()
     */
    public function testInstantiateIOFileInNonExistantDirectoryThrowsException()
    {
        $filename = $this->root->url() . '/makethisdir/deleteme.csv';
        $file = new File($filename);
    }

    /**
     * @covers ::__construct()
     */
    public function testInsantiateIOFileInNonExistantDirectoryCreatesDirectoryAndFileIfParentsOptionIsTrue()
    {
        $filename = $this->root->url() . '/makethisdir/deleteme.csv';
        $dirname = dirname($filename);
        $this->assertFileNotExists($dirname);
        $file = new File($filename, ['parents' => true]);
        $this->assertFileExists($dirname);
    }

    // public function testInstantiateIOFileAllowsSettingMode()
    // {
    //     $filename = $this->root->url() . '/makethisdir/deleteme.csv';
    //     $file = new File($filename, ['mode' => 0755]);
    // }
}
