<?php namespace CSVelte\Table;

use CSVelte\Utils;
use CSVelte\Table\Schema\ColumnSchema;

/**
 * Table Schema
 *
 * @package    CSVelte
 * @subpackage CSVelte\Table
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 * @todo Maybe rename this to CSVelte\Table\Schema\TableSchema?
 */
class Schema
{
    protected $columns;

    /**
     * Table Schema Class Constructor
     *
     * @param array
     * @return void
     * @todo add primaryKey and foreignKeys parameters
     */
    public function __construct(array $columns)
    {
        $this->setColumns($columns);
    }

    /**
     * Set Column Schemae
     *
     * @param array of array|ColumnSchema
     * @return void ($this?)
     */
    protected function setColumns($columns)
    {
        foreach ($columns as $key => $col) {
            if ($col instanceof ColumnSchema) {
                $this->columns[$col->getId()] = $col;
            } else {
                $this->columns[$key] = new ColumnSchema($key, $col);
            }
        }
    }

    /**
     * Set Column Schemae
     *
     * @param string An identifier to retrieve the column by
     * @return CSVelte\Table\Schema\ColumnSchema
     */
    public function getColumnSchema($id)
    {
        return Utils::array_get($this->columns, $id, null, true);
    }
}
