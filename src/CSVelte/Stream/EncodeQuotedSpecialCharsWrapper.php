<?php namespace CSVelte\Stream;

use CSVelte\Exception\InvalidStreamUriException;
use CSVelte\Exception\EndOfFileException;
use CSVelte\Exception\FileNotFoundException;

/**
 * Abstract Stream Wrapper
 * This may actually be unnecessary... I don't know if I'm actually going to need
 * more than the one stream wrapper. I may not need even that one. This is an
 * experiment by all means. I may end up implementing EncodeQuotedSpecialChars
 * as a trait and then simply add that trait to the Wrapper class rather than
 * making this its own class.
 * @note look into  ini_set('memory_limit', '16M') for memory configuration
 */
class EncodeQuotedSpecialCharsWrapper extends Wrapper
{
    /**
     * @constant int The default read buffer size
     */
    const DEFAULT_RBLEN = 4096;

    const ENCODE_FORMAT = "<[=%%%03d=]>";

    /**
     * @var resource The underlying stream handle resource
     */
    protected $stream;

    /**
     * @var int The cursor position within read buffer
     */
    protected $rbPos;

    /**
     * @var array position => replacement k/v pairs for current read buffer
     */
    protected $rbRepl;

    /**
     * @var int The expected read buffer size
     * @todo PHP buffers all reads to 4096 behind the scenes on its own. So no
     *     matter what, it's buffering at least 4096. It doesn't really make any
     *     sense to have a buffer smaller than that. Unless, I guess, you use
     *     stream_set_read_buffer to change PHP's internal read buffer size. Take
     *     a look at that function just to see what the docs say.
     * @note I read somewhere that fread will not read more than 8192 bytes at a
     *     time so a buffer any larger than that doesn't make sense.
     */
    protected $rbLen = self::DEFAULT_RBLEN;

    /**
     * @var int The current (actual) read buffer size, not counting length of
     *     escape/encode sequences
     */
    protected $rbCurLen;

    /**
     * @var string The read buffer content
     */
    protected $rb;

    /**
     * @var resource The underlying stream handle resource context
     */
    public $context;

    /**
     * @var boolean If true, this class is responsible for reporting errors
     */
    protected $errReporting;

    /**
     * @var string Path to file being streamed
     */
    protected $path;

    /**
     * @var string A list of "special" characters to be "encoded/escaped"
     * @todo I may end up only using this for newline characters since the other
     *     characters can easily be handled via preg_split somewhere else. But
     *     if this method is significantly faster or better on memory or in any
     *     other way is beneficial, I'll keep it how it is.
     * @todo Needs to be configurable. The only characters that actually need to
     *     be encoded are the newline character(s) that are being used as the
     *     lineTerminator in the CSV file and the delimiter. Possibly the quote
     *     character as well, but I think that will be handled another way.
     */
    protected $special = "\r\n\t,;|:_/";
    protected $specialChars = array();

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct ()
    {
        $this->setSpecialChars($this->special);
    }

    protected function setSpecialChars($chars)
    {
        foreach (str_split($chars) as $c) {
            $this->specialChars[ord($c)] = $c;
        }
    }

    // __destruct ( void )

    // public bool rename ( string $path_from , string $path_to )

    // public function stream_cast($cast_as)
    // {
    //     return $this->stream;
    // }

    /**
     * @param string path
     * @param string mode
     * @param int options flag
     * @param string opened path
     * @return boolean
     */
    public function stream_open($path, $mode, $options, &$opened_path)
    {
        // @todo Should this turn off exception throwing as well?
        // I think what I'll probably end up doing is create a method called
        // raiseError($error) where $error is an exception or error to be thrown
        // if errReporting is set to true. Or possibly I'll wrap everything in
        // each method in a try/catch that will trigger the appropriate error
        // depending on what exception was thrown... think about it
        $this->errReporting = $options & STREAM_REPORT_ERRORS;
        if (!$url = $this->parse_url($path)) {
            throw new InvalidStreamUriException('Invalid stream URI: ' . $path);
        }
        // @todo is this necessary? I thought I needed to do this but since opened_path
        // is a required parameter, I'm kind of wondering if I just get a full
        // path to a filename if STREAM_USE_PATH is set...
        $fullpath = null;
        if ($options & STREAM_USE_PATH) {
            if ($fullpath = $this->searchIncludePath($url['path'])) {
                $opened_path = $fullpath;
            }
        }
        if (false === ($this->stream = fopen($this->path = $fullpath ?: $url['path'], $mode))) {
            // @todo add an assertValidStream() method that checks for existence,
            // readability/writability, valid stream uri, etc. and throws the
            // appropriate exception if any of these fail...
            // @todo File not found isn't always the reason fopen fails so this
            // is misleading (potentially)
            throw new FileNotFoundException('Could not open stream: ' . $this->path);
        }
        $this->position = 0;
        $this->rbPos = 0;
        return true;
    }

    protected function parse_url($url)
    {
        $url = str_replace('csvelte://', 'file://', $url);
        return parse_url($url);
    }

    /**
     * Search the include path for a particular file
     *
     * @param string The filename/path to search for
     * @return string|false The full path to found file or false if none was found
     * @access protected
     */
    protected function searchIncludePath($file)
    {
        // if filename starts with a slash, it's an absolute path, return false
        if (strpos($file, DIRECTORY_SEPARATOR) !== 0) {
            $paths = explode(PATH_SEPARATOR, get_include_path());
            foreach ($paths as $path) {
                $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
                if (file_exists($realpath = realpath($path . $file))) {
                    return $realpath;
                }
            }
        }
        return false;
    }

