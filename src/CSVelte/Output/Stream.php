<?php namespace CSVelte\Output;

use CSVelte\Contract\Writable;
use CSVelte\Traits\StreamIO;

/**
 * CSVelte Stream Writer
 *
 * @package   CSVelte
 * @subpackage CSVelte\Writable
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class Stream implements Writable
{
    use StreamIO;

    /**
     * Get the "mode" used to open stream resource handle
     *
     * @return string
     * @see fopen function
     * @todo I'm definitely not in love with this design but I'll refactor later
     */
    protected function getMode()
    {
        return 'w';
    }

    public function write($data)
    {
        return fwrite($this->source, $data);
    }
}
