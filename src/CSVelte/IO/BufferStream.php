<?php
/**
 * CSVelte: Slender, elegant CSV for PHP.
 *
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @version   v0.2.1
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace CSVelte\IO;

use CSVelte\Traits\IsReadable;
use CSVelte\Traits\IsWritable;
use CSVelte\Contract\Streamable;

/**
 * Buffered Stream.
 *
 * Read operations pull from the buffer, write operations fill up the buffer.
 * When the buffer reaches a
 *
 * @package    CSVelte
 * @subpackage CSVelte\IO
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 * @since      v0.2.1
 * @todo       Add methods to convert KB and MB to bytes so that you don't have
 *             to actually know how many bytes are in 16KB. You would just do
 *             $buffer = new BufferStream('16KB');
 */
class BufferStream implements Streamable
{
    use IsReadable, IsWritable;

    /**
     * Buffer contents
     * @var string A string containing the buffer contents
     */
    protected $buffer = '';

    /**
     * Is stream readable?
     *
     * @var boolean Whether stream is readable
     */
    protected $readable = true;

    /**
     * Is stream writable?
     *
     * @var boolean Whether stream is writable
     */
    protected $writable = true;

    /**
     * Is stream seekable?
     *
     * @var boolean Whether stream is seekable
     */
    protected $seekable = false;

    /**
     * @var array Stream meta data
     *            hwm: "high water mark" - once buffer reaches this number (in bytes)
     *                 write() operations will begin returning false defaults to 16384 bytes (16KB)
     */
    protected $meta = [
        'hwm' => 16384
    ];

    /**
     * Instantiate a buffer stream.
     *
     * Instantiate a new buffer stream, optionally changing the high water mark
     * from its default of 16384 bytes (16KB). Once buffer reaches high water
     * mark, write operations will begin returning false. It's possible for buffer
     * size to exceed this level since it is only AFTER it is reached that writes
     * begin returning false.
     *
     * @param int Number (in bytes) representing buffer "high water mark"
     */
    public function __construct($hwm = null)
    {
        if (!is_null($hwm)) {
            $this->meta['hwm'] = $hwm;
        }
    }

    public function isEmpty()
    {
        return $this->getSize() === 0;
    }

    public function isFull()
    {
        return $this->getSize() >= $this->getMetadata('hwm');
    }

    /**
     * Readability accessor.
     *
     * Despite the fact that any class that implements this interface must also
     * define methods such as read and readLine, that is no guarantee that an
     * object will necessarily be readable. This method should tell the user
     * whether a stream is, in fact, readable.
     *
     * @return boolean True if readable, false otherwise
     */
    public function isReadable()
    {
        return $this->readable;
    }

    /**
     * Read in the specified amount of characters from the input source
     *
     * @param integer Amount of characters to read from input source
     * @return string|boolean The specified amount of characters read from input source
     */
    public function read($chars)
    {
        $data = substr($this->buffer, 0, $chars);
        $this->buffer = substr($this->buffer, $chars);
        return $data;
    }

    /**
     * Read the entire stream, beginning to end.
     *
     * In most stream implementations, __toString() differs from getContents()
     * in that it returns the entire stream rather than just the remainder, but
     * due to the way this stream works (sort of like a conveyor belt), this
     * method is an alias to getContents()
     *
     * @return string The entire stream, beginning to end
     */
    public function __toString()
    {
        return $this->getContents();
    }

    /**
     * Read the remainder of the stream
     *
     * @return string The remainder of the stream
     */
    public function getContents()
    {
        $buffer = $this->buffer;
        $this->buffer = "";
        return $buffer;
    }

    /**
     * Return the size (in bytes) of this readable (if known).
     *
     * @return int|null Size (in bytes) of this readable
     */
    public function getSize()
    {
        return strlen($this->buffer);
    }

    /**
     * Return the current position within the stream/readable
     *
     * @return int The current position within readable
     */
    public function tell()
    {
        return false;
    }

    /**
     * Determine whether the end of the readable resource has been reached
     *
     * @return boolean Whether we're at the end of the readable
     */
    public function eof()
    {
        return empty($this->buffer);
    }

    /**
     * File must be able to be rewound when the end is reached
     */
    public function rewind()
    {
        $this->buffer = '';
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     *
     * The keys returned are identical to the keys returned from PHP's
     * stream_get_meta_data() function.
     *
     * @param string $key Specific metadata to retrieve.
     * @return array|mixed|null Returns an associative array if no key is
     *     provided. Returns a specific key value if a key is provided and the
     *     value is found, or null if the key is not found.
     * @see http://php.net/manual/en/function.stream-get-meta-data.php
     */
    public function getMetadata($key = null)
    {
        if (!is_null($key)) {
            return isset($this->meta[$key]) ? $this->meta[$key] : null;
        }
        return $this->meta;
    }

    /**
     * Closes the stream and any underlying resources.
     *
     * @return void
     */
    public function close()
    {
        $this->buffer = false;
    }

    /**
     * Separates any underlying resources from the stream.
     *
     * After the stream has been detached, the stream is in an unusable state.
     *
     * @return string|null Underlying PHP stream, if any
     */
    public function detach()
    {
        $buffer = $this->buffer;
        $this->buffer = false;
        return $buffer;
    }

    /**
     * Writability accessor.
     *
     * Despite the fact that any class that implements this interface must also
     * define methods such as write and writeLine, that is no guarantee that an
     * object will necessarily be writable. This method should tell the user
     * whether a stream is, in fact, writable.
     *
     * @return boolean True if writable, false otherwise
     */
    public function isWritable()
    {
        return $this->writable;
    }

    /**
     * Write data to the output.
     *
     * @param string The data to write
     * @return int The number of bytes written
     */
    public function write($data)
    {
        if ($this->getSize() >= $this->getMetadata('hwm')) {
            return false;
        }
        $this->buffer .= $data;
        return strlen($data);
    }

     /**
      * Seekability accessor.
      *
      * Despite the fact that any class that implements this interface must also
      * define methods such as seek, that is no guarantee that an
      * object will necessarily be seekable. This method should tell the user
      * whether a stream is, in fact, seekable.
      *
      * @return boolean True if seekable, false otherwise
      */
    public function isSeekable()
    {
        return $this->seekable;
    }

    /**
     * Seek to specified offset.
     *
     * @param integer Offset to seek to
     * @param integer Position from whence the offset should be applied
     * @return boolean True if seek was successful
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        return $this->seekable;
    }

}