    /**
     * @return void
     */
    public function stream_close()
    {
        fclose($this->stream);
    }

    /**
     * @return boolean
     */
    public function stream_eof()
    {
        return feof($this->stream);
    }

    /**
     * @return boolean
     * @todo Under what circumstances is this necessary? When does PHP have data
     *     that needs to be flushed? I'm assuming this is something to do with a
     *     write buffer but I don't know how it all works...
     */
    public function stream_flush()
    {
        return fflush($this->stream);
    }

    /**
     * @param int operation
     * @return boolean
     */
    public function stream_lock($operation)
    {
        return flock($this->stream, $operation);
    }

    /**
     * @param string path
     * @param int option flag
     * @param mixed value
     * @return boolean
     * @todo Implement this
     */
    public function stream_metadata($path, $option, $value)
    {
        throw new \Exception("Not yet implemented: " . self::class . "::" . __METHOD__);
    }

    /**
     * Fill/Refill read buffer
     */
    protected function refillReadBuffer()
    {
        $buffer = "";
        if (false === ($buffer = @fread($this->stream, $this->rbLen))) {
            // @todo throw exception?
            if ($this->stream_eof()) {
                throw new EndOfFileException("Reached end of stream");
            }
            return false;
        }
        if ($bufferLen = strlen($buffer)) {
            $replacements = array();
            // @todo move this into its own method
            $i = 0;
            while ($char = $buffer[$i++]) {
                if (in_array($char, $this->specialChars)) {
                    $replacements[$i] = $this->encodeChar($char);
                }
            }
            $this->rb = $buffer;
            $this->rbRepl = $replacements;
        }
        return $bufferLen;
    }

    protected function encodeChar($char)
    {
        return sprintf(self::ENCODE_FORMAT, ord($char));
    }

    protected function readFromBuffer($count)
    {
        if (!$read = substr($this->rb, $this->rbPos)) {
            $read = "";
        }
        $offset = 0;
        $this->rbPos += strlen($read);
        foreach ($this->rbRepl as $pos => $repl) {
            // $encoded = sprintf(self::ENCODE_FORMAT, ord($repl));
            $encoded = $repl;
            $top = substr($read, 0, $pos + $offset - 1);
            $bottom = substr($read, strlen($top)+1);
            $read = $top . $encoded . $bottom;
            $offset = strlen($encoded) + $offset;
        }
        return $read;
    }

    /**
     * Read data from the stream
     * This is where the magic happens. This method right here is the entire
     * reason for writing this stream wrapper. It allows for you to read from a
     * file as you would any other file, but before giving you the data you seek,
     * it replaces any special characters (newlines, delimiters, etc.), configurable
     * via the stream_set_option method, with special placeholders but without
     * affecting the read character count. For example, lets say you want to read
     * 100 characters and two quoted newline characters fall within those 100
     * characters. This will replace those two newlines with something like
     * [[=%10=]] but the returned string will actually be 116 characters because
     * it will only count the encoded newline as a single character. A newline
     * sequence such as \r\n will look like [[=%10=]][[=%13=]] rather than having
     * its own special escape sequence.
     *
     * @todo Implement buffering to make the following statement true
     * To achieve maximum performance and cut down on unnecessary I/O, this class
     * buffers a certain (configurable) amount of data into memory and reads from
     * that rather than using I/O functions for every call to fread() or similar.
     *
     * @param int count
     * @return string
     */
    public function stream_read($count)
    {
        $data = "";
        while (strlen($data) < $count) {
            if ($this->rbPos >= $this->rbCurLen) {
                try {
                    $this->rbCurLen = $this->refillReadBuffer();
                } catch (EndOfFileException $e) {
                    // no more data left to be read, break out of loop and return data
                    break;
                }
            }
            /**
             * @todo I think this is going to be a problem. When 100 bytes are
             *     requested and three chars are replaced by their encoded counter-parts,
             *     that 100 bytes is going to be more like 112 characters or something.
             *     PHP issues an error when more data is returned than is asked
             *     for. I think that is sort of an insurmountable issue. Think
             *     about it, but it may be that rather than this method, you may
             *     end up having to do something where you read the data using a
             *     special object that keeps track of the positions of special
             *     characters as it reads and then provides the reader with a list
             *     of position => ascii code replacements...
             */
            $data .= $this->readFromBuffer($count);
        }
        // return $data;
        return substr($data, 0, $count);
    }

    /**
     * @param int offset
     * @param int whence flag
     * @return boolean
     */
    public function stream_seek($offset, $whence = SEEK_SET)
    {

    }

    /**
     * @param int option flag
     * @param int arg1
     * @param int arg2
     * @return boolean
     */
    public function stream_set_option($option, $arg1, $arg2)
    {

    }

    /**
     * @return array
     */
    public function stream_stat()
    {

    }

    /**
     * @return int
     */
    public function stream_tell()
    {

    }

    /**
     * @param int new size
     * @return boolean
     */
    public function stream_truncate($new_size)
    {

    }

    /**
     * @param string data
     * @return int
     */
    public function stream_write($data)
    {

    }

    /**
     * @param string path
     * @return boolean
     */
    public function unlink($path)
    {

    }

    /**
     * @param string path
     * @param int flags
     * @return array
     */
    public function url_stat($path, $flags)
    {

    }
}
