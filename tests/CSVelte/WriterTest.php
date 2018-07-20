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
        $dialect = new Dialect(['delimiter' => ',']);
        $writer = new Writer($stream);
        $this->assertNotSame($dialect, $writer->getDialect());
        $this->assertSame($writer, $writer->setDialect($dialect));
        $this->assertSame($dialect, $writer->getDialect());
    }
}