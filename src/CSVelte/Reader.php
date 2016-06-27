<?php namespace CSVelte;

use CSVelte\Input\InputInterface;

/**
 * CSVelte
 * A PHP CSV utility library (formerly PHP CSV Utilities).
 *
 * @package   CSVelte
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class Reader /*implements \Countable*/
{
    protected $source;
    /**
     * Class constructor
     * @todo Replace CSVelte\File hint with CSVelte\InputInterface so that reader
     *       can accept streams and any other type of input object you can cook up
     */
    public function __construct(InputInterface $input)
    {
        $this->source = $input;
    }

}
