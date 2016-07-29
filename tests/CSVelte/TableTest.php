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
 * @todo I might go ahead and leave Table for the next version... I'm going to
 *     try to nail down the essential CSV read/write functionality for now.
 */
class TableTest extends TestCase
{
    protected $CSVstrings = array(
        'Basic' => "ID,Title,Name,Stock Number,Difference,Standard Number,Price,Weird Name,Another Title,Decimal\n1,\"Eldon Base for stackable storage shelf platinum\",Muhammed MacIntyre,3,-213.25,38.94,35,Nunavut,Storage & Organization,0.8\n2,\"1.7 Cubic Foot Compact Office Refrigerators\",Barry French,293,457.81,208.16,68.02,Nunavut,Appliances,0.58\n3,Cardinal Slant-DÆ Ring Binder Heavy Gauge Vinyl,Barry French,293,46.71,8.69,2.99,Nunavut,\"Binders and Binder Accessories\",0.39\n4,R380,Clay Rozendal,483,1198.97,195.99,3.99,Nunavut,Telephones and Communication,0.58\n5,Holmes HEPA Air Purifier,Carlos Soltero,515,30.94,21.78,5.94,Nunavut,Appliances,0.5\n6,G.E. Longer-Life Indoor Recessed Floodlight Bulbs,Carlos Soltero,515,4.43,6.64,4.95,Nunavut,Office Furnishings,0.37\n7,Angle-D Binders with Locking Rings Label Holders,Carl Jackson,613,-54.04,7.3,7.72,Nunavut,Binders and Binder Accessories,0.38\n8,SAFCO Mobile Desk Side File Wire Frame,Carl Jackson,613,127.70,42.76,6.22,Nunavut,Storage & Organization,\n9,SAFCO Commercial Wire Shelving Black,Monica Federle,643,-695.26,138.14,35,Nunavut,Storage & Organization,\n10,Xerox 198,Dorothy Badders,678,-226.36,4.98,8.33,Nunavut,Paper,0.38",
    );

    public function testCreateTable()
    {
        $table = new Table();
        $this->assertInstanceOf(Table::class, $table);
    }

    public function testCreateTableFromScratch()
    {
        $table = new Table();
        $lines = explode("\n", $this->CSVstrings['Basic']);
        $i = 0;
        foreach ($lines as $line_no => $line) {
            $items = explode(",", $line);
            if (!$i) {
                // this is the header
                
            }
            $i++;
        }

    }

    // public function testBuildTableFromScratch()
    // {
    //     // and then hopefully we can find a good enough use for it to justify
    //     // all the grief it's causing me.
    //     $table = new Table();
    //     $table->attach();
    // }

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
