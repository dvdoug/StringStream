<?php
/**
 * Stream wrapper for strings.
 * @author Doug Wright
 */

declare(strict_types=1);

namespace DVDoug\StringStream;

use function fopen;
use function substr;
use function strlen;

class StringStream
{
    /**
     * Content of stream.
     * @var string
     */
    private $string;

    /**
     * Whether this stream can be read.
     * @var bool
     */
    private $read;

    /**
     * Whether this stream can be written.
     * @var bool
     */
    private $write;

    /**
     * Whether this stream should have UNIX-style newlines converted to Windows-style.
     * @var bool
     */
    private $normaliseForWin = false;

    /**
     * Options.
     * @var int
     */
    private $options;

    /**
     * Current position within stream.
     * @var int
     */
    private $position;

    /**
     * Context.
     *
     * @var resource
     */
    public $context;

    /**
     * Open stream.
     * @param string $aPath
     * @param string $aMode
     * @param int    $aOptions
     * @param string $aOpenedPath
     *
     * @return bool
     */
    public function stream_open($aPath, $aMode, $aOptions, &$aOpenedPath): bool
    {
        $this->string = substr($aPath, strpos($aPath, '://') + 3);
        $this->options = $aOptions;

        if (strpos($aMode, 't') && defined('PHP_WINDOWS_VERSION_MAJOR')) {
            $this->normaliseForWin = true;
            $this->string = preg_replace('/(?<!\r)\n/', "\r\n", $this->string);
        }

        // strip binary/text flags from mode for comparison
        $mode = strtr($aMode, ['b' => '', 't' => '']);

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
     * Read from stream.
     *
     * @param int $aBytes number of bytes to return
     *
     * @return int|false
     */
    public function stream_read($aBytes)
    {
        if ($this->read) {
            $read = substr($this->string, $this->position, $aBytes);
            $this->position += strlen($read);

            return $read;
        } else {
            return false;
        }
    }

    /**
     * Write to stream.
     *
     * @param string $aData data to write
     *
     * @return int
     */
    public function stream_write($aData): int
    {
        if ($this->normaliseForWin) {
            $data = preg_replace('/(?<!\r)\n/', "\r\n", $aData);
        } else {
            $data = $aData;
        }

        if ($this->write) {
            $left = substr($this->string, 0, $this->position);
            $right = substr($this->string, $this->position + strlen($data));
            $this->string = $left . $data . $right;
            $this->position += strlen($data);

            return strlen($data);
        } else {
            return 0;
        }
    }

    /**
     * Return current position.
     *
     * @return int
     */
    public function stream_tell(): int
    {
        return $this->position;
    }

    /**
     * Return if EOF.
     *
     * @return bool
     */
    public function stream_eof(): bool
    {
        return $this->position >= strlen($this->string);
    }

    /**
     * Seek to new position.
     *
     * @param int $aOffset
     * @param int $aWhence
     *
     * @return bool
     */
    public function stream_seek($aOffset, $aWhence): bool
    {
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
                $this->position = strlen($this->string) + $aOffset;
                $this->truncate_after_seek();

                return true;
                break;

            default:
                return false;
        }
    }

    /**
     * Truncate to given size.
     *
     * @param int $aSize
     *
     * @return bool
     */
    public function stream_truncate($aSize): bool
    {
        if (strlen($this->string) > $aSize) {
            $this->string = substr($this->string, 0, $aSize);
        } elseif (strlen($this->string) < $aSize) {
            $this->string = str_pad($this->string, $aSize, "\0", STR_PAD_RIGHT);
        }

        return true;
    }

    /**
     * Return info about stream.
     *
     * @return array
     */
    public function stream_stat(): array
    {
        return [
            'dev' => 0,
            'ino' => 0,
            'mode' => 0,
            'nlink' => 0,
            'uid' => 0,
            'gid' => 0,
            'rdev' => 0,
            'size' => strlen($this->string),
            'atime' => 0,
            'mtime' => 0,
            'ctime' => 0,
            'blksize' => -1,
            'blocks' => -1,
        ];
    }

    /**
     * Return info about stream.
     *
     * @param string $aPath
     * @param array  $aOptions
     *
     * @return array
     */
    public function url_stat($aPath, $aOptions): array
    {
        $resource = fopen($aPath, 'rb');

        return fstat($resource);
    }

    /**
     * If we've seeked past the end of file, auto truncate.
     */
    protected function truncate_after_seek(): void
    {
        if ($this->position > strlen($this->string)) {
            $this->stream_truncate($this->position);
        }
    }
}
