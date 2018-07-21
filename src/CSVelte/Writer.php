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

use CSVelte\Contract\Streamable;
use Noz\Collection\Collection;

use function Noz\collect;
use function Stringy\create as s;
use Traversable;

class Writer
{
    /** @var Streamable The output stream to write to */
    protected $output;

    /** @var Dialect The *dialect* of CSV to write */
    protected $dialect;

    /** @var Collection The header row */
    protected $header;

    /**
     * Writer constructor.
     *
     * Although this is the constructor, I don't envision it being used much in userland. I think much more common
     * methods of creating writers will be available within CSVelte base class such as CSVelte::toSplFileObject,
     * CSVelte::toPath(), CSVelte::toOutputBuffer(), etc.
     *
     * @param Streamable $output The destination streamable being written to
     * @param Dialect $dialect The dialect being written
     */
    public function __construct(Streamable $output, Dialect $dialect = null)
    {
        if (is_null($dialect)) {
            $dialect = new Dialect;
        }
        $this->setOutputStream($output)
            ->setDialect($dialect);
    }

    /**
     * Set the CSV dialect
     *
     * @param Dialect $dialect The *dialect* of CSV to use
     *
     * @return self
     */
    public function setDialect(Dialect $dialect)
    {
        $this->dialect = $dialect;
        return $this;
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
     * Set output stream
     *
     * @param Streamable $stream The output stream to write to
     *
     * @return self
     */
    protected function setOutputStream(Streamable $stream)
    {
        $this->output = $stream;
        return $this;
    }

    /**
     * Insert a single record into CSV output
     *
     * Returns total bytes written to the output stream.
     *
     * @param array|Traversable $data A row of data to write to the CSV output
     *
     * @return false|int
     */
    public function insertRow($data)
    {
        $d = $this->getDialect();
        $data = collect($data)
            ->map(function($field) use ($d) {
                if ($qstyle = $d->getQuoteStyle()) {
                    $wrap = false;
                    switch ($qstyle) {
                        case Dialect::QUOTE_ALL:
                            $wrap = true;
                            break;
                        case Dialect::QUOTE_MINIMAL:
                            if (s($field)->containsAny([$d->getQuoteChar(), $d->getDelimiter(), $d->getLineTerminator()])) {
                                $wrap = true;
                            }
                            break;
                        case Dialect::QUOTE_NONNUMERIC:
                            if (is_numeric((string) $field)) {
                                $wrap = true;
                            }
                            break;
                    }
                    if ($wrap) {
                        $field = s($field);
                        if ($field->contains($d->getQuoteChar())) {
                            $escapeChar = $d->isDoubleQuote() ? $d->getQuoteChar() : '\\' /*$d->getEscapeChar()*/;
                            $field = $field->replace($d->getQuoteChar(), $d->getQuoteChar() . $d->getQuoteChar());
                        }
                        $field = $field->surround($d->getQuoteChar());
                    }
                }
                return (string) $field;
            });
        $str = s($data->join($d->getDelimiter()))
            ->append($d->getLineTerminator());

        return $this->output->write((string) $str);
    }

    /**
     * Write multiple rows to CSV output
     *
     * Returns total bytes written to the output stream.
     *
     * @param array|Traversable $data An array of rows of data to write to the CSV output
     *
     * @return int
     */
    public function insertAll($data)
    {
        return collect($data)
            ->map(function($row, $lineNo, $i) {
                return $this->insertRow($row);
            })
            ->sum();
    }
}