<?php namespace CSVelte;

use CSVelte\Contract\Writable;

/**
 * CSVelte Writer Base Class
 * A PHP CSV utility library (formerly PHP CSV Utilities).
 *
 * @package   CSVelte
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class Writer
{
    /**
     * @var CSVelte\Flavor
     */
    protected $flavor;

    /**
     * @var CSVelte\Contracts\Writable
     */
    protected $output;

    public function __construct(Writable $output, Flavor $flavor = null)
    {
        if (is_null($flavor)) $flavor = new Flavor;
        $this->flavor = $flavor;
        $this->output = $output;
    }

    public function getFlavor()
    {
        return $this->flavor;
    }

    public function writeRow($row)
    {
        if (is_array($row)) $row = new \ArrayIterator($row);
        $row = $this->prepareRow($row);
        return $this->output->write($row);
    }

    public function writeRows($rows)
    {
        if (is_array($rows)) $rows = new \ArrayIterator($rows);
        if (!($rows instanceof Iterator)) {
            throw new \InvalidArgumentException('First argument for ' . __CLASS__ . '::' . __METHOD__ . ' must be iterable');
        }
    }

    protected function prepareRow(\Iterator $row)
    {
        $return = array();
        foreach ($row as $col) {
            $return []= $col;
        }
        return implode($this->flavor->delimiter, $return) . $this->flavor->lineTerminator;
    }
}
