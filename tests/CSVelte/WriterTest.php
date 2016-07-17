<?php
use PHPUnit\Framework\TestCase;
use CSVelte\Writer;
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
    protected $writable;

    public function setUp()
    {
        $writable = $this->createMock(Writable::class);
        $writable->expects($this->any())
            ->method('write')
            ->will($this->returnCallback(function(){ $data = func_get_arg(0); return strlen($data); }));
        $this->writable = $writable;
    }

    public function testFoo()
    {
        $this->assertEquals($foo = "I am going to write writable tests first I guess", $foo);
    }

    // /**
    //  * Just a basic test to get started
    //  */
    // public function testWriterUsesDefaultFlavor()
    // {
    //     $writer = new CSVelte\Writer($this->writable);
    //     $this->assertInstanceOf(CSVelte\Flavor::class, $writer->getFlavor());
    // }
    //
    // public function testWriterUsesWritable()
    // {
    //     $writer = new CSVelte\Writer($this->writable);
    //     $data = "Write this to the writable.";
    //     $expected = strlen($data);
    //     $this->assertEquals($expected, $this->writable->write($data));
    //     $row = array('foo','bar','baz','bin');
    //     $written = implode(",", $row) . "\r\n";
    //     $this->assertEquals($expected = strlen($written), $writer->writeRow($row));
    // }
}
