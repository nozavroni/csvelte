<?php

use PHPUnit\Framework\TestCase;

/**
 * CSVelte\Table\Cell Tests
 *
 * @package   CSVelte Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class TableCellTest extends TestCase
{
    public function testTableCellInstatiate()
    {
        $this->assertTrue(true);
    }
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
