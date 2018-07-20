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
namespace CSVelteTest;

use CSVelte\Dialect;
use CSVelte\Reader;

use function CSVelte\to_stream;
use function Noz\collect;

class ReaderTest extends UnitTestCase
{
    public function testInstantiateReaderWithoutDialectUsesDefault()
    {
        $source = fopen($this->getFilePathFor('veryShort'), 'r+');
        $reader = new Reader(to_stream($source));
        $this->assertInstanceOf(Dialect::class, $reader->getDialect());
    }

    public function testInstantiateReaderWithCustomDialectUsesCustomDialect()
    {
        $source = fopen($this->getFilePathFor('veryShort'), 'r+');
        $dialect = new Dialect([
            'header' => false,
        ]);
        $reader = new Reader(to_stream($source), $dialect);
        $this->assertInstanceOf(Dialect::class, $reader->getDialect());
        $this->assertSame($dialect, $reader->getDialect());
        $this->assertFalse($reader->getDialect()->hasHeader());
    }

    public function testSetDialectDoesTheSameAsSettingItInConstructor()
    {
        $source = fopen($this->getFilePathFor('veryShort'), 'r+');
        $dialect = new Dialect([
            'header' => false,
        ]);
        $reader = new Reader(to_stream($source));
        $this->assertInstanceOf(Dialect::class, $reader->getDialect());
        $this->assertNotSame($dialect, $reader->getDialect());
        $this->assertTrue($reader->getDialect()->hasHeader());
        $reader->setDialect($dialect);
        $this->assertSame($dialect, $reader->getDialect());
        $this->assertFalse($reader->getDialect()->hasHeader());
    }

    public function testSetDialectRewindsAndResetsReader()
    {
        $source = fopen($this->getFilePathFor('commaNewlineHeader'), 'r+');
        $dialect = new Dialect([
            'header' => false,
        ]);
        $reader = new Reader(to_stream($source), $dialect);
        $this->assertSame([
            0 => 'Bank Name',
            1 => 'City',
            2 => 'ST',
            3 => 'CERT',
            4 => 'Acquiring Institution',
            5 => 'Closing Date',
            6 => 'Updated Date'
        ], $reader->current());

        $newdialect = new Dialect(['header' => true]);
        $reader->setDialect($newdialect);
        $this->assertSame([
            'Bank Name' => 'First CornerStone Bank',
            'City' => "King of\nPrussia",
            'ST' => 'PA',
            'CERT' => '35312',
            'Acquiring Institution' => 'First-Citizens Bank & Trust Company',
            'Closing Date' => '6-May-16',
            'Updated Date' => '25-May-16'
        ], $reader->current());
    }

    /** BEGIN: SPL implementation method tests */

    public function testCurrentReturnsCurrentLineFromInput()
    {
        $source = fopen($this->getFilePathFor('commaNewlineHeader'), 'r+');
        $dialect = new Dialect([
            'header' => false,
        ]);
        $reader = new Reader(to_stream($source), $dialect);
        $this->assertSame([
            0 => 'Bank Name',
            1 => 'City',
            2 => 'ST',
            3 => 'CERT',
            4 => 'Acquiring Institution',
            5 => 'Closing Date',
            6 => 'Updated Date'
        ], $reader->current());
    }

    public function testNextMovesInputToNextLineAndLoadsItIntoMemory()
    {
        $source = fopen($this->getFilePathFor('commaNewlineHeader'), 'r+');
        $dialect = new Dialect([
            'header' => false,
        ]);
        $reader = new Reader(to_stream($source), $dialect);
        $this->assertSame([
            0 => 'Bank Name',
            1 => 'City',
            2 => 'ST',
            3 => 'CERT',
            4 => 'Acquiring Institution',
            5 => 'Closing Date',
            6 => 'Updated Date'
        ], $reader->current());
        $this->assertSame($reader, $reader->next());
        $this->assertSame([
            'First CornerStone Bank',
            "King of\nPrussia",
            'PA',
            '35312',
            'First-Citizens Bank & Trust Company',
            '6-May-16',
            '25-May-16'
        ], $reader->current());
    }

    public function testKeyReturnsLineNumber()
    {
        $source = fopen($this->getFilePathFor('commaNewlineHeader'), 'r+');
        $dialect = new Dialect([
            'header' => false,
        ]);
        $reader = new Reader(to_stream($source), $dialect);
        $this->assertSame([
            0 => 'Bank Name',
            1 => 'City',
            2 => 'ST',
            3 => 'CERT',
            4 => 'Acquiring Institution',
            5 => 'Closing Date',
            6 => 'Updated Date'
        ], $reader->current());
        $this->assertSame(1, $reader->key());
    }

    public function testKeyReturnsLineNumberNotIncludingHeaderLine()
    {
        $source = fopen($this->getFilePathFor('commaNewlineHeader'), 'r+');
        $reader = new Reader(to_stream($source));
        $this->assertSame(1, $reader->key());
        $this->assertSame([
            'Bank Name' => 'First CornerStone Bank',
            'City' => "King of\nPrussia",
            'ST' => 'PA',
            'CERT' => '35312',
            'Acquiring Institution' => 'First-Citizens Bank & Trust Company',
            'Closing Date' => '6-May-16',
            'Updated Date' => '25-May-16'
        ], $reader->current());
    }

    public function testValidReturnsFalseIfInputIsAtEOF()
    {
        $source = fopen($this->getFilePathFor('commaNewlineHeader'), 'r+');
        $stream = to_stream($source);
        $reader = new Reader($stream);
        $this->assertFalse($stream->eof());
        $this->assertTrue($reader->valid());
        $stream->seek($stream->getSize()+1);
        $this->assertTrue($stream->eof());
        $this->assertFalse($reader->valid());
    }

    public function testRewindResetsReaderToBeginning()
    {
        $source = fopen($this->getFilePathFor('commaNewlineHeader'), 'r+');
        $stream = to_stream($source);
        $reader = new Reader($stream);
        $this->assertSame([
            'Bank Name' => 'Trust Company Bank',
            'City' => 'Memphis',
            'ST' => 'TN',
            'CERT' => '9956',
            'Acquiring Institution' => 'The Bank of Fayette County',
            'Closing Date' => '29-Apr-16',
            'Updated Date' => '25-May-16'
        ], $reader->next()->current());
        $this->assertSame($reader, $reader->rewind());
        $this->assertSame([
            'Bank Name' => 'First CornerStone Bank',
            'City' => "King of\nPrussia",
            'ST' => 'PA',
            'CERT' => '35312',
            'Acquiring Institution' => 'First-Citizens Bank & Trust Company',
            'Closing Date' => '6-May-16',
            'Updated Date' => '25-May-16'
        ], $reader->current());
    }

    public function testCountReturnsNumberOfLines()
    {
        $dialect = new Dialect(['header' => false]);
        $source = fopen($this->getFilePathFor('commaNewlineHeader'), 'r+');
        $reader = new Reader(to_stream($source), $dialect);
        $this->assertEquals(29, $reader->count());
        $this->assertEquals(29, count($reader));
        $reader->setDialect(new Dialect(['header' => true]));
        $this->assertEquals(28, $reader->count());
        $this->assertEquals(28, count($reader));
    }
}