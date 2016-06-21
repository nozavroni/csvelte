<?php
/**
 * CSVelte\FlavorTest
 *
 * @package   CSVelte\Flavor Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
use PHPUnit\Framework\TestCase;
use Mockery as m;
use Mockery\Adapter\PHPUnit\MockeryPHPUnitIntegration;
use CSVelte\Flavor;

class FlavorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testCSVelteFlavor()
    {
        $this->assertInstanceOf($expected = 'CSVelte\Flavor', new Flavor);
    }
}
