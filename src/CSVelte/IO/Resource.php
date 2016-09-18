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
 * @since      v0.2.1
 */
class Resource
{
    /**
     * Available base access modes
     * @var string base access mode must be one of these letters
     */
    protected static $bases = "rwaxc";

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
     * @todo I think I can get rid of this by simply checking whether base is a
     *     particular letter OR plus is present... try it
     * @todo Why are x and c (alone) not even on either of these lists?
     *       I just figured out why... readable and writable default to false. So
     *       only modes that change that default behavior are listed here
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
     * Extra context to open the resource with.
     *
     * An associative array of context options and parameters.
     *
     * @var array An associative array of stream context options and params
     * @see http://php.net/manual/en/stream.contexts.php
     */
    protected $context = [
        'options' => [],
        'params' => []
    ];

    /**
     * Context resource handle.
     *
     * Holds a context resource handle object for $this->context
     *
     * @var resource The context resource handle
     */
    protected $crh;

    /**
     * Should fopen use include path?
     *
     * @var boolean True if fopen should use the include path to find potential files
     */
    protected $useIncludePath;

    /**
     * Base open mode.
     *
     * @var string A single character for base open mode (r, w, a, x or c)
     */
    protected $base = '';

    /**
     * Plus reading or plus writing.
     *
     * @var string Either a plus or an empty string
     */
    protected $plus = '';

    /**
     * Binary or text flag.
     *
     * @var string Either "b" or "t" for binary or text
     */
    protected $flag = '';

    /**
     * Does access mode string indicate readability?
     *
     * @var string Whether access mode indicates readability
     */
    protected $readable = false;

    /**
     * Does access mode string indicate writability
     *
     * @var string Whether access mode indicates writability
     */
    protected $writable = false;

