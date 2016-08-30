<?php
/**
 * CSVelte: Slender, elegant CSV for PHP.
 *
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @version   v0.2
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace CSVelte\IO;

use \SplFileObject;
// use \SplFileInfo;

use CSVelte\Contract\Readable;
use CSVelte\Contract\Writable;

// Considering adding the following traits as well as adopting such a naming
// convention (traits will start with verb such as Is/Does/Will/Etc.)
use CSVelte\IO\IsReadable;
use CSVelte\IO\IsWritable;
use CSVelte\Exception\FileNotFoundException;

/**
 * CSVelte File.
 *
 * Represents a file for reading/writing. Implements both readable and writable
 * interfaces so that it can be passed to either ``CSVelte\Reader`` or
 * ``CSVelte\Writer``.
 *
 * @package    CSVelte
 * @subpackage CSVelte\Contract
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 * @since      v0.2
 */
class File extends SplFileObject implements Readable, Writable
{
    use IsReadable, IsWritable;

    /**
     * @var constant Used as code for exception thrown for missing file
     */
    const ERR_FILENOTFOUND = 1;

    /**
     * @var constant Used as code for exception thrown for missing directory
     */
    const ERR_DIRNOTFOUND = 2;

    /**
     * Initialization options for this file
     * @var array These options are set when instantiating this file object.
     *            These values are just defaults.
     *      create:  If set to true, CSVelte will attempt to create the file if
     *               it doesn't exist.
     *      parents: Set to true to enable creation of any parent
     *               directories of the file.
     *      mode:    Set the mode for this file and parent directories
     *               (if any) that were created.
     */
    protected $options = [
        'create' => true,
        'parents' => false,
        'mode' => 0644
    ];

    /**
     * File Object Constructor.
     *
     * @param string $filename The path and name of the file
     * @param array $options An array of any/none of the following options
     *                          (see $options var above for more details)
     */
    public function __construct($filename, array $options = [])
    {
        $this->options = array_merge($this->options, $options);
        if (!file_exists($filename)) {
            if ($this->options['create']) {
                if (!is_dir($dirname = dirname($filename))) {
                    if ($this->options['parents']) {
                        mkdir($dirname, $this->options['mode'], true);
                    } else {
                        throw new FileNotFoundException("Directory not found: ". $dirname, self::ERR_DIRNOTFOUND);
                    }
                }
                touch($filename);
                chmod($filename, $this->options['mode']);
            } else {
                throw new FileNotFoundException("File not found: ". $filename, self::ERR_FILENOTFOUND);
            }
        }
        parent::__construct($filename);
    }

    /**
     * Read from file.
     * Read $length number of characters from file
     *
     * @param int $length Number of characters to read from the file
     * @return string Up to $length characters read from the file
     */
    public function read($length)
    {
        return $this->fread($length);
    }

    /**
     * Read single line.
     * Read the next line from the file (moving the internal pointer down a line).
     * Returns multiple lines if newline character(s) fall within a quoted string.
     *
     * @return string A single line read from the file.
     * @todo I'm going to leave this be for now, but if issues pop up with line
     *       endings, look into using ``stream_get_line`` rather than fgets. It
     *       allows you to specify the line terminator.
     */
    public function readLine()
    {
        return rtrim($this->fgets(), "\r\n");
    }

    /**
     * Is end of file?
     *
     * If the end of the file has been reached, this should return true.
     *
     * @return boolean True if end of file has been reached
     */
    public function isEof()
    {
        return $this->eof();
    }

    /**
     * Write to file.
     * Write $data to the file.
     *
     * @param mixed Anything that can be cast to a string can be written
     * @return int The number of bytes written to the file
     */
    public function write($data)
    {

    }

}
