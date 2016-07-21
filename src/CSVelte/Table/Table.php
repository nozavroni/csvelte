<?php namespace CSVelte\Table;
use CSVelte\Traits\HandlesQuotedLineTerminators;
/**
 * CSV data is just a big table. This is a table class.Fill it with rows and cols.
 * You fill it with rows of data...
 *
 * ...and then you do stuff with it.
 *
 * @package    CSVelte
 * @subpackage CSVelte\Table
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 * @todo I got really excited when the idea of creating a Table class to put rows
 *     into and to represent the CSV dataset as a whole, but as I'm actually
 *     putting this thing together, it's occurring to me that by building a table
 *     in this way, I'm basically forcing the library into loading an entire CSV
 *     file or dataset into memory before being able to do any real work. This is
 *     something I've worked very hard, until now, to avoid. The reasons should
 *     be obvious but in case they aren't... CSV files have the potential to be
 *     absolutely enormous. Hundreds of megabytes or even gigabytes or worse! I
 *     like the idea of having a Table object to contain my rows enough, though,
 *     that I'm committed to finding a way to use one without actually having to
 *     load up an entire CSV file's-worth of data in order to make use of it.
 *
 * @todo OK, I believe the only solution, if I want to keep the concept of a
 *    Table object, without having to load an entire CSV file or dataset into
 *    memory, is to have a class called BufferredTable or maybe just BufferTable
 *    or TableBuffer. Yeah I think I like CSVelte\Table\TableBuffer. But anyway,
 *    it would work by allowing you to fill it with a configurable amount of data
 *    either limiting buffer size by amount of lines, or amount of bytes.
 *    Probably lines, just to keep me sane. This way, I would still have a table
 *    object with maybe like 50-100 rows (maybe more, I'd have to play around to
 *    find out how many rows it could handle before causing any problems with )
 *    memory/performance on a lowest common denominator computer/server). This
 *    would allow me to benefit from a virtual data table.
 *
 *    # Some ways I could make use of a buffered virtual data table:
 *
 *        - I could probably get rid of the concept of the HandlesQuotedLineTerminators
 *              trait. Instead, I would read a certain number of lines into the
 *              table buffer, without concern for quotes or newlines or anything
 *              else, and then read from THAT buffered table object as you would
 *              have read from the file/stream, only now taking quotes and newlines
 *              and all that into account.
 */
class Table extends AbstractTable
{

}
