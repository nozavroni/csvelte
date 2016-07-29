<?php

use PHPUnit\Framework\TestCase;
use CSVelte\Table\Row;
use CSVelte\Table\HeaderRow;
use CSVelte\Table\Cell;
use CSVelte\Table\Data\StringValue;
use CSVelte\Table\Data\BooleanValue;
use CSVelte\Table\Data\DateTimeValue;
use CSVelte\Table\Data\NumberValue;
use CSVelte\Table\Data\IntegerValue;

/**
 * CSVelte\Table\Cell Tests
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class TableCellTest extends TestCase
{
    protected $CSVstrings = array(
        'Basic' => "ID,Title,Name,Stock Number,Difference,Standard Number,Price,Weird Name,Another Title,Decimal\n1,Eldon Base for stackable storage shelf platinum,Muhammed MacIntyre,3,-213.25,38.94,35,Nunavut,Storage & Organization,0.8\n2,1.7 Cubic Foot Compact Office Refrigerators,Barry French,293,457.81,208.16,68.02,Nunavut,Appliances,0.58\n3,Cardinal Slant-DÆ Ring Binder Heavy Gauge Vinyl,Barry French,293,46.71,8.69,2.99,Nunavut,Binders and Binder Accessories,0.39\n4,R380,Clay Rozendal,483,1198.97,195.99,3.99,Nunavut,Telephones and Communication,0.58\n5,Holmes HEPA Air Purifier,Carlos Soltero,515,30.94,21.78,5.94,Nunavut,Appliances,0.5\n6,G.E. Longer-Life Indoor Recessed Floodlight Bulbs,Carlos Soltero,515,4.43,6.64,4.95,Nunavut,Office Furnishings,0.37\n7,Angle-D Binders with Locking Rings Label Holders,Carl Jackson,613,-54.04,7.3,7.72,Nunavut,Binders and Binder Accessories,0.38\n8,SAFCO Mobile Desk Side File Wire Frame,Carl Jackson,613,127.70,42.76,6.22,Nunavut,Storage & Organization,\n9,SAFCO Commercial Wire Shelving Black,Monica Federle,643,-695.26,138.14,35,Nunavut,Storage & Organization,\n10,Xerox 198,Dorothy Badders,678,-226.36,4.98,8.33,Nunavut,Paper,0.38",
    );

    public function testTableCellInstatiate()
    {
        $cell = new Cell('$25.00');
        $this->assertEquals('$25.00', (string) $cell);
    }

    // public function testBuildRowWithCells()
    // {
    //     $l = 0;
    //     $rows = array();
    //     $header = array();
    //     $lines = explode("\n", $this->CSVstrings['Basic']);
    //     foreach ($lines as $line_no => $line) {
    //         $c = 0;
    //         $items = explode(',', $line);
    //         $rarr = array();
    //         foreach ($items as $col_no => $item) {
    //             $rarr []= new Cell($item);
    //             $c++;
    //         }
    //         $row = new Row($rarr);
    //         if (!$l) {
    //             // header row
    //             $header = $row;
    //         } else {
    //             $row->setHeaderRow($header);
    //         }
    //         $rows []= $row;
    //         $l++;
    //     }
    //     dd($rows);
    // }

    // public function testInstantiateNewTableCellWithData()
    // {
    //     $table = new Table([
    //         'columns' => [
    //             'id' => [
    //                 'type' => 'integer',
    //                 'position' => 1,
    //                 'default' => 0,
    //                 'required' => true,
    //                 'foreignKey' => true,
    //                 'autoField' => [
    //                     'initial' => 0,
    //                     'formula' => function($a) { return ++$a; }
    //                 ],
    //             ],
    //             'firstname' => ['type' => 'string', 'label' => 'First Name', 'required' => true, 'maxLen' => 100],
    //             'lastname' => ['type' => 'string', 'label' => 'Last Name', 'required' => true, 'maxLen' => 100],
    //             'email' => ['position' => 2, 'type' => 'string', 'format' => 'email', 'required' => true, 'unique' => true]
    //         ]
    //     ]);
    //     foreach($lines as $line_no => $line) {
    //         $elems = $this->parseLine($line);
    //         // lets say elems looks like... ['23', '"Luke"', '"Visinoni"', '"luke.visinoni@gmail.com"']
    //         $row = $table->newRow();
    //         foreach ($items as $col_no => $value) {
    //             $cell = $row->newCell($value);
    //             $row->append($cell);
    //         }
    //     }
    // }

}
