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
  class StringStream {

    /**
     * Content of stream
     * @var string
     */
    private $string;

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
     * Open stream
     * @param string $aPath
     * @param string $aMode
     * @param int $aOptions
     * @param string $aOpenedPath
     * @return boolean
     */
    function stream_open($aPath, $aMode, $aOptions, &$aOpenedPath) {
      $this->string = substr($aPath, 9);
      $this->options = $aOptions;

      switch ($aMode) {

        case 'r':
          $this->read = true;
          $this->write = false;
          $this->position = 0;
          break;

        case 'r+':
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
          $this->position = strlen($this->string);
          break;

        case 'a+':
          $this->read = true;
          $this->write = true;
          $this->position = strlen($this->string);
          break;

        case 'c':
          $this->read = false;
          $this->write = true;
          $this->position = 0;
          break;

        case 'c+':
          $this->read = true;
          $this->write = true;
          $this->position = 0;
          break;

        default:
          trigger_error('Invalid mode specified', E_ERROR);
      }


      return true;
    }

    /**
     * Read from stream
     * @param int $aBytes number of bytes to return
     * @return string
     */
    function stream_read($aBytes) {
      $read = substr($this->string, $this->position, $aBytes);
      $this->position += strlen($read);
      return $read;
    }

    /**
     * Write to stream
     * @param string $aData data to write
     * @return string
     */
    function stream_write($aData) {
      $left = substr($this->string, 0, $this->position);
      $right = substr($this->string, $this->position + strlen($aData));
      $this->string = $left . $aData . $right;
      $this->position += strlen($aData);
      return strlen($aData);
    }

    /**
     * Return current position
     * @return int
     */
    function stream_tell() {
      return $this->position;
    }

    /**
     * Return if EOF
     * @return boolean
     */
    function stream_eof() {
      return $this->position >= strlen($this->string);
    }

    /**
     * Seek to new position
     * @param int $aOffset
     * @param int $aWhence
     * @return boolean
     */
    function stream_seek($aOffset, $aWhence) {
      switch ($aWhence) {
        case SEEK_SET:
          if ($aOffset < strlen($this->string) && $aOffset >= 0) {
            $this->position = $aOffset;
            return true;
          }
          else {
            return false;
          }
          break;

        case SEEK_CUR:
          if ($aOffset >= 0) {
             $this->position += $aOffset;
             return true;
          }
          else {
            return false;
          }
          break;

        case SEEK_END:
          if (strlen($this->string) + $aOffset >= 0) {
            $this->position = strlen($this->string) + $aOffset;
            return true;
          }
          else {
            return false;
          }
          break;

        default:
          return false;
      }
    }

    /**
     * Truncate to given size
     * @param int $aSize
     */
    public function stream_truncate($aSize) {
      if (strlen($this->string) > $aSize) {
        $this->string = substr($this->string, 0, $aSize);
      }
      else if (strlen($this->string) < $aSize) {
        $this->string = str_pad($this->string, $aSize, '\0', STR_PAD_RIGHT);
      }
      return true;
    }

    /**
     * Return info about stream
     * @return array
     */
    public function stream_stat() {
      return array('dev'    => 0,
                   'ino'    => 0,
                   'mode'   => 0,
                   'nlink'  => 0,
                   'uid'    => 0,
                   'gid'    => 0,
                   'rdev'   => 0,
                   'size'   => strlen($this->string),
                   'atime'  => 0,
                   'mtime'  => 0,
                   'ctime'  => 0,
                   'blksize' => -1,
                   'blocks'  => -1);
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