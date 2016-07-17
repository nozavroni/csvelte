<?php namespace CSVelte;
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

    public function __construct(Writable $output)
    {
        $this->flavor = new Flavor;
        $this->output = $output;
    }

    public function getFlavor()
    {
        return $this->flavor;
    }

    public function writeRow($row)
    {
        $row = implode($this->flavor->delimiter, $row);
        $row .= $this->flavor->lineTerminator;
        return $this->output->write($row);
    }
}
