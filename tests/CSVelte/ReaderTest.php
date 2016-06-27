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
use CSVelte\Input\InputInterface;

class ReaderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    // just a simple test to get things started...
    public function testReaderAcceptsInputSource()
    {
        $stub = $this->createMock(InputInterface::class);
        $reader = new Reader($stub);
        $this->assertInstanceOf(Reader::class, $reader);
    }
}
