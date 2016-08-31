<?php
namespace CSVelteTest\IO;

use CSVelte\IO\File;
use org\bovigo\vfs\vfsStream;
/**
 * CSVelte\IO\File Tests.
 * This tests the new IO\File class that will be replacing CSVelte\Input\File and
 * CSVelte\Output\File.
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

    /**
     * @covers ::readLine()
     */
    public function testReadLineReadsNextLineWithoutTrailingNewline()
    {
        $file = new File($this->getFilePathFor('veryShort'));
        $this->assertEquals("foo,bar,baz", $file->readLine());
        $this->assertEquals("bin,boz,bork", $file->readLine());
        $this->assertEquals("lib,bil,ilb", $file->readLine());
    }

    /**
     * @covers ::readLine()
     * @expectedException \RuntimeException
     */
    public function testReadLineThrowsRuntimeExceptionIfEofReached()
    {
        $file = new File($this->getFilePathFor('veryShort'));
        $this->assertEquals("foo,bar,baz", $file->readLine());
        $this->assertEquals("bin,boz,bork", $file->readLine());
        $this->assertEquals("lib,bil,ilb", $file->readLine());
        $file->readLine(); // this should trigger an exception
    }

    /**
     * @covers ::isEof()
     */
    public function testIsEofReturnsFalseUntilEofIsReached()
    {
        $file = new File($this->getFilePathFor('veryShort'));
        $this->assertFalse($file->isEof());
        $this->assertEquals("foo,bar,baz", $file->readLine());
        $this->assertFalse($file->isEof());
        $this->assertEquals("bin,boz,bork", $file->readLine());
        $this->assertFalse($file->isEof());
        $this->assertEquals("lib,bil,ilb", $file->readLine());
        $this->assertTrue($file->isEof());
    }

    /**
     * @covers ::readLine()
     */
    public function testReadLineReadsLinesWithoutRespectToQuotedNewlines()
    {
        $file = new File($this->getFilePathFor('shortQuotedNewlines'));
        $this->assertEquals("foo,bar,baz", $file->readLine());
        $this->assertEquals("bin,\"boz,bork", $file->readLine());
        $this->assertEquals("lib,bil,ilb\",bon", $file->readLine());
        $this->assertEquals("bib,bob,\"boob", $file->readLine());
        $this->assertEquals("boober\"", $file->readLine());
        $this->assertEquals("cool,pool,wool", $file->readLine());
    }

    /**
     * @covers ::write()
     */
    public function testCreateNewFileAndWriteToIt()
    {
        $data = $this->getFileContentFor('veryShort');
        $file = new File($fn = $this->root->url() ."/tempfile1.csv", ['open_mode' => 'w']);
        $this->assertEquals(strlen($data), $file->write($data));
        $this->assertEquals($data, file_get_contents($fn));
    }

    /**
     * @covers ::write()
     */
    public function testAppendFileWrite()
    {
        $file = new File($fn = $this->getFilePathFor('shortQuotedNewlines'), ['open_mode' => 'a']);
        $data = "\"foo, bar\",boo,far\n";
        $this->assertEquals(strlen($data), $file->write($data));
        $this->assertEquals(
            "foo,bar,baz\nbin,\"boz,bork\nlib,bil,ilb\",bon\nbib,bob,\"boob\nboober\"\ncool,pool,wool\n" . $data,
            file_get_contents($fn)
        );
    }

    /**
     * @covers ::write()
     */
    public function testFileOverWrite()
    {
        $file = new File($fn = $this->getFilePathFor('shortQuotedNewlines'), ['open_mode' => 'w']);
        $data = "\"foo, bar\",boo,far\n";
        $this->assertEquals(strlen($data), $file->write($data));
        $this->assertEquals(
            $data, 
            file_get_contents($fn)
        );
    }
}
