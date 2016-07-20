<?php namespace CSVelte\Table;

use CSVelte\Contract\DataType;

/**
 * Table Data Item Class
 * Represents data within a particular row/column within a set of tabular data
 *
 * @package    CSVelte
 * @subpackage CSVelte\Table
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 */
class Data
{
    /**
     * @var CSVelte\Table\DataType
     */
    protected $value;

    /**
     * Class constructor
     *
     * @param mixed Can be either a native PHP value or a DataType object
     * @return void
     * @access public
     */
    public function __construct($val)
    {
        $this->value = $this->assumeDataType($val);
    }

    protected function assumeDataType($val)
    {
        if ($val instanceof DataType) return $val;
        // value is not a DataType object, so try to cast it to one...
        // @todo one thing to think about though is that several of these have at least
        // one sub-type (DateTime has Date, Time, Timezone; Numeric potentially
        // has float, int, signed/unsigned int, etc.; and Text has literally an
        // endless number of possible sub-types)
        $types = array('DateTime', 'Duration', 'Numeric', 'Boolean', 'Text');
        switch (true) {
            case DateTime::validate($val):
                return DateTime::create($val);
            case Numeric::validate($val):
                return Numeric::create($val);
            case Boolean::validate($val):
                return Boolean::create($val);
            case Duration::validate($val):
                return Duration::create($val);
        }
    }
}
