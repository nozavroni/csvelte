<?php

/*
 * CSVelte: Slender, elegant CSV for PHP
 * Inspired by Python's CSV module and Frictionless Data and the W3C's CSV
 * standardization efforts, CSVelte was written in an effort to take all the
 * suck out of working with CSV.
 *
 * @version   v0.2.3
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace CSVelte\Contract;

/**
 * Streamable Interface.
 *
 * Implementors of this class will be acceptable by the reader, writer, taster,
 * and various other classes that expect stream-like data. This interface replaces
 * the old Readable, Writable, and Seekable interfaces which were useless.
 *
 * @package CSVelte
 * @subpackage Contract (Interfaces)
 *
 * @since v0.2.1
 */
interface Streamable
{
    /**
     * Read the entire stream, beginning to end.
     *
     * Implementors of this method must seek to the beginning of the stream and
     * then read the entire contents of the stream and return it.
     *
     * @return string The entire stream, beginning to end
     */
    public function __toString();

    /**
     * Readability accessor.
     *
     * Despite the fact that any class that implements this interface must also
     * define methods such as read and readLine, that is no guarantee that an
     * object will necessarily be readable. This method should tell the user
     * whether a stream is, in fact, readable.
     *
     * @return bool True if readable, false otherwise
     */
    public function isReadable();

    /**
     * Read in the specified amount of characters from the input source.
     *
     * @param int $chars Amount of characters to read from input source
     *
     * @return string|false The specified amount of characters read from input source
     */
    public function read($chars);

    /**
     * Read a single line from input source and return it (and move pointer to )
     * the beginning of the next line).
     *
     * @param string $eol       Line terminator sequence/character
     * @param int    $maxLength The maximum line length to return
     *
     * @return string|false The next line from the input source
     */
    public function readLine($eol = PHP_EOL, $maxLength = null);

    /**
     * Read the remainder of the stream.
     *
     * @return string|null The remainder of the stream
     */
    public function getContents();

    /**
     * Return the size (in bytes) of this readable (if known).
     *
     * @return int|null Size (in bytes) of this readable
     */
    public function getSize();

    /**
     * Return the current position within the stream/readable.
     *
     * @return int|false The current position within readable
     */
    public function tell();

    /**
     * Determine whether the end of the readable resource has been reached.
     *
     * @return bool Whether we're at the end of the readable
     */
    public function eof();

    /**
     * File must be able to be rewound when the end is reached.
     */
    public function rewind();

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     *
     * The keys returned are identical to the keys returned from PHP's
     * stream_get_meta_data() function.
     *
     * @param string $key Specific metadata to retrieve.
     *
     * @return array|mixed|null Returns an associative array if no key is
     *                          provided. Returns a specific key value if a key is provided and the
     *                          value is found, or null if the key is not found.
     *
     * @see http://php.net/manual/en/function.stream-get-meta-data.php
     */
    public function getMetadata($key = null);

    /**
     * Closes the stream and any underlying resources.
     */
    public function close();

    /**
     * Separates any underlying resources from the stream.
     *
     * After the stream has been detached, the stream is in an unusable state.
     *
     * @return mixed Underlying PHP stream, if any
     */
    public function detach();

    /**
     * Writability accessor.
     *
     * Despite the fact that any class that implements this interface must also
     * define methods such as write and writeLine, that is no guarantee that an
     * object will necessarily be writable. This method should tell the user
     * whether a stream is, in fact, writable.
     *
     * @return bool True if writable, false otherwise
     */
    public function isWritable();

    /**
     * Write data to the output.
     *
     * @param string $data The data to write
     *
     * @return int|false The number of bytes written
     */
    public function write($data);

    /**
     * Seekability accessor.
     *
     * Despite the fact that any class that implements this interface must also
     * define methods such as seek, that is no guarantee that an
     * object will necessarily be seekable. This method should tell the user
     * whether a stream is, in fact, seekable.
     *
     * @return bool True if seekable, false otherwise
     */
    public function isSeekable();

    /**
     * Seek to specified offset.
     *
     * @param int $offset Offset to seek to
     * @param int $whence Position from whence the offset should be applied
     *
     * @return bool|false True if seek was successful
     */
    public function seek($offset, $whence = SEEK_SET);
}
