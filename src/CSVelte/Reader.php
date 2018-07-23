<?php
/**
 * CSVelte: Slender, elegant CSV for PHP
 *
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @copyright Copyright (c) 2018 Luke Visinoni
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   See LICENSE file (MIT license)
 */
namespace CSVelte;

use Iterator;
use Countable;
use CSVelte\Contract\Streamable;
use Noz\Collection\Collection;
use Stringy\Stringy;

use function Noz\collect;
use function Stringy\create as s;

class Reader implements Iterator, Countable
{
    /** @var Streamable The input stream to read from */
    protected $input;

    /** @var Dialect The *dialect* of CSV to read */
    protected $dialect;

    /** @var Collection The line currently sitting in memory */
    protected $current;

    /** @var Collection The header row */
    protected $header;

    /** @var int The current line number */
    protected $lineNo;

    /**
     * Reader constructor.
     *
     * Although this is the constructor, I don't envision it being used much in userland. I think much more common
     * methods of creating readers will be available within CSVelte base class such as CSVelte::fromPath(),
     * CSVelte::fromString(), CSVelte::fromSplFileObject, CSVelte::toSplFileObject, CSVelte::toPath(), etc.
     *
     * @param Streamable $input The source being read from
     * @param Dialect $dialect The dialect being read
     */
    public function __construct(Streamable $input, Dialect $dialect = null)
    {
        if (is_null($dialect)) {
            $dialect = new Dialect;
        }
        $this->setInputStream($input)
            ->setDialect($dialect);
    }

    /**
     * Get csv data as a two-dimensional array
     *
     * @return array
     */
    public function toArray()
    {
        return iterator_to_array($this);
    }

    /**
     * Set the CSV dialect
     *
     * @param Dialect $dialect The *dialect* of CSV to read
     *
     * @return self
     */
    public function setDialect(Dialect $dialect)
    {
        $this->dialect = $dialect;
        // call rewind because new dialect needs to be used to re-read
        return $this->rewind();
    }

    /**
     * Get dialect
     *
     * @return Dialect
     */
    public function getDialect()
    {
        return $this->dialect;
    }

    /**
     * Fetch a single row
     *
     * Fetch the next row from the CSV data. If no more data available, returns false.
     *
     * @return array|false
     */
    public function fetchRow()
    {
        if (!$this->valid()) {
            return false;
        }
        $line = $this->current();
        $this->next();
        return $line;
    }

    /**
     * Set input stream
     *
     * @param Streamable $stream The input stream to read from
     *
     * @return self
     */
    protected function setInputStream(Streamable $stream)
    {
        $this->input = $stream;
        return $this;
    }

    /**
     * Loads next line into memory
     *
     * Reads from input one character at a time until a newline is reached that isn't within quotes. Once a completed
     * line has been loaded, it is assigned to the `$this->current` property. Subsequent calls will continue to load
     * successive lines until the end of the input source is reached.
     *
     * @return self
     */
    protected function loadLine()
    {
        $d = $this->getDialect();
        $line = '';
        while ($str = $this->input->readLine($d->getLineTerminator())) {
            $line .= $str;
            if (count(s($line)->split($d->getQuoteChar())) % 2) {
                break;
            }
        }
        $this->current = $this->parseLine($line);
        return $this;
    }

    /**
     * Parse a line of CSV into individual fields
     *
     * Accepts a line (string) of CSV data that it then splits at the delimiter character. The method is smart, in that
     * it knows not to split at delimiters within quotes. Ultimately, fields are placed into a collection and returned.
     *
     * @param string $line A single line of CSV data to parse into individual fields
     *
     * @return Collection
     */
    protected function parseLine($line)
    {
        $d = $this->getDialect();
        $fields = collect(s($line)
            ->trimRight($d->getLineTerminator())
            ->split(" *{$d->getDelimiter()} *(?=([^\"]*\"[^\"]*\")*[^\"]*$)"));
        if (!is_null($this->header)) {
            // @todo there may be cases where this gives a false positive...
            if (count($fields) == count($this->header)) {
                $fields = $fields->rekey($this->header);
            }
        }
        return $fields->map(function(Stringy $field, $pos) use ($d) {
            if ($d->isDoubleQuote()) {
                $field = $field->replace('""', '"');
            }
            return (string) $field->trim($d->getQuoteChar());
        });
    }

    /** == BEGIN: SPL implementation methods == */

    /**
     * Get current row
     *
     * @return array
     */
    public function current()
    {
        return $this->current->toArray();
    }

    /**
     * Move pointer to beginning of the next line internally and then load the line
     *
     * @return self
     */
    public function next()
    {
        $this->loadLine();
        $this->lineNo++;
        return $this;
    }

    /**
     * Get current line number
     *
     * @return int
     */
    public function key()
    {
        return $this->lineNo;
    }

    /**
     * Have we reached the end of the CSV data?
     *
     * @return bool
     */
    public function valid()
    {
        return !$this->input->eof();
    }

    /**
     * Rewind to the beginning
     *
     * Rewinds the internal pointer to the beginning of the CSV data, load first line, and reset line number to 1. Also
     * loads the header (if one exists) and uses its values as indexes within rows.
     *
     * @return self
     */
    public function rewind()
    {
        $this->lineNo = 1;
        $this->input->rewind();
        if ($this->getDialect()->hasHeader()) {
            $this->loadLine();
            $this->header = $this->current();
        }
        $this->loadLine();
        return $this;
    }

    /**
     * Get number of lines in the CSV data (not including header)
     *
     * @return int
     */
    public function count()
    {
        return count($this->toArray());
    }

    /** == END: SPL implementation methods == */
}