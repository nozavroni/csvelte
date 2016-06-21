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
     * Test that CSVelte can read a row
     */
    public function testCSVelteGetHeaders()
    {
        $csv = new CSVelte();
        $csv->import("./files/sample1.csv");
        $this->assertInternalType('array', $csv->headers());
    }
}
