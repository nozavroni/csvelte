<?php
namespace CSVelteTest;

use CSVelte\IO\File;
use CSVelte\Reader;
use org\bovigo\vfs\vfsStream;
/**
 * CSVelte\Reader Tests.
 * New Format for refactored tests -- see issue #11
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @todo      Move all of the tests from OldReaderTest.php into this class
 * @coversDefaultClass CSVelte\Reader
 */
class ReaderTest extends UnitTestCase
{
    /**
     * @covers ::__construct()
     */
    public function testReaderCanUseIOFileReadable()
    {
        $readable = new File($this->getFilePathFor('shortQuotedNewlines'));
        $reader = new Reader($readable);
        $this->assertEquals(['foo','bar','baz'], $reader->current()->toArray());
        $this->assertEquals(['bin',"boz,bork\nlib,bil,ilb",'bon'], $reader->next()->toArray());
    }

}
