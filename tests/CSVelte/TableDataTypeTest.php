<?php

use PHPUnit\Framework\TestCase;
use CSVelte\Table\Data;
use CSVelte\Table\DataType\Numeric;
use CSVelte\Table\DataType\Text;
use CSVelte\Table\DataType\Boolean;
use CSVelte\Table\DataType\DateTime;
use CSVelte\Table\DataType\Duration;
use CSVelte\Table\DataType\Null;

/**
 * CSVelte\Table\DataType Tests
 * DataTypes, as the name implies, represent the data type of a particular data
 * cell or column or item, whatever you want to call it. I have a set of pre-
 * defined datatypes and then a custom datatype that can be customized using a
 * regular expression for validation and type detection/conversion from a string.
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class TableDataTypeTest extends TestCase
{
    // public function testDataItemCanDetermineItsOwnTypeFromString()
    // {
    //     $dataItem = new Data('500');
    //     $this->assertEquals('numeric', $dataItem->getType());
    // }

    public function testTextDataTypeCastWithNoParamsWillInferCastTypeFromValue()
    {
        $numeric = new Text("500");
        $this->assertInstanceOf(Numeric::class, $fivehundo = $numeric->cast());
        $this->assertEquals(Text::TYPE_NUMERIC, $fivehundo->getType());
        $this->assertTrue(is_int($fivehundo->getValue()));
        $this->assertSame(500, $fivehundo->getValue());
    }
}
