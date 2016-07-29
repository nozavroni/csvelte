<?php
/**
 * CSVelteTest
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
use PHPUnit\Framework\TestCase;
use Mockery as m;
use Mockery\Adapter\PHPUnit\MockeryPHPUnitIntegration;
use CSVelte\CSVelte;

class CSVelteTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * Just a simple test to get things started...
     */
    public function testCSVelte()
    {
        $this->assertInstanceOf($expected = 'CSVelte\CSVelte', new CSVelte);
    }

    /**
     * Test that import method will throw an exception if file doesn't exist
     * @expectedException CSVelte\Exception\FileNotFoundException
     */
    public function testImportThrowsExceptionWhenFileDoesntExist()
    {
        $csv = new CSVelte();
        $file = m::mock('CSVelte\File');
        $file->shouldReceive(['exists' => false]);
        $csv->import($file);
    }

    /**
     * Test that import method will throw an exception if file permissions don't
     * allow read access
     * @expectedException CSVelte\Exception\FileNotFoundException
     */
    // public function testImportThrowsExceptionWhenFileExistsButPermissionDenied()
    // {
    //
    // }

    /**
     * Test that CSVelte returns an array when headers are requested
     */
    public function testCSVelteGetHeaders()
    {
        $csv = new CSVelte();
        // $csv->import("./files/sample1.csv");
        $this->assertInternalType('array', $csv->headers());
    }
}
