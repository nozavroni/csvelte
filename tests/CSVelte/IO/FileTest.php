<?php
namespace CSVelteTest\IO;

use \SplFileObject;
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
        $file = new File($filename, 'w+');
        $this->assertFileExists($filename);
    }

    /**
     * @covers ::__construct()
     */
    public function testUseIncludePathOptionTrue()
    {
        $filename = 'incpathtmp.csv';
        touch($filepath = $this->tmpdir . DIRECTORY_SEPARATOR . $filename);
        file_put_contents($filepath, $this->getFileContentFor('veryShort'));
        set_include_path(
            $this->tmpdir . PATH_SEPARATOR .
            get_include_path()
        );
        $file = new File($filename, 'r+', true);
        $this->assertEquals("foo,bar,baz\nbin,boz,bork\n", $file->read(25));
    }

    /**
     * @covers ::fread()
     */
    public function testFreadGetsCorrectNumberOfChars()
    {
        $file = new File($this->getFilePathFor('commaNewlineHeader'));
        $this->assertEquals("Bank Name,City,ST,CERT,Ac", $file->read(25));
    }

    /**
     * @covers ::fread()
     */
    public function testFreadGetsAllCharsIfMoreAreRequestedThanAreAvailable()
    {
        $file = new File($this->getFilePathFor('veryShort'));
        $this->assertEquals("foo,bar,baz\nbin,boz,bork\nlib,bil,ilb\n", $file->read(250));
    }

    /**
     * @covers ::fgets()
     */
    public function testFgetsReadsNextLineWithTrailingNewline()
    {
        $file = new File($this->getFilePathFor('veryShort'));
        $this->assertEquals("foo,bar,baz\n", $file->readLine("\n"));
        $this->assertEquals("bin,boz,bork\n", $file->readLine("\n"));
        $this->assertEquals("lib,bil,ilb\n", $file->readLine("\n"));
    }

    /**
     * @covers ::fgets()
     * @expectedException \RuntimeException
     */
    public function testFgetsThrowsRuntimeExceptionIfEofReached()
    {
        $file = new File($this->getFilePathFor('veryShort'));
        $this->assertEquals("foo,bar,baz\n", $file->readLine("\n"));
        $this->assertEquals("bin,boz,bork\n", $file->readLine("\n"));
        $this->assertEquals("lib,bil,ilb\n", $file->readLine("\n"));
        $file->fgets(); // this should trigger an exception
    }

    /**
     * @covers ::eof()
     */
    public function testEofReturnsFalseUntilEofIsReached()
    {
        $file = new File($this->getFilePathFor('veryShort'));
        $this->assertFalse($file->eof());
        $this->assertEquals("foo,bar,baz\n", $file->readLine("\n"));
        $this->assertFalse($file->eof());
        $this->assertEquals("bin,boz,bork\n", $file->readLine("\n"));
        $this->assertFalse($file->eof());
        $this->assertEquals("lib,bil,ilb\n", $file->readLine("\n"));
        $this->assertTrue($file->eof());
    }

    /**
     * @covers ::fgets()
     */
    public function testFgetsReadsLinesWithoutRespectToQuotedNewlines()
    {
        $file = new File($this->getFilePathFor('shortQuotedNewlines'));
        $this->assertEquals("foo,bar,baz\n", $file->readLine("\n"));
        $this->assertEquals("bin,\"boz,bork\n", $file->readLine("\n"));
        $this->assertEquals("lib,bil,ilb\",bon\n", $file->readLine("\n"));
        $this->assertEquals("bib,bob,\"boob\n", $file->readLine("\n"));
        $this->assertEquals("boober\"\n", $file->readLine("\n"));
        $this->assertEquals("cool,pool,wool\n", $file->readLine("\n"));
    }

    /**
     * @covers ::write()
     */
    public function testCreateNewFileAndWriteToIt()
    {
        $data = $this->getFileContentFor('veryShort');
        $file = new File($fn = $this->root->url() ."/tempfile1.csv", 'w');
        $this->assertEquals(strlen($data), $file->write($data));
        $this->assertEquals($data, file_get_contents($fn));
    }

    /**
     * @covers ::write()
     */
    public function testAppendFileWrite()
    {
        $file = new File($fn = $this->getFilePathFor('shortQuotedNewlines'), 'a');
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
        $file = new File($fn = $this->getFilePathFor('shortQuotedNewlines'), 'w');
        $data = "\"foo, bar\",boo,far\n";
        $this->assertEquals(strlen($data), $file->write($data));
        $this->assertEquals(
            $data,
            file_get_contents($fn)
        );
    }

    /**
     * @covers ::seek()
     */
    public function testseekReturnsZeroOnSuccess()
    {
        $file = new File($this->getFilePathFor('shortQuotedNewlines'));
        $this->assertEquals(0, $file->seek(10));
    }

    public function testseekPlacesPointerInPositionForReadAndWriteIfOpenModeIsRPlus()
    {
        $file = new File($fn = $this->getFilePathFor('shortQuotedNewlines'), 'r+');
        $this->assertEquals(0, $file->seek(10), 'CSVelte\\IO\\File::seek() should return zero on success.');
        $this->assertEquals("z\nbin,\"boz", $file->fread(10), 'CSVelte\\IO\\File::seek() should cause fread() to start form sought position.');
        $this->assertEquals(10, $file->write('skaggzilla'));
        $this->assertEquals("foo,bar,baz\nbin,\"bozskaggzillabil,ilb\",bon\nbib,bob,\"boob\nboober\"\ncool,pool,wool\n", file_get_contents($fn), 'CSVelte\\IO\\File::write() should start overwriting wherever it\'s seek\'d to if open mode is r+.');
    }
}
