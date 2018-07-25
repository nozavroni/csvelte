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
use CSVelte\Writer;

use function CSVelte\to_stream;

class WriterTest extends UnitTestCase
{
    public function testInstantiateWriterWithNoDialectUsesDefault()
    {
        $stream = to_stream(fopen('php://temp', 'w+'));
        $writer = new Writer($stream);
        $this->assertInstanceOf(Dialect::class, $writer->getDialect());
    }

    public function testInstantiateWriterWithDialectCanChangeDialectWithSetDialect()
    {
        $stream = to_stream(fopen('php://temp', 'w+'));
        $dialect = new Dialect(['delimiter' => "\t"]);
        $writer = new Writer($stream);
        $this->assertNotSame($dialect, $writer->getDialect());
        $this->assertSame($writer, $writer->setDialect($dialect));
        $this->assertSame($dialect, $writer->getDialect());
        $this->assertEquals("\t", $writer->getDialect()->getDelimiter());
    }

    public function testWriterWritesToOutputStreamAccordingToDialect()
    {
        $stream = to_stream(fopen('php://temp', 'w+'));
        $dialect = new Dialect(['header' => false]);
        $writer = new Writer($stream, $dialect);
        $this->assertEquals(12, $writer->insertRow([
            'foo',
            'bar',
            'baz'
        ]));
        $this->assertEquals("foo,bar,baz\n", (string) $stream);
        $this->assertEquals(71, $writer->insertRow([
            'test',
            'this is a test, with a comma in it',
            'this is a "quoted" field'
        ]));
        $this->assertEquals("foo,bar,baz\ntest,\"this is a test, with a comma in it\",\"this is a \"\"quoted\"\" field\"\n", (string) $stream);
        $this->assertEquals(11, $writer->insertRow([
            1,
            '2',
            '3.5556'
        ]));
        $this->assertEquals("foo,bar,baz\ntest,\"this is a test, with a comma in it\",\"this is a \"\"quoted\"\" field\"\n1,2,3.5556\n", (string) $stream);
        $this->assertEquals(103, $writer->insertRow([
            "this field\nhas\nline breaks",
            'this field has a \' single quote',
            'this has? weird^*& characters!! ,.:?!2@'
        ]));
        $this->assertEquals("foo,bar,baz\ntest,\"this is a test, with a comma in it\",\"this is a \"\"quoted\"\" field\"\n1,2,3.5556\n\"this field\nhas\nline breaks\",this field has a ' single quote,\"this has? weird^*& characters!! ,.:?!2@\"\n", (string) $stream);
    }

    public function testInsertAllWritesMultipleRowsAndReturnsTotalWrittenBytes()
    {
        $stream = to_stream(fopen('php://temp', 'w+'));
        $dialect = new Dialect(['header' => false]);
        $writer = new Writer($stream, $dialect);
        $data = [
            ['1', 'luke@example.com', 'A short description', 'ON'],
            ['2', 'bob@example.com', 'What about "bob"?', 'OFF'],
            ['3', 'steve@example.com', 'The problem with steve, is steve.', 'ON'],
            ['4', 'joe@example.com', 'Hey Joe, where you goin\' with that gun in yo hand?', 'ON'],
        ];
        $this->assertEquals(219, $writer->insertAll($data));
        $this->assertEquals("1,luke@example.com,A short description,ON\n2,bob@example.com,\"What about \"\"bob\"\"?\",OFF\n3,steve@example.com,\"The problem with steve, is steve.\",ON\n4,joe@example.com,\"Hey Joe, where you goin' with that gun in yo hand?\",ON\n", (string) $stream);
    }
}
