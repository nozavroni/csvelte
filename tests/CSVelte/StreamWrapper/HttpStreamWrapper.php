<?php
namespace CSVelteTest\StreamWrapper;
/**
 * Class MockHttpStreamWrapper
 * Stunt double of the PHP HTTP stream wrapper.
 * @author Mykola Bespaliuk
 */
class HttpStreamWrapper implements
    \IteratorAggregate, \ArrayAccess, \Countable{
    /**
     * Using static properties here because it's easy to set them up
     * before running request and in stream_open method corresponding
     * object properties are overridden with the
     * contents of the static properties here.
     */
    public static $mockBodyData = '';
    public static $mockResponseCode = 'HTTP/1.1 200 OK';
    public $context;
    public $position = 0;
    public $bodyData = 'test body data';
    public $responseCode = '';

    /**
     * @var array $foo
     * Example:
     * array(
     *     0 => 'HTTP/1.0 301 Moved Permantenly',
     *     1 => 'Cache-Control: no-cache',
     *     2 => 'Connection: close',
     *     3 => 'Location: http://example.com/foo.jpg',
     *     4 => 'HTTP/1.1 200 OK',
     *     ...
     */
    protected $foo = array();

    public function getContext()
    {
        return $this->context;
    }

    public function getContextOptions()
    {
        return stream_context_get_options($this->context);
    }

    public function getContextParams()
    {
        return stream_context_get_params($this->context);
    }

    /* IteratorAggregate */
    public function getIterator() {
        return new \ArrayIterator($this->foo);
    }
    /* ArrayAccess */
    public function offsetExists($offset) {
        return array_key_exists($offset, $this->foo);
    }
    public function offsetGet($offset ) {
        return $this->foo[$offset];
    }
    public function offsetSet($offset, $value) {
        $this->foo[$offset] = $value;
    }
    public function offsetUnset($offset) {
        unset($this->foo[$offset]);
    }

    /* Countable */
    public function count() {
        return count($this->foo);
    }

    /* StreamWrapper */
    public function stream_open($path, $mode, $options, &$opened_path) {
        $this->bodyData = self::$mockBodyData;
        $this->responseCode = self::$mockResponseCode;
        array_push($this->foo, self::$mockResponseCode);
        return true;
    }

    public function stream_read($count) {
        if ($this->position > strlen($this->bodyData)) {
            return false;
        }
        $result =  substr($this->bodyData, $this->position, $count);
        $this->position += $count;
        return $result;
    }

    public function stream_eof() {
        return $this->position >= strlen($this->bodyData);
    }

    public function stream_stat() {
        return array('wrapper_data' => array('test'));
    }

    public function stream_tell() {
        return $this->position;
    }
}
