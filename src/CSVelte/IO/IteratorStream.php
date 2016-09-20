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

use \Iterator;
use CSVelte\IO\BufferStream;
use CSVelte\Traits\IsReadable;
use CSVelte\Traits\IsWritable;
use CSVelte\Contract\Streamable;

/**
 * Iterator Stream.
 *
 * A read-only stream that uses an iterable to continuously fill up a buffer as
 * read operations deplete it.
 *
 * @package    CSVelte
 * @subpackage CSVelte\IO
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 * @since      v0.2.1
 */
class IteratorStream implements Streamable
{
    use IsReadable, IsWritable;

    /**
     * Buffer stream
     * @var \CSVelte\IO\BufferStream A BufferStream object
     */
    protected $buffer;

    protected $overflow;

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
    protected $writable = false;

    /**
     * Is stream seekable?
     *
     * @var boolean Whether stream is seekable
     */
    protected $seekable = false;

    /**
     * @var array Any additional options / meta data
     */
    protected $meta = [

    ];

    /**
     * Instantiate an iterator stream
     *
     * Instantiate a new iterator stream. The iterator is used to continually
     * refill a buffer as it is drained by read operations.
     *
     * @param \Iterator The iterator to stream data from
     * @param \CSVelte\IO\BufferIterator|null Either a buffer or null (to use
     *     default buffer)
     */
    public function __construct(Iterator $iter, $buffer = null)
    {
        $this->iter = $iter;
        if (!($buffer instanceof BufferStream)) {
            $buffer = new BufferStream();
        }
        $this->buffer = $buffer;
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

    public function read($bytes)
    {
        $data = '';
        while (strlen($data) < $bytes) {
            if ($this->buffer->isEmpty()) {
                $this->inflateBuffer();
            }
            if (!$read = $this->buffer->read($bytes - strlen($data))) {
                break;
            }
            $data .= $read;
        }
        return $data;
    }

    protected function inflateBuffer()
    {
        while (!$this->buffer->isFull() && $this->iter->valid()) {
            $data = $this->iter->current();
            $this->buffer->write($data);
            $this->iter->next();
        }
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
        $this->rewind();
        return $this->getContents();
    }

    /**
     * Read the remainder of the stream
     *
     * @return string The remainder of the stream
     */
    public function getContents()
    {
        $contents = '';
        while (!$this->eof()) {
            $contents .= $this->read(
                // kind of arbitrary... we have to specify something for the
                // chunk length, so I just used the buffer's "high water mark"
                $this->buffer->getMetadata('hwm')
            );
        }
        return $contents;
    }

    /**
     * Return the size (in bytes) of this stream (if known).
     *
     * @return int|null Size (in bytes) of this stream
     */
    public function getSize()
    {
        // no way to know so return null
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
     * Determine whether the end of the stream has been reached
     *
     * @return boolean Whether we're at the end of the stream
     */
    public function eof()
    {
        return (
            !$this->iter->valid() &&
            $this->buffer->eof()
        );
    }

    /**
     * Rewind to beginning of stream
     */
    public function rewind()
    {
        $this->iter->rewind();
        $this->buffer->rewind();
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
        $buff = $this->buffer->close();
        $iter = true;
        if (method_exists($this->iter, 'close')) {
            $iter = $this->iter->close();
        }
        return $buff && $iter;
    }

    /**
     * Separates any underlying resources from the stream.
     *
     * After the stream has been detached, the stream is in an unusable state.
     *
     * @return string|null Underlying PHP stream, if any
     * @todo I'm not sure what detach is for so I don't know whether what I'm
     *     doing here is right. The reason I have the method at all is because
     *     psr7 StreamInterface has one.f
     */
    public function detach()
    {
        $buffer = $this->buffer;
        $iter = $this->iter;
        $this->buffer = null;
        $this->iter = null;
        return [$iter, $buffer];
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
        return $this->writable;
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
