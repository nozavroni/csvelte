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
use CSVelte\Reader;

class ReaderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * Just a simple test to get things started...
     */
    public function testCSVelteReader()
    {
        $this->assertInstanceOf($expected = 'CSVelte\Reader', new CSVelte\Reader);
    }
}
