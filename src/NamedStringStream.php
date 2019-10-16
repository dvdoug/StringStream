<?php
/**
 * Stream wrapper for strings
 * @package StringStream
 * @author Doug Wright
 */
namespace DVDoug\StringStream;

/**
 * Stream wrapper for strings
 * @author Doug Wright
 * @package StringStream
 */
class NamedStringStream {

    /**
     * Content of all streams
     * @var string[]
     */
    private static $strings = array();

    /**
     * Name of stream
     * @var string
     */
    private $name;

    /**
     * Whether this stream can be read
     * @var boolean
     */
    private $read;

    /**
     * Whether this stream can be written
     * @var boolean
     */
    private $write;

    /**
     * Whether this stream should have UNIX-style newlines converted to Windows-style
     * @var boolean
     */
    private $normaliseForWin = false;

    /**
     * Options
     * @var int
     */
    private $options;

    /**
     * Current position within stream
     * @var int
     */
    private $position;

    /**
     * Context
     * @var resource
     */

    public $context;

    /**
     * Clear all named strings
     * @param void
     * @return void
     */
    public static function clear() {
        self::$strings = array();
    }

    /**
     * Dump all named strings
     * @param void
     * @return void
     */
    public static function dump() {
        var_dump(self::$strings);
    }

    /**
     * export all named strings
     * @param void
     * @return string
     */
    public static function save() {
        return json_encode(self::$strings);
    }

    /**
     * import all named strings
     * @param string
     * @return void
     */
    public static function load($data) {
        self::$strings = json_decode($data, true);
    }

    /**
     * Open stream
     * @param string $aPath
     * @param string $aMode
     * @param int $aOptions
     * @param string $aOpenedPath
     * @return boolean
     */
    public function stream_open($aPath, $aMode, $aOptions, &$aOpenedPath) {
        $this->name = substr($aPath, strpos($aPath, '://') + 3);
        $this->options = $aOptions;

        if (strpos($aMode, 't') && defined('PHP_WINDOWS_VERSION_MAJOR')) {
            $this->normaliseForWin = true;
        //    $this->string = preg_replace('/(?<!\r)\n/', "\r\n", $this->string);
        }

        // strip binary/text flags from mode for comparison
        $mode = strtr($aMode, array('b' => '', 't' => ''));

        if (!array_key_exists($this->name, self::$strings)) self::$strings[$this->name] = '';

        switch ($mode) {

            case 'r':
                $this->read = true;
                $this->write = false;
                $this->position = 0;
                break;

            case 'r+':
            case 'c+':
                $this->read = true;
                $this->write = true;
                $this->position = 0;
                break;

            case 'w':
                $this->read = false;
                $this->write = true;
                $this->position = 0;
                $this->stream_truncate(0);
                break;

            case 'w+':
                $this->read = true;
                $this->write = true;
                $this->position = 0;
                $this->stream_truncate(0);
                break;

            case 'a':
                $this->read = false;
                $this->write = true;
                $this->position = strlen(self::$strings[$this->name]);
                break;

            case 'a+':
                $this->read = true;
                $this->write = true;
                $this->position = strlen(self::$strings[$this->name]);
                break;

            case 'c':
                $this->read = false;
                $this->write = true;
                $this->position = 0;
                break;

            default:
                if ($this->options & STREAM_REPORT_ERRORS) {
                    trigger_error(
                        'Invalid mode specified (mode specified makes no sense for this stream implementation)',
                        E_ERROR
                    );
                } else {
                    return false;
                }
        }


        return true;
    }

    /**
     * Read from stream
     * @param int $aBytes number of bytes to return
     * @return string
     */
    public function stream_read($aBytes) {
        if ($this->read) {
            $read = substr(self::$strings[$this->name], $this->position, $aBytes);
            $this->position += strlen($read);

            return $read;
        } else {
            return false;
        }
    }

    /**
     * Write to stream
     * @param string $aData data to write
     * @return int
     */
    public function stream_write($aData) {

        if ($this->normaliseForWin) {
            $data = preg_replace('/(?<!\r)\n/', "\r\n", $aData);
        } else {
            $data = $aData;
        }

        if ($this->write) {
            $left = substr(self::$strings[$this->name], 0, $this->position);
            $right = substr(self::$strings[$this->name], $this->position + strlen($data));
            self::$strings[$this->name] = $left.$data.$right;
            $this->position += strlen($data);

            return strlen($data);
        } else {
            return 0;
        }
    }

    /**
     * Return current position
     * @return int
     */
    public function stream_tell() {
        return $this->position;
    }

    /**
     * Return if EOF
     * @return boolean
     */
    public function stream_eof() {
        return $this->position >= strlen(self::$strings[$this->name]);
    }

    /**
     * Seek to new position
     * @param int $aOffset
     * @param int $aWhence
     * @return boolean
     */
    public function stream_seek($aOffset, $aWhence) {
        switch ($aWhence) {
            case SEEK_SET:
                $this->position = $aOffset;
                $this->truncate_after_seek();

                return true;
                break;

            case SEEK_CUR:
                $this->position += $aOffset;
                $this->truncate_after_seek();

                return true;
                break;

            case SEEK_END:
                $this->position = strlen(self::$strings[$this->name]) + $aOffset;
                $this->truncate_after_seek();

                return true;
                break;

            default:
                return false;
        }
    }

    /**
     * If we've seeked past the end of file, auto truncate
     */
    protected function truncate_after_seek() {
        if ($this->position > strlen(self::$strings[$this->name])) {
            $this->stream_truncate($this->position);
        }
    }

    /**
     * Truncate to given size
     * @param int $aSize
     */
    public function stream_truncate($aSize) {
        if (strlen(self::$strings[$this->name]) > $aSize) {
            self::$strings[$this->name] = substr(self::$strings[$this->name], 0, $aSize);
        } elseif (strlen(self::$strings[$this->name]) < $aSize) {
            self::$strings[$this->name] = str_pad(self::$strings[$this->name], $aSize, "\0", STR_PAD_RIGHT);
        }

        return true;
    }

    /**
     * Return info about stream
     * @return array
     */
    public function stream_stat() {
        return array(
            'dev' => 0,
            'ino' => 0,
            'mode' => 0,
            'nlink' => 0,
            'uid' => 0,
            'gid' => 0,
            'rdev' => 0,
            'size' => strlen(self::$strings[$this->name]),
            'atime' => 0,
            'mtime' => 0,
            'ctime' => 0,
            'blksize' => -1,
            'blocks' => -1,
        );
    }

    /**
     * Return info about stream
     * @param string $aPath
     * @param array $aOptions
     * @return array
     */
    public function url_stat($aPath, $aOptions) {
        $resource = fopen($aPath, 'r');

        return fstat($resource);
    }
}
