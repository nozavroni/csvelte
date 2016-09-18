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
     * @todo I think I can get rid of this by simply checking whether base is a
     *     particular letter OR plus is present... try it
     * @todo Why are x and c not even on either of these lists?
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
    protected $base;

    /**
     * Plus reading or plus writing.
     *
     * @var string Either a plus or an empty string
     */
    protected $plus;

    /**
     * Binary or text flag.
     *
     * @var string Either "b" or "t" for binary or text
     */
    protected $flag;

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
    public function __construct($uri, $mode = null, $lazy = null, $use_include_path = null, $context_options = null, $context_params = null)
    {
        $this->setUri($uri)
             ->setMode($mode)
             ->setLazy($lazy)
             ->setUseIncludePath($use_include_path)
             ->setContext($context_options, $context_params);
        if (!$this->isLazy()) {
            $this->connect();
        }
    }

    public function __destruct()
    {
        $this->close();
    }

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

    protected function close()
    {
        if ($this->isConnected()) {
            return fclose($this->conn);
        }
        // return null if nothing to close
        return;
    }

    public function setUri($uri)
    {
        $this->assertNotConnected(__METHOD__);
        if (parse_url($uri)) {
            $this->uri = $uri;
            return $this;
        }
        throw new InvalidArgumentException("{$uri} is not a valid stream uri.");
    }

    /**
     * Set the fopen mode.
     *
     * Thank you to GitHub user "binsoul" whose AccessMode class inspired this
     *
     * @param string $mode A 1-3 character string determining open mode
     * @see http://php.net/manual/en/function.fopen.php
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

        if (strpos("rwaxc", $base) !== false) {
            $this->base = $base;
            $this->plus = $plus;
            $this->flag = $flag;
            $this->readable = isset(self::$readWriteHash['read'][$this->getMode()]);
            $this->writable = isset(self::$readWriteHash['write'][$this->getMode()]);
            return $this;
        }

        throw new InvalidArgumentException("{$mode} is not a valid stream access mode.");
    }

    protected function setLazy($lazy)
    {
        if (is_null($lazy)) $lazy = true;
        $this->lazy = (boolean) $lazy;
        return $this;
    }

    public function setUseIncludePath($use_include_path)
    {
        $this->assertNotConnected(__METHOD__);
        $this->useIncludePath = (boolean) $use_include_path;
        return $this;
    }

// loop over each wrapper and call setcontextoptions then rewrite setcontextoptions
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

    public function getResource()
    {
        if (!$this->isConnected()) {
            $this->connect();
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
        return sprintf(
            "%s%s%s",
            $this->base,
            $this->plus,
            $this->flag
        );
    }

    public function isBinary()
    {
        return $this->flag == "b";
    }

    public function isText()
    {
        return $this->flag == "t";
    }

    public function isLazy()
    {
        return $this->lazy;
    }

    public function getUseIncludePath()
    {
        return $this->useIncludePath;
    }

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

    public function setContextParams($params)
    {
        if (is_array($params)) {
            $this->context['params'] = $params;
            $this->updateContext();
            return $this;
        }
        throw new InvalidArgumentException("Context parameters must be an array, got: " . gettype($params));
    }

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

    public function getContextParams()
    {
        return $this->context['params'];
    }

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

    public function isReadable()
    {
        return $this->readable;
    }

    public function isWritable()
    {
        return $this->writable;
    }

    protected function assertNotConnected($method)
    {
        if ($this->isConnected()) {
            throw new IOException("Cannot perform this operation on a stream once it has already been opened: {$method}", IOException::ERR_STREAM_ALREADY_OPEN);
        }
    }

    protected function assertValidWrapper($name)
    {
        if (!in_array($name, stream_get_wrappers())) {
            throw new InvalidArgumentException("{$name} is not a registered stream wrapper.");
        }
    }

}
