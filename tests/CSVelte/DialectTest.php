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

class DialectTest extends UnitTestCase
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
        $this->assertSame(Dialect::TRIM_ALL, $dialect->getTrim());
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
            ->setHeaderRowCount(2)
            ->setLineTerminators(["\r"])
            ->setQuoteChar("'")
            ->setIsSkipBlankRows(true)
            ->setSkipColumns(1)
            ->setIsSkipInitialSpace(true)
            ->setSkipRows(1)
            ->setTrim(Dialect::TRIM_NONE)
            ->setQuoteStyle(Dialect::QUOTE_ALL);

        $this->assertSame('//', $dialect->getCommentPrefix());
        $this->assertSame("\t", $dialect->getDelimiter());
        $this->assertFalse($dialect->isDoubleQuote());
        $this->assertSame("utf-16", $dialect->getEncoding());
        $this->assertFalse($dialect->hasHeader());
        $this->assertSame(2, $dialect->getHeaderRowCount());
        $this->assertSame(["\r"], $dialect->getLineTerminators());
        $this->assertSame("'", $dialect->getQuoteChar());
        $this->assertTrue($dialect->isSkipBlankRows());
        $this->assertSame(1, $dialect->getSkipColumns());
        $this->assertTrue($dialect->isSkipInitialSpace());
        $this->assertSame(1, $dialect->getSkipRows());
        $this->assertSame(Dialect::TRIM_NONE, $dialect->getTrim());
        $this->assertSame(Dialect::QUOTE_ALL, $dialect->getQuoteStyle());
    }

    public function testDialectAllowsAllAttributesToBeSetFromConstructor()
    {
        $dialect = new Dialect([
            'commentPrefix' => "//",
            'delimiter' => "\t",
            'doubleQuote' => false,
            'encoding' => "utf-16",
            'header' => false,
            'headerRowCount' => 2,
            'lineTerminators' => ["\r"],
            'quoteChar' => "'",
            'skipBlankRows' => true,
            'skipColumns' => 1,
            'skipInitialSpace' => true,
            'skipRows' => 1,
            'trim' => Dialect::TRIM_START,
            'quoteStyle' => Dialect::QUOTE_ALL
        ]);

        $this->assertSame('//', $dialect->getCommentPrefix());
        $this->assertSame("\t", $dialect->getDelimiter());
        $this->assertFalse($dialect->isDoubleQuote());
        $this->assertSame("utf-16", $dialect->getEncoding());
        $this->assertFalse($dialect->hasHeader());
        $this->assertSame(2, $dialect->getHeaderRowCount());
        $this->assertSame(["\r"], $dialect->getLineTerminators());
        $this->assertSame("'", $dialect->getQuoteChar());
        $this->assertTrue($dialect->isSkipBlankRows());
        $this->assertSame(1, $dialect->getSkipColumns());
        $this->assertTrue($dialect->isSkipInitialSpace());
        $this->assertSame(1, $dialect->getSkipRows());
        $this->assertSame(Dialect::TRIM_START, $dialect->getTrim());
        $this->assertSame(Dialect::QUOTE_ALL, $dialect->getQuoteStyle());
    }

    public function testSettersTryToBeFlexible()
    {
        $dialect = new Dialect;
        $dialect->setLineTerminators("\n");
        $this->assertSame(["\n"], $dialect->getLineTerminators());
    }
}