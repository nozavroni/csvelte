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

    public function __construct()
    {
        $this->flavor = new Flavor;
    }

    public function getFlavor()
    {
        return $this->flavor;
    }
}
