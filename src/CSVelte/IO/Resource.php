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

use \InvalidArgumentException;
use CSVelte\Exception\IOException;

/**
 * Stream Resource.
 *
 * Represents a stream resource connection. May be open or closed. This allows
 * me to provide a nice, clean, easy-to-use interface for opening stream
 * resources in a particular mode as well as to lazy-open a stream.
 *
 * @package    CSVelte
 * @subpackage CSVelte\IO
 * @copyright  (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author     Luke Visinoni <luke.visinoni@gmail.com>
 * @since      v0.2
 */
class Resource
{
    /**
     * Hash of readable/writable stream open mode types.
     *
     * Mercilessly stolen from:
     * https://github.com/guzzle/streams/blob/master/src/Stream.php
     *
     * My kudos and sincere thanks go out to Michael Dowling and Graham Campbell
     * of the guzzle/streams PHP package. Thanks for the inspiration (in some cases)
     * and the not suing me for outright theft (in this case).
     *
     * @var array Hash of readable and writable stream types
     */
    protected static $readWriteHash = [
        'read' => [
            'r' => true, 'w+' => true, 'r+' => true, 'x+' => true, 'c+' => true,
            'rb' => true, 'w+b' => true, 'r+b' => true, 'x+b' => true,
            'c+b' => true, 'rt' => true, 'w+t' => true, 'r+t' => true,
            'x+t' => true, 'c+t' => true, 'a+' => true,
        ],
        'write' => [
            'w' => true, 'w+' => true, 'rw' => true, 'r+' => true, 'x+' => true,
            'c+' => true, 'wb' => true, 'w+b' => true, 'r+b' => true,
            'x+b' => true, 'c+b' => true, 'w+t' => true, 'r+t' => true,
            'x+t' => true, 'c+t' => true, 'a' => true, 'a+' => true,
        ],
    ];

    /**
     * Stream URI.
     *
     * Contains the stream URI to connect to.
     *
     * @var string The stream uri
     */
    protected $uri;

    /**
     * Stream resource handle.
     *
     * Contains the underlying stream resource handle (if there is one).
     * Otherwise it will be null.
     *
     * @var resource The stream resource handle
     */
    protected $conn;

    /**
     * Lazy open switch.
     *
     * Determines whether the actual fopen for this resource should be delayed
     * until an I/O operation is performed.
     *
     * @var boolean True if connection is lazy
     */
    protected $lazy;

    /**
     * Resource constructor.
     *
     * Instantiates a stream resource. If lazy is set to true, the connection
     * is delayed until the first call to getResource().
     *
     * @param string  $uri  The URI to connect to
     * @param string  $mode The connection mode
     * @param boolean $lazy Whether connection should be deferred until an I/O
     *     operation is requested (such as read or write) on the attached stream
     */
    public function __construct($uri, $mode = null, $lazy = false)
    {
        $this->setUri($uri)
             ->setMode($mode)
             ->setLazy($lazy);
        if (!$this->isLazy()) {
            $this->conn = $this->connect();
        }
    }

    protected function connect()
    {
        $that = $this;
        $e = null;
        set_error_handler(function ($errno, $errstr, $errfile, $errline) use ($that, &$e) {
            $e = new IOException(sprintf(
                "Could not open connection for %s using mode %s.",
                $that->getUri(),
                $that->getMode()
            ), IOException::ERR_STREAM_CONNECTION_FAILED);
        });
        $handle = fopen($this->getUri(), $this->getMode());
        restore_error_handler();
        if ($e) throw $e;
        return $handle;
    }

    protected function setUri($uri)
    {
        if (parse_url($uri)) {
            $this->uri = $uri;
            return $this;
        }
        throw new InvalidArgumentException("{$uri} is not a valid stream uri.");
    }

    protected function setMode($mode = null)
    {
        if (is_null($mode)) $mode = "r+b";
        $this->readable = isset(self::$readWriteHash['read'][$mode]);
        $this->writable = isset(self::$readWriteHash['write'][$mode]);
        if ($this->readable || $this->writable) {
            $this->mode = $mode;
            return $this;
        }
        throw new InvalidArgumentException("{$mode} is not a valid stream access mode.");
    }

    protected function setLazy($lazy)
    {
        $this->lazy = (boolean) $lazy;
        return $this;
    }

    public function getResource()
    {
        if (!$this->isConnected()) {
            $this->conn = $this->connect();
        }
        return $this->conn;
    }

    public function isConnected()
    {
        return is_resource($this->conn);
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function getMode()
    {
        return $this->mode;
    }

    public function isLazy()
    {
        return $this->lazy;
    }

    public function isReadable()
    {
        return $this->readable;
    }

    public function isWritable()
    {
        return $this->writable;
    }
}
