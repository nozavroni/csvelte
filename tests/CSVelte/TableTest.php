<?php

use PHPUnit\Framework\TestCase;
use CSVelte\Table\Table;
use CSVelte\Table\Data;
use CSVelte\Table\DataType\Numeric;
use CSVelte\Table\DataType\Text;
use CSVelte\Table\DataType\Boolean;
use CSVelte\Table\DataType\DateTime;
use CSVelte\Table\DataType\Duration;
use CSVelte\Table\DataType\Null;

/**
 * CSVelte\Table\Table Tests
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class TableTest extends TestCase
{
    public function testCreateTable()
    {
        $table = new Table();
        $this->assertInstanceOf(Table::class, $table);
    }

    // public function testLetsSeeHowOneMightUseATableObjectWhenWritingACSVFile()
    // {
    //     $out = new CSVelte\Output\Stream('file:///files/foo.csv');
    //     $writer = new CSVelte\Writer($out);
    //
    //     $in = new CSVelte\Input\Stream('file:///files/fee.csv');
    //     $reader = new CSVelte\Reader($in, $flavor);
    //
    //     // How tables would work...
    //     $table = $reader->generateTable();
    //     $table->setBufferLength(100); // 100 lines would be read into table at a time
    //     $header = $table->getHeaderRow();
    //     foreach ($table as $row_no => $row) {
    //         foreach ($row as $col_no => $data) {
    //             $data->getType(); // uses entire column to infer data type from string
    //             $data->cast(Table\Data::TYPE_NUMERIC);
    //             $data->getType(); // now returns "numeric"
    //             $data->getValue(); // returns an integer or float
    //         }
    //     }
    //     $col = $table->getColumn('firstname'); // returns an iterable containing each column's data CSVelte\Table\Column
    //
    //
    //     // Pretend this is inside CSVelte\Writer in like a initBuffer() method or something
    //
    // }
}