    /**
     * Resource constructor.
     *
     * Instantiates a stream resource. If lazy is set to true, the connection
     * is delayed until the first call to getResource().
     *
     * @param string|resource $uri  The URI to connect to OR a stream resource handle
     * @param string $mode The connection mode
     * @param boolean $lazy Whether connection should be deferred until an I/O
     *     operation is requested (such as read or write) on the attached stream
     * @throws \CSVelte\Exception\IOException if connection fails
     * @todo Does stream_get_meta_data belong in Stream or Resource?
     */
    public function __construct($uri, $mode = null, $lazy = null, $use_include_path = null, $context_options = null, $context_params = null)
    {
        // first, check if we're wrapping an existing stream resource
        if (is_resource($handle = $uri)) {
            if (($resource_type = get_resource_type($handle)) != ($exp_resource_type = "stream")) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid stream resource type for %s, expected "%s", got: "%s"',
                    __METHOD__,
                    $exp_resource_type,
                    $resource_type
                ));
            }
            // set all this manually
            $meta = stream_get_meta_data($handle);
            $this->setUri($meta['uri'])
                 ->setMode($meta['mode']);
            $this->conn = $handle;
            return;
        }

        // ok we're opening a new stream resource handle
        $this->setUri($uri)
             ->setMode($mode)
             ->setLazy($lazy)
             ->setUseIncludePath($use_include_path)
             ->setContext($context_options, $context_params);
        if (!$this->isLazy()) {
            $this->connect();
        }
    }

    /**
     * Class destructor
     */
    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * Invoke magic method.
     *
     * Returns the underlying stream resource when object is accessed as a function
     *
     * @return resource The underlying stream resource
     */
    public function __invoke()
    {
        return $this->getHandle();
    }

    /**
     * Connect (open connection) to file/stream.
     *
     * File open is (by default) delayed until the user explicitly calls connect()
     * or they request the resource handle with getHandle().
     *
     * @return boolean True if connection was successful
     * @throws \CSVelte\Exception\IOException if connection fails
     */
    public function connect()
    {
        if (!$this->isConnected()) {
            $that = $this;
            $e = null;
            set_error_handler(function ($errno, $errstr, $errfile, $errline) use ($that, &$e) {
                $e = new IOException(sprintf(
                    "Could not open connection for %s using mode %s.",
                    $that->getUri(),
                    $that->getMode()
                ), IOException::ERR_STREAM_CONNECTION_FAILED);
            });
            $this->conn = fopen(
                $this->getUri(),
                $this->getMode(),
                $this->getUseIncludePath(),
                $this->getContext()
            );
            restore_error_handler();
            if ($e) throw $e;
        }
        return $this->isConnected();
    }

    /**
     * Close connection.
     *
     * Close the connection to this stream (if open).
     *
     * @return boolean|null Whether close was successful, or null if already closed
     */
    public function disconnect()
    {
        if ($this->isConnected()) {
            return fclose($this->conn);
        }
        // return null if nothing to close
        return;
    }

    /**
     * Set stream URI.
     *
     * Set the stream URI. Can only be set if the connection isn't open yet.
     * If you try to set the URI on an open resource, an IOException will be thrown
     *
     * @param string $uri The URI for this stream resource to open
     * @return $this
     * @throws \InvalidArgumentException if not a valid stream uri
     * @throws \CSVelte\Exception\IOException if stream has already been opened
     * @todo I'm pretty sure that the parse_url function is too restrictive. It
     *     will reject URIs that are perfectly valid.
     */
    public function setUri($uri)
    {
        $this->assertNotConnected(__METHOD__);

        if (is_object($uri) && method_exists($uri, '__toString')) {
            $uri = (string) $uri;
        }

        if (is_string($uri) && parse_url($uri)) {
            $this->uri = $uri;
            return $this;
        }

        throw new InvalidArgumentException(sprintf(
            'Not a valid stream uri, expected "%s", got: "%s"',
            'string',
            gettype($uri)
        ));
    }

    /**
     * Set the fopen mode.
     *
     * Thank you to GitHub user "binsoul" whose AccessMode class inspired this
     * Also thanks to the author(s) of Guzzle streams implementation, where the
     * readwritehash idea came from. Both libraries are MIT licensed, so my
     * merciless theft of their code is alright.
     *
     * @param string $mode A 1-3 character string determining open mode
     * @return $this
     * @throws \InvalidArgumentException if not a valid stream access mode
     * @throws \CSVelte\Exception\IOException if stream has already been opened
     * @see http://php.net/manual/en/function.fopen.php
     * @see https://github.com/binsoul/io-stream/blob/master/src/AccessMode.php
     * @see https://raw.githubusercontent.com/guzzle/streams/master/src/Stream.php
     * @todo convert $mode to lower case and test it
     */
    public function setMode($mode = null)
    {
        $this->assertNotConnected(__METHOD__);
        if (is_null($mode)) $mode = "r+b";

        $mode = substr($mode, 0, 3);
        $rest = substr($mode, 1);

        $base = substr($mode, 0, 1);
        $plus = (strpos($rest, '+') !== false) ? '+' : '';
        $flag = trim($rest, '+');

        $this->flag = '';
        $this->setBaseMode($base)
             ->setIsPlus($plus == '+')
             ->setIsText($flag == 't')
             ->setIsBinary($flag == 'b');

        return $this;
    }

    /**
     * Update access parameters.
     *
     * After changing any of the access mode parameters, this method must be
     * called in order for readable and writable to stay accurate.
     *
     * @return $this
     */
    protected function updateAccess()
    {
        $this->readable = isset(self::$readWriteHash['read'][$this->getMode()]);
        $this->writable = isset(self::$readWriteHash['write'][$this->getMode()]);
        return $this;
    }

    /**
     * Set base access mode character.
     *
     * @param string $base The base mode character (must be one of "rwaxc")
     * @return $this
     * @throws \InvalidArgumentException If passed invalid base char
     * @throws \CSVelte\Exception\IOException if stream has already been opened
     */
    public function setBaseMode($base)
    {
        $this->assertNotConnected(__METHOD__);
        if (strpos(self::$bases, $base) === false) {
            throw new InvalidArgumentException("\"{$base}\" is not a valid base stream access mode.");
        }
        $this->base = $base;
        return $this->updateAccess();
    }

    /**
     * Set plus mode.
     *
     * @param boolean $isPlus Whether base access mode should include the + sign
     * @return $this
     * @throws \CSVelte\Exception\IOException if stream has already been opened
     */
    public function setIsPlus($isPlus)
    {
        $this->assertNotConnected(__METHOD__);
        $this->plus = $isPlus ? '+' : '';
        return $this->updateAccess();
    }

    /**
     * Set binary-safe mode.
     *
     * @param boolean $isBinary Whether binary safe mode or not
     * @return $this
     * @throws \CSVelte\Exception\IOException if stream has already been opened
     */
    public function setIsBinary($isBinary)
    {
        $this->assertNotConnected(__METHOD__);
        if ($isBinary) {
            $this->flag = 'b';
        }
        return $this;
    }

    /**
     * Set text mode.
     *
     * @param boolean $isText Whether text mode or not
     * @return $this
     * @throws \CSVelte\Exception\IOException if stream has already been opened
     */
    public function setIsText($isText)
    {
        $this->assertNotConnected(__METHOD__);
        if ($isText) {
            $this->flag = 't';
        }
        return $this;
    }

    /**
     * Set lazy flag.
     *
     * Set the lazy flag, which tells the class whether to defer the connection
     * until the user specifically requests it.
     *
     * @param boolean|null Whether or not to "lazily" open the stream
     * @return $this
     */
    protected function setLazy($lazy)
    {
        if (is_null($lazy)) $lazy = true;
        $this->lazy = (boolean) $lazy;
        return $this;
    }

    /**
     * Set use include path flag.
     *
     * Sets whether or not fopen should search the include path for files. Can
     * only be set if resource isn't open already. If called when resource is
     * already open an exception will be thrown.
     *
     * @param boolean $use_include_path Whether to search include path for files
     * @throws \CSVelte\Exception\IOException
     * @return $this
     */
    public function setUseIncludePath($use_include_path)
    {
        $this->assertNotConnected(__METHOD__);
        $this->useIncludePath = (boolean) $use_include_path;
        return $this;
    }

    /**
     * Set stream context options and params.
     *
     * Sets arrays of stream context options and params. Check out the URI below
     * for more on stream contexts.
     *
     * @param array|null $options Stream context options
     * @param array|null $params  Stream Context params
     * @return $this
     * @see http://php.net/manual/en/stream.contexts.php
     */
    public function setContext($options = null, $params = null)
    {
        if (is_array($options)) {
            foreach ($options as $wrap => $opts) {
                $this->setContextOptions($opts, $wrap);
            }
        }
        if (!is_null($params)) {
            $this->setContextParams($params);
        }
        return $this;
    }

    /**
     * Set context resource directly
     * @param resource|null $context Stream context resource to set directly
     * @return $this
     * @see http://php.net/manual/en/function.stream-context-create.php
     * @todo Need to write a unit test for passing this method a null value
     */
    public function setContextResource($context)
    {
        if (!is_null($context)) {
            if (!is_resource($context) || get_resource_type($context) != "stream-context") {
                throw new InvalidArgumentException(sprintf(
                    "Invalid argument for %s. Expecting resource of type \"stream-context\" but got: \"%s\"",
                    __METHOD__,
                    gettype($context)
                ));
            }
            // don't need to call updateContext() because its already a context resource
            $this->crh = $context;
        }
        return $this;
    }

    /**
     * Update the stream context.
     *
     * After setting/updating stream context options and/or params, this method
     * must be called in order to update the stream context resource.
     *
     * @return $this
     */
    protected function updateContext()
    {
        // if already connected, set the options on the context resource
        // otherwise, it will be set at connection time
        if ($this->isConnected()) {
            // set options and params on existing stream resource
            stream_context_set_params(
                $this->getContext(),
                $this->getContextParams() + [
                    'options' => $this->getContextOptions()
                ]
            );
        }
        return $this;
    }

    /**
     * Set context options.
     *
     * Sets stream context options for this stream resource.
     *
     * @param array $options An array of stream context options
     * @param string $wrapper The wrapper these options belong to (if no wrapper
     *     argument, then $options should be an associative array with key being
     *     a wrapper name and value being its options)
     * @return $this
     * @throws \InvalidArgumentException if passed invalid options or wrapper
     * @see http://php.net/manual/en/stream.contexts.php
     */
    public function setContextOptions($options, $wrapper = null)
    {
        if (is_array($options)) {
            if (is_null($wrapper)) {
                $this->context['options'] = $options;
            } else {
                $this->assertValidWrapper($wrapper);
                $this->context['options'][$wrapper] = $options;
            }
            $this->updateContext();
            return $this;
        }
        throw new InvalidArgumentException("Context options must be an array, got: " . gettype($options));
    }

    /**
     * Set context params.
     *
     * Set the context params for this stream resource.
     *
     * @param array $params An array of stream resource params
     * @return $this
     * @throws \InvalidArgumentException if passed invalid params
     * @see http://php.net/manual/en/stream.contexts.php
     */
    public function setContextParams($params)
    {
        if (is_array($params)) {
            $this->context['params'] = $params;
            $this->updateContext();
            return $this;
        }
        throw new InvalidArgumentException("Context parameters must be an array, got: " . gettype($params));
    }

    /**
     * Get context options for this stream resource.
     *
     * Returns the stream context options for this stream resource. Either all
     * options for all wrappers, or just the options for the specified wrapper.
     *
     * @param  string $wrapper If present, return options only for this wrapper
     * @return array Context options (either all or for specified wrapper)
     * @throws \InvalidArgumentException if the wrapper doesn't exist
     */
    public function getContextOptions($wrapper = null)
    {
        if (is_null($wrapper)) {
            return $this->context['options'];
        }
        $this->assertValidWrapper($wrapper);
        if (isset($this->context['options'][$wrapper])) {
            return $this->context['options'][$wrapper];
        }
    }

    /**
     * Get context params for this stream resource.
     *
     * Returns the stream context params for this stream resource.
     *
     * @return array Context params for this stream resource
     */
    public function getContextParams()
    {
        return $this->context['params'];
    }

    /**
     * Get stream context resource.
     * @return resource|null The stream context resource
     */
    public function getContext()
    {
        // if context resource hasn't been created, create one
        if (is_null($this->crh)) {
            $this->crh = stream_context_create(
                $this->getContextOptions(),
                $this->getContextParams()
            );
        }
        // return context resource handle
        return $this->crh;
    }

    /**
     * Retrieve underlying stream resource handle.
     *
     * An accessor method for the underlying stream resource object. Also triggers
     * stream connection if in lazy open mode. Because this method may potentially
     * call the connect() method, it is possible that it may throw an exception
     * if there is some issue with opening the stream.
     *
     * @return resource The underlying stream resource handle
     * @throws \CSVelte\Exception\IOException
     */
    public function getHandle()
    {
        if (!$this->isConnected() && $this->isLazy()) {
            $this->connect();
        }
        return $this->conn;
    }

    /**
     * Is the stream connection open?
     *
     * Tells you whether this stream resource is open or not.
     *
     * @return boolean Whether the stream is open
     */
    public function isConnected()
    {
        return is_resource($this->conn);
    }

    /**
     * Get the stream URI.
     *
     * Accessor method for stream URI.
     *
     * @return string The stream URI
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Get the access mode.
     *
     * Tells you what the access mode is. This is the short string of characters
     * that you would pass to the fopen function to tell it how to open a file/stream
     *
     * @return string The file/stream access mode
     * @see http://php.net/manual/en/function.fopen.php
     */
    public function getMode()
    {
        return sprintf(
            "%s%s%s",
            $this->base,
            $this->plus,
            $this->flag
        );
    }

    /**
     * Is access mode binary-safe?
     * @return boolean Whether binary-safe flag is set
     */
    public function isBinary()
    {
        return $this->flag == "b";
    }

    /**
     * Is stream connected in text mode?
     * @return boolean Whether text mode flag is set
     */
    public function isText()
    {
        return $this->flag == "t";
    }

    /**
     * Is this a lazy open resource?
     * @return boolean Whether this is a lazily-opened resource
     */
    public function isLazy()
    {
        return $this->lazy;
    }

    /**
     * Should fopen search include path?
     * @return boolean Whether fopen should search include path for files
     */
    public function getUseIncludePath()
    {
        return $this->useIncludePath;
    }

    /**
     * Does the access mode string indicate readability?
     *
     * Readable, in this context, only refers to the manner in which this stream
     * resource was opened (if it even is opened yet). It is no indicator about
     * whether or not the underlying stream actually supports read operations.
     * It simply refers to the access mode string passed to it by the user.
     *
     * @return boolean Whether access mode indicates readability
     */
    public function isReadable()
    {
        return $this->readable;
    }

    /**
     * Does the access mode string indicate writability?
     *
     * Writable, in this context, only refers to the manner in which this stream
     * resource was opened (if it even is opened yet). It is no indicator about
     * whether or not the underlying stream actually supports write operations.
     * It simply refers to the access mode string passed to it by the user.
     *
     * @return boolean Whether access mode indicates writability
     */
    public function isWritable()
    {
        return $this->writable;
    }

    /**
     * Is cursor positioned at the beginning of stream?
     *
     * Returns true if this stream resource's access mode positions the internal
     * cursor at the beginning of the stream.
     *
     * @return boolean Whether cursor positioned at beginning of stream
     */
    public function isCursorPositionedAtBeginning()
    {
        return $this->base != 'a';
    }

    /**
     * Is cursor positioned at the end of stream?
     *
     * Returns true if this stream resource's access mode positions the internal
     * cursor at the end of the stream.
     *
     * @return boolean Whether cursor positioned at end of stream
     */
    public function isCursorPositionedAtEnd()
    {
        return $this->base == 'a';
    }

    /**
     * Is content truncated to zero-length on opening?
     *
     * Returns true if this stream resource's access mode indicates truncation of
     * stream content to zero-length upon opening.
     *
     * @return boolean Whether stream content is truncated on opening
     */
    public function isTruncated()
    {
        return $this->base == 'w';
    }

    /**
     * Does stream access mode indicate file creation?
     *
     * Returns true if this stream's access mode implies that PHP will attempt to
     * create a file if none exists.
     *
     * @return boolean Whether PHP should attempt to create file at $uri
     */
    public function attemptsFileCreation()
    {
        return $this->base != 'r';
    }

    /**
     * Does stream access mode indicate the rejection of existing files?
     *
     * Returns true if this stream's access mode implies that PHP will fail to
     * open a file if it already exists.
     *
     * @return boolean Whether PHP should attempt to create file at $uri
     */
    public function rejectsExistingFiles()
    {
        return $this->base == 'x';
    }

    /**
     * Are write operations appended to the end of the stream?
     *
     * Returns true if write operations are appended to the end of the stream
     * regardless of the position of the read cursor.
     *
     * @return boolean Whether write operations ore always appended
     */
    public function appendsWriteOps()
    {
        return $this->base == 'w';
    }

    /**
     * Assert that stream resource is not open.
     *
     * Used internally to ensure that stream is not open, since some methods should
     * only be called on unopened stream resources.
     *
     * @param  string The method that is asserting
     * @return void
     * @throws \CSVelte\Exception\IOException if stream is open
     */
    protected function assertNotConnected($method)
    {
        if ($this->isConnected()) {
            throw new IOException("Cannot perform this operation on a stream once it has already been opened: {$method}", IOException::ERR_STREAM_ALREADY_OPEN);
        }
    }

    /**
     * Assert that given wrapper is a valid, registered stream wrapper
     *
     * Used internally to ensure that a given stream wrapper is valid and available
     *
     * @param  string The name of the stream wrapper
     * @return void
     * @throws \InvalidArgumentException if wrapper doesn't exist
     */
    protected function assertValidWrapper($name)
    {
        if (!in_array($name, stream_get_wrappers())) {
            throw new InvalidArgumentException("{$name} is not a known stream wrapper.");
        }
    }

}
