<?php
use PHPUnit\Framework\TestCase;
use CSVelte\Writer;
use CSVelte\Output\Stream;
use CSVelte\Contract\Writable;
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
    public function testWriterCustomFlavor()
    {
        $out = new Stream('php://memory');
        $writer = new Writer($out, $expectedFlavor = new Flavor(array('delimiter' => '|')));
        $this->assertSame($expectedFlavor, $writer->getFlavor());
    }

    public function testWriterWriteWriteSingleRowUsingArray()
    {
        $out = new Stream('php://memory');
        $writer = new Writer($out);
        $data = array('one','two', 'three');
        $this->assertEquals(strlen(implode(',', $data)) + strlen("\r\n"), $writer->writeRow($data));
    }
}
