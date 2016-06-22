<?php namespace CSVelte;

use CSVelte\Input\InputInterface;

/**
 * CSVelte\Taster
 * Given CSV data, Taster will "taste" the data and provide its buest guess at
 * its "flavor". In other words, this class inspects CSV data and attempts to
 * auto-detect various CSV attributes such as line endings, quote characters, etc..
 *
 * @package   CSVelte
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class Taster
{
    /**
     * @var CSVelte\InputInterface
     */
    protected $input;

    /**
     * Class constructor
     * accepts a CSV input source
     */
    public function __construct(InputInterface $input)
    {
        $this->input = $input;
    }

    public function taste()
    {
        return new Flavor;
    }

    public function guessLineTerminator()
    {
        return "\n";
    }
}
