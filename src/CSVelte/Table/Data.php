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
 * @todo After reading the CSVW spec a little more I will probably name this
 *     class CSVelte\Table\Cell
 * @todo Consider renaming this class CSVelte\Table\Value or CSVelte\Value and
 *     then for each of the data types, you would have a CSVelte\Value\Text or
 *     CSVelte\Value\Numeric. The concept of a data item containing a type would
 *     go away and instead a data item would just BE a type. So rather than:
 *
 *         $text = new CSVelte\Table\DataType\Text('this is some text');
 *         $data = new CSVelte\Table\Data($text);
 *
 *     You would do:
 *
 *         $textdata = new CSVelte\Value\Text('this is some text');
 *
 *     Although I suppose, now that I think about it, it makes sense that there
 *     would be a Table data "cell" or "item" and it would contain a "value" obj
 *
 *     Another possibility is data types not even containing values but instead
 *     simply describing a data type. So you would do something like:
 *
 *         $text = CSVelte\DataType\Text::create('this is some text');
 *
 *     Which would produce a CSVelte\Value object that had text properties...
 *
 */
class Data
{
    /**
     * @var CSVelte\Table\DataType
     */
    protected $value;

    /**
     * @var string the original syntactic representation of the value of the cell
     */
    protected $strValue;

    /**
     * Class constructor
     *
     * @param mixed Can be either a native PHP value or a DataType object
     * @return void
     * @access public
     */
    public function __construct($val)
    {
        $this->strValue = (string) $val;
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
        // update: see JSON table schema spec for data types. They specify data
        // types that then also have "formats" for the different possible "sub-
        // types" I mentioned above.
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
