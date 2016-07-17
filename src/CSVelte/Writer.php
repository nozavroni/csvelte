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

    public function writeRow(Iterator $row)
    {
        $row = implode($this->flavor->delimiter, $row);
        $row .= $this->flavor->lineTerminator;
        return $this->output->write($row);
    }

    protected function prepareRow($row) {

    }
}
