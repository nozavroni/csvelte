<?php namespace CSVelte;

use CSVelte\Exception\FileNotFoundException;

/**
 * CSVelte
 * A PHP CSV utility library (formerly PHP CSV Utilities).
 *
 * @package   CSVelte
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class CSVelte
{
    /**
     * Class constructor
     */
    public function __construct()
    {

    }

    /**
     * Import CSV data from external source (file)
     *
     * @var string The name of the file you wish to import
     * @return boolean True if import was successful
     * @todo Should this maybe return the number of lines imported on success?
     * @todo Move all the file assertion code into CSVelte\File
     */
    public function import($file)
    {
        $this->assertFileIsReadable($file);
    }

    /**
     * Headers accessor
     * Gets or sets the CSV header values (as an array)
     *
     * @return array|boolean Returns array of header values or a boolean value if setting
     * @todo I'm not sure I like the ambiguous accessor interface... a method
     *       should only do one thing and it shouldn't depend on context
     */
    public function headers()
    {
        return [];
    }

    /**
     * Assert that a particular file exists and is readable (user has permission
     * to read/access it)
     *
     * @access protected
     * @var string The name of the file you wish to check
     * @return void
     */
    protected function assertFileIsReadable($filename)
    {
        $this->assertFileExists($filename);
        if (!is_readable($filename)) {
            throw new PermissionDeniedException('Permission denied for: ' . $filename);
        }
    }

    /**
     * Assert that a particular file exists
     *
     * @access protected
     * @var string The name of the file you wish to check
     * @return void
     */
    protected function assertFileExists($filename)
    {
        if (!file_exists($filename)) {
            throw new FileNotFoundException('File does not exist: ' . $filename);
        }
    }
}
