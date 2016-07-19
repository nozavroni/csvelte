<?php

use PHPUnit\Framework\TestCase;
use CSVelte\Table\Data;
use CSVelte\Table\Data\Numeric;

/**
 * CSVelte\Table\Data Tests
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class TableDataTest extends TestCase
{
    public function testDataFactoryNumericFromString()
    {
        $intgr = Data::fromString('423');
        $this->assertInstanceOf(Data\Numeric::class, $intgr);
        $this->assertEquals($expected = new Data\Numeric(423), $intgr);

        $negint = Data::fromString('-423');
        $this->assertInstanceOf(Data\Numeric::class, $negint);
        $this->assertEquals($expected = new Data\Numeric(-423), $negint);

        $decimal = Data::fromString('4.23');
        $this->assertInstanceOf(Data\Numeric::class, $decimal);
        $this->assertEquals($expected = new Data\Numeric(4.23), $decimal);

        $negdec = Data::fromString('-4.23');
        $this->assertInstanceOf(Data\Numeric::class, $negdec);
        $this->assertEquals($expected = new Data\Numeric(-4.23), $negdec);

        // $exp = Data::fromString('2**16');
        // $this->assertInstanceOf(Data\Numeric::class, $exp);
        // $this->assertEquals($expected = new Data\Numeric(65536), $exp);
        //
        // $decexp = Data::fromString('2.4**16');
        // $this->assertInstanceOf(Data\Numeric::class, $decexp);
        // $this->assertEquals($expected = new Data\Numeric(1211657.47909451), $decexp);
        //
        // $negdecexp = Data::fromString('-2.4**16');
        // $this->assertInstanceOf(Data\Numeric::class, $negdecexp);
        // $this->assertEquals($expected = new Data\Numeric(-1211657.47909451), $negdecexp);
        //
        // $negexp = Data::fromString('-2**16');
        // $this->assertInstanceOf(Data\Numeric::class, $negexp);
        // $this->assertEquals($expected = new Data\Numeric(-65536), $negexp);

        // @todo I'm damn near certain I'm doing several things wrong here but I'll leave it for now...

        $scinot = Data::fromString('1.423E-11');
        $this->assertInstanceOf(Data\Numeric::class, $scinot);
        $expected = new Data\Numeric(1.423E-11);
        $this->assertEquals($expected->__toString(), '1.423E-11');

        $negscinot = Data::fromString('-1.423E-11');
        $this->assertInstanceOf(Data\Numeric::class, $scinot);
        $expected = new Data\Numeric(-1.423E-11);
        $this->assertEquals($expected->__toString(), '-1.423E-11');

        $scinotdec = Data::fromString('.423E-11');
        $this->assertInstanceOf(Data\Numeric::class, $scinot);
        $expected = new Data\Numeric(.423E-11);
        $this->assertEquals($expected->__toString(), '0.423E-11');
    }
}
