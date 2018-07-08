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
use PHPUnit_Framework_TestCase;

class DialectTest extends PHPUnit_Framework_TestCase
{
    public function testDefaultDialectAttributesAreConsistentWithCSVW()
    {
        $dialect = new Dialect;
        $this->assertSame("#", $dialect->getCommentPrefix());
        $this->assertSame(",", $dialect->getDelimiter());
        $this->assertTrue($dialect->isDoubleQuote());
        $this->assertSame("utf-8", $dialect->getEncoding());
        $this->assertTrue($dialect->hasHeader());
        $this->assertSame(["\r\n", "\n"], $dialect->getLineTerminators());
        $this->assertSame('"', $dialect->getQuoteChar());
        $this->assertFalse($dialect->isSkipBlankRows());
        $this->assertSame(0, $dialect->getSkipColumns());
        $this->assertFalse($dialect->isSkipInitialSpace());
        $this->assertSame(0, $dialect->getSkipRows());
        $this->assertFalse($dialect->isTrim());
        $this->assertSame(Dialect::QUOTE_MINIMAL, $dialect->getQuoteStyle());
    }

    public function testDefaultDialectAllowsOverwritingAttributesWithSetters()
    {
        $dialect = new Dialect;
        $dialect->setCommentPrefix('//')
            ->setDelimiter("\t")
            ->setIsDoubleQuote(false)
            ->setEncoding("utf-16")
            ->setHasHeader(false)
            ->setLineTerminators(["\r"])
            ->setQuoteChar("'")
            ->setIsSkipBlankRows(true)
            ->setSkipColumns(1)
            ->setIsSkipInitialSpace(true)
            ->setSkipRows(1)
            ->setIsTrim(true)
            ->setQuoteStyle(Dialect::QUOTE_ALL);

        $this->assertSame('//', $dialect->getCommentPrefix());
        $this->assertSame("\t", $dialect->getDelimiter());
        $this->assertFalse($dialect->isDoubleQuote());
        $this->assertSame("utf-16", $dialect->getEncoding());
        $this->assertFalse($dialect->hasHeader());
        $this->assertSame(["\r"], $dialect->getLineTerminators());
        $this->assertSame("'", $dialect->getQuoteChar());
        $this->assertTrue($dialect->isSkipBlankRows());
        $this->assertSame(1, $dialect->getSkipColumns());
        $this->assertTrue($dialect->isSkipInitialSpace());
        $this->assertSame(1, $dialect->getSkipRows());
        $this->assertTrue($dialect->isTrim());
        $this->assertSame(Dialect::QUOTE_ALL, $dialect->getQuoteStyle());
    }
}