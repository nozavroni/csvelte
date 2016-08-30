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
use org\bovigo\vfs\vfsStream;
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

    /**
     * @covers ::__construct()
     */
    public function testInstantiateIOFileModeDefaultsTo0644()
    {
        $filename = $this->root->url() . '/deleteme.csv';
        $file = new File($filename);
        $perms = substr(sprintf('%o', fileperms($filename)), -4);
        $this->assertEquals($expected = '0644', $perms);
    }

    /**
     * @covers ::__construct()
     */
    public function testInstantiateIOFileAllowsSettingModeForFile()
    {
        $filename = $this->root->url() . '/deleteme.csv';
        $file = new File($filename, ['mode' => 0777]);
        $perms = substr(sprintf('%o', fileperms($filename)), -4);
        $this->assertEquals($expected = '0777', $perms);
    }

    /**
     * @covers ::__construct()
     */
    public function testInstantiateIOFileModeDefaultsTo0644ForCreatedParentDirs()
    {
        $filename = $this->root->url() . '/makethisdir/deleteme.csv';
        $file = new File($filename, ['parents' => true]);
        $perms = substr(sprintf('%o', fileperms(dirname($filename))), -4);
        $this->assertEquals($expected = '0644', $perms);
    }

    /**
     * @covers ::__construct()
     */
    public function testInstantiateIOFileAllowsSettingModeForCreatedParentDirs()
    {
        $filename = $this->root->url() . '/makethisdir/deleteme.csv';
        $file = new File($filename, ['mode' => 0777, 'parents' => true]);
        $perms = substr(sprintf('%o', fileperms(dirname($filename))), -4);
        $this->assertEquals($expected = '0777', $perms);
    }

    /**
     * @covers ::read()
     */
    public function testReadGetsCorrectNumberOfChars()
    {
        $file = new File($this->getFilePathFor('commaNewlineHeader'));
        $this->assertEquals("Bank Name,City,ST,CERT,Ac", $file->read(25));
    }

    /**
     * @covers ::read()
     */
    public function testReadGetsAllCharsIfMoreAreRequestedThanAreAvailable()
    {
        $file = new File($this->getFilePathFor('veryShort'));
        $this->assertEquals("foo,bar,baz\nbin,boz,bork\nlib,bil,ilb\n", $file->read(250));
    }
}
