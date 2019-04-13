<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class StringStreamTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        stream_wrapper_register('string', '\DVDoug\StringStream\StringStream');
    }

    public function testReadOnly(): void
    {
        $handle = fopen('string://foobar', 'rb');

        self::assertEquals('foobar', stream_get_contents($handle, -1, 0));
        self::assertEquals('foo', stream_get_contents($handle, 3, 0));
        self::assertEquals('bar', stream_get_contents($handle, -1, 3));

        fwrite($handle, 'bar');

        self::assertEquals('foobar', stream_get_contents($handle, -1, 0));
        self::assertEquals('foo', stream_get_contents($handle, 3, 0));
        self::assertEquals('bar', stream_get_contents($handle, -1, 3));

        fclose($handle);
    }

    public function testReadWriteNoTruncate(): void
    {
        $handle = fopen('string://foobar', 'r+b');

        fwrite($handle, 'bar');

        self::assertEquals('barbar', stream_get_contents($handle, -1, 0));
        self::assertEquals('bar', stream_get_contents($handle, 3, 0));
        self::assertEquals('bar', stream_get_contents($handle, -1, 3));

        fclose($handle);
    }

    public function testWriteOnlyTruncate(): void
    {
        $handle = fopen('string://foobar', 'wb');

        self::assertEquals(0, ftell($handle));

        self::assertEquals('', stream_get_contents($handle, -1, 0));

        fwrite($handle, 'bar');

        self::assertEquals(3, ftell($handle));

        fclose($handle);
    }

    public function testReadWriteTruncate(): void
    {
        $handle = fopen('string://foobar', 'w+b');
        self::assertEquals(0, ftell($handle));

        fwrite($handle, 'bar');

        self::assertEquals('bar', stream_get_contents($handle, -1, 0));
        self::assertEquals('bar', stream_get_contents($handle, 3, 0));
        self::assertEquals('', stream_get_contents($handle, -1, 3));

        fclose($handle);
    }

    public function testWriteOnlyAppend(): void
    {
        $handle = fopen('string://foobar', 'ab');
        self::assertEquals(6, ftell($handle));

        fwrite($handle, 'bar');

        self::assertEquals(9, ftell($handle));

        fclose($handle);
    }

    public function testReadWriteAppend(): void
    {
        $handle = fopen('string://foobar', 'a+b');
        self::assertEquals('foobar', stream_get_contents($handle, -1, 0));
        self::assertEquals('foo', stream_get_contents($handle, 3, 0));
        self::assertEquals('bar', stream_get_contents($handle, -1, 3));

        fwrite($handle, 'bar');

        self::assertEquals('foobarbar', stream_get_contents($handle, -1, 0));
        self::assertEquals('foo', stream_get_contents($handle, 3, 0));
        self::assertEquals('barbar', stream_get_contents($handle, -1, 3));

        fclose($handle);
    }

    public function testWriteOnlyNoTruncate(): void
    {
        $handle = fopen('string://foobar', 'cb');
        self::assertEquals(0, ftell($handle));

        fwrite($handle, 'bar');

        self::assertEquals(3, ftell($handle));

        fclose($handle);
    }

    public function testReadWriteNoTruncate2(): void
    {
        $handle = fopen('string://foobar', 'c+b');

        fwrite($handle, 'bar');

        self::assertEquals('barbar', stream_get_contents($handle, -1, 0));
        self::assertEquals('bar', stream_get_contents($handle, 3, 0));
        self::assertEquals('bar', stream_get_contents($handle, -1, 3));

        fclose($handle);
    }

    public function testTruncateExtension(): void
    {
        $handle = fopen('string://foobar', 'r+b');
        ftruncate($handle, 9);

        self::assertEquals("foobar\0\0\0", stream_get_contents($handle, -1, 0));

        fclose($handle);
    }

    public function testSeekCurPastEnd(): void
    {
        $handle = fopen('string://foobar', 'r+b');
        $ok = fseek($handle, 8, SEEK_CUR);
        self::assertEquals("foobar\0\0", stream_get_contents($handle, -1, 0));

        fclose($handle);
    }

    public function testSeekSetPastEnd(): void
    {
        $handle = fopen('string://foobar', 'r+b');
        $ok = fseek($handle, 7, SEEK_SET);

        self::assertEquals(0, $ok);
        self::assertEquals("foobar\0", stream_get_contents($handle, -1, 0));

        fclose($handle);
    }

    public function testSeekEndAfterBeginning(): void
    {
        $handle = fopen('string://foobar', 'r+b');
        $ok = fseek($handle, -5, SEEK_END);
        self::assertEquals(0, $ok);
        self::assertEquals(1, ftell($handle));

        self::assertEquals('oobar', stream_get_contents($handle, -1));

        fclose($handle);
    }

    public function testSeekingAndWritingPastEnd(): void
    {
        $handle = fopen('string://foobar', 'r+b');
        fseek($handle, 3, SEEK_END);
        fwrite($handle, 'foo');

        self::assertEquals("foobar\0\0\0foo", stream_get_contents($handle, -1, 0));

        fclose($handle);
    }

    public function testUnknownSeekMethod(): void
    {
        $handle = fopen('string://foobar', 'r+b');
        $ok = fseek($handle, 3, PHP_INT_MAX);

        self::assertEquals(-1, $ok);

        fclose($handle);
    }

    public function testStat(): void
    {
        $stat = stat('string://foobar');

        $expected = [
            'dev' => 0,
            'ino' => 0,
            'mode' => 0,
            'nlink' => 0,
            'uid' => 0,
            'gid' => 0,
            'rdev' => 0,
            'size' => 6,
            'atime' => 0,
            'mtime' => 0,
            'ctime' => 0,
            'blksize' => -1,
            'blocks' => -1,
        ];

        foreach ($expected as $key => $value) {
            self::assertEquals($value, $stat[$key]);
        }
    }

    public function testOtherWrapperName(): void
    {
        stream_wrapper_register('mystring', '\DVDoug\StringStream\StringStream');

        $handle = fopen('mystring://foobar', 'rb');

        self::assertEquals('foobar', stream_get_contents($handle));

        stream_wrapper_unregister('mystring');
    }

    public function testBinaryAndTextFlags(): void
    {
        $binaryHandle = fopen("string://foobar\n", 'rb');
        $textHandle = fopen("string://foobar\n", 'rt');

        self::assertEquals(7, strlen(stream_get_contents($binaryHandle)));
        self::assertEquals(6 + strlen(PHP_EOL), strlen(stream_get_contents($textHandle)));
    }
}
