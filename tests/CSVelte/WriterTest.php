<?php
use PHPUnit\Framework\TestCase;
use CSVelte\Writer;
use CSVelte\Flavor;
/**
 * CSVelte\Writer Tests
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class WriterTest extends TestCase
{
    /**
     * Just a basic test to get started
     */
    public function testWriterUsesDefaultFlavor()
    {
        $writer = new CSVelte\Writer();
        $this->assertInstanceOf(CSVelte\Flavor::class, $writer->getFlavor());
    }
}
