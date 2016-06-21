<?php

use PHPUnit\Framework\TestCase;
use Mockery as m;
use Mockery\Adapter\PHPUnit\MockeryPHPUnitIntegration;
use CSVelte\Flavor;

/**
 * CSVelte\FlavorTest
 *
 * @package   CSVelte\Flavor Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class FlavorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * This is just a really simple test to get things started...
     */
    public function testCSVelteFlavor()
    {
        $this->assertInstanceOf($expected = 'CSVelte\Flavor', new Flavor);
    }

    /**
     * Test that CSVelte\Flavor provides reasonable default values for its attributes
     */
    public function testCSVelteFlavorDefaults()
    {
        $flavor = new Flavor;
        $this->assertEquals($delimiter = ",", $flavor->delimiter);
        $this->assertEquals($quoteChar = "\"", $flavor->quoteChar);
        $this->assertEquals($escapeChar = "\\", $flavor->escapeChar);
        $this->assertEquals($lineTerminator = "\n", $flavor->lineTerminator);
        // @todo add this later (as it becomes needed)
        // $this->assertEquals($quoting = 0, $flavor->quoting);
    }

    /**
     * @expectedException CSVelte\Exception\UnknownAttributeException
     */
    public function testCSVelteFlavorNonExistAttribute()
    {
        $flavor = new Flavor;
        $foo = $flavor->foo;
    }
}
