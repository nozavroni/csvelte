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
use CSVelte\Contract\Readable;
use CSVelte\Contract\Writable;
use CSVelte\Contract\Seekable;
use CSVelte\Exception\FileNotFoundException;

/**
 * CSVelte File.
 *
 * Represents a file for reading/writing. Implements both readable and writable
 * interfaces so that it can be passed to either ``CSVelte\Reader`` or
 * ``CSVelte\Writer``.
 *
 * @package    CSVelte
 * @subpackage CSVelte\IO
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 * @since      v0.2
 */
class File extends SplFileObject implements Readable, Writable, Seekable
{
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
     *      open_mode: Same as mode for fopen
     *      use_include_path: Search include path for $filename
     *      context: See stream_context_create()
     *               http://php.net/manual/en/function.stream-context-create.php
     */
    protected $options = [
        'create' => true,
        'parents' => false,
        'mode' => 0644,
        'open_mode' => 'rb+',
        'use_include_path' => false,
        'context' => null
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
        if ($this->options['use_include_path']) {
            $filename = stream_resolve_include_path($filename);
        }
        if (!file_exists($filename)) {
            if ($this->options['create']) {
                if (!is_dir($dirname = dirname($filename))) {
                    if ($this->options['parents']) {
                        mkdir($dirname, $this->options['mode'], true);
                    } else {
                        throw new FileNotFoundException("Directory not found: ". $dirname, FileNotFoundException::ERR_DIR_NOT_FOUND);
                    }
                }
                touch($filename);
                chmod($filename, $this->options['mode']);
            } else {
                throw new FileNotFoundException("File not found: ". $filename, FileNotFoundException::ERR_FILE_NOT_FOUND);
            }
        }
        parent::__construct(
            $filename,
            $this->options['open_mode'],
            $this->options['use_include_path'], // this isnt necessary
            $this->options['context']
        );
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
     * @todo I'm not sure if this should be stripping line endings or not. Maybe
     *       I should have a separate method that gets a line w/out line ending?
     * @todo Decided to just kill this for now... if I need it Ill bring it back
     */
    public function fgets()
    {
        return rtrim(parent::fgets(), "\r\n");
    }

}
