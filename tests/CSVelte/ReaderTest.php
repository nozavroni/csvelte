<?php
/**
 * CSVelte\Reader Tests
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
use PHPUnit\Framework\TestCase;
use Mockery as m;
use Mockery\Adapter\PHPUnit\MockeryPHPUnitIntegration;
use CSVelte\Reader as CSVReader;
use CSVelte\File as CSVFile;

class ReaderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected function getFileMock($filename = '../files/sample.csv')
    {
        $file = m::mock(CSVFile::class);
        $file->shouldReceive(['exists' => true, 'filename' => $filename]);
        return $file;
    }

    /**
     * Just a simple test to get things started...
     */
    public function testCSVelteReader()
    {
        $file = $this->getFileMock('../files/sample.csv');
        $this->assertInstanceOf($expected = CSVReader::class, new CSVReader($file));
    }

    public function testReaderAcceptsFile()
    {
        $file = $this->getFileMock('../files/sample.csv');
        $reader = new CSVReader($file);
        $this->assertInstanceOf(CSVFile::class, $reader->file());
    }

    public function testReaderCountsRows()
    {
        $file = $this->getFileMock('../files/sample.csv');
        $reader = new CSVReader($file);
        $this->assertEquals($expected = 100, $reader->count());
        $this->assertEquals($expected, count($reader));
    }
}
