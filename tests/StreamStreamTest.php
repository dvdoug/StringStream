<?php

  require_once(__DIR__.'/../StringStream.php');

  class test extends \PHPUnit_Framework_TestCase {
    
    public static function setUpBeforeClass() {
      stream_wrapper_register('string', '\DVDoug\StringStream\StringStream');
    }
    
    public function testReadOnly() {
      $handle = fopen('string://foobar', 'r');
      
      self::assertEquals('foobar', stream_get_contents($handle, -1, 0));
      self::assertEquals('foo', stream_get_contents($handle, 3, 0));
      self::assertEquals('bar', stream_get_contents($handle, -1, 3));
      
      fwrite($handle, 'bar');
      
      self::assertEquals('foobar', stream_get_contents($handle, -1, 0));
      self::assertEquals('foo', stream_get_contents($handle, 3, 0));
      self::assertEquals('bar', stream_get_contents($handle, -1, 3));

      fclose($handle);
    }
    
    public function testReadWriteNoTruncate() {
      $handle = fopen('string://foobar', 'r+');
     
      fwrite($handle, 'bar');
      
      self::assertEquals('barbar', stream_get_contents($handle, -1, 0));
      self::assertEquals('bar', stream_get_contents($handle, 3, 0));
      self::assertEquals('bar', stream_get_contents($handle, -1, 3));

      fclose($handle);
    }
    
    public function testWriteOnlyTruncate() {
      $handle = fopen('string://foobar', 'w');
      
      self::assertEquals(0, ftell($handle));
      
      self::assertEquals('', stream_get_contents($handle, -1, 0));
      
      fwrite($handle, 'bar');
      
      self::assertEquals(3, ftell($handle));

      fclose($handle);
    }
    
    public function testReadWriteTruncate() {
      $handle = fopen('string://foobar', 'w+');
      self::assertEquals(0, ftell($handle));
      
      fwrite($handle, 'bar');
      
      self::assertEquals('bar', stream_get_contents($handle, -1, 0));
      self::assertEquals('bar', stream_get_contents($handle, 3, 0));
      self::assertEquals('', stream_get_contents($handle, -1, 3));

      fclose($handle);
    }
    
    public function testWriteOnlyAppend() {
      $handle = fopen('string://foobar', 'a');
      self::assertEquals(6, ftell($handle));
      
      fwrite($handle, 'bar');
      
      self::assertEquals(9, ftell($handle));

      fclose($handle);
    }
    
    public function testReadWriteAppend() {
      $handle = fopen('string://foobar', 'a+');
      self::assertEquals('foobar', stream_get_contents($handle, -1, 0));
      self::assertEquals('foo', stream_get_contents($handle, 3, 0));
      self::assertEquals('bar', stream_get_contents($handle, -1, 3));
      
      fwrite($handle, 'bar');
      
      self::assertEquals('foobarbar', stream_get_contents($handle, -1, 0));
      self::assertEquals('foo', stream_get_contents($handle, 3, 0));
      self::assertEquals('barbar', stream_get_contents($handle, -1, 3));

      fclose($handle);
    }
    
    public function testWriteOnlyNoTruncate() {
      $handle = fopen('string://foobar', 'c');
      self::assertEquals(0, ftell($handle));
      
      fwrite($handle, 'bar');
      
      self::assertEquals(3, ftell($handle));
      
      fclose($handle);
    }
    
    public function testReadWriteNoTruncate2() {
      $handle = fopen('string://foobar', 'c+');
     
      fwrite($handle, 'bar');
      
      self::assertEquals('barbar', stream_get_contents($handle, -1, 0));
      self::assertEquals('bar', stream_get_contents($handle, 3, 0));
      self::assertEquals('bar', stream_get_contents($handle, -1, 3));

      fclose($handle);
    }
    
    /**
     * @requires PHP 5.4
     */
    public function testTruncateExtension() {
    
      $handle = fopen('string://foobar', 'r+');
      ftruncate($handle, 9);

      self::assertEquals("foobar\0\0\0", stream_get_contents($handle, -1, 0));
      
      fclose($handle);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testWriteExclusive() {
      $handle = fopen('string://foobar', 'x+');
    }
    
    public function testSeekCurPastEnd() {
      $handle = fopen('string://foobar', 'r+');
      $ok = fseek($handle, 8, SEEK_CUR);
      self::assertEquals("foobar\0\0", stream_get_contents($handle, -1, 0));
    
      fclose($handle);
    }
    
    public function testSeekSetPastEnd() {
      $handle = fopen('string://foobar', 'r+');
      $ok = fseek($handle, 7, SEEK_SET);
      
      self::assertEquals(0, $ok);
      self::assertEquals("foobar\0", stream_get_contents($handle, -1, 0));
      
      fclose($handle);
    }
    
    public function testSeekEndAfterBeginning() {
      $handle = fopen('string://foobar', 'r+');
      $ok = fseek($handle, -5, SEEK_END);
      self::assertEquals(0, $ok);
      self::assertEquals(1, ftell($handle));
      
      self::assertEquals("oobar", stream_get_contents($handle, -1));
    
      fclose($handle);
    }
    
    public function testSeekingAndWritingPastEnd() {
      $handle = fopen('string://foobar', 'r+');
      fseek($handle, 3, SEEK_END);
      fwrite($handle, 'foo');
    
      self::assertEquals("foobar\0\0\0foo", stream_get_contents($handle, -1, 0));
    
      fclose($handle);
    }
    
    public function testUnknownSeekMethod() {
      $handle = fopen('string://foobar', 'r+');
      $ok = fseek($handle, 3, PHP_INT_MAX);
    
      self::assertEquals(-1, $ok);
    
      fclose($handle);
    }
    
    public function testStat() {
      $stat = stat('string://foobar');

      $expected = array ('dev' => 0,
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
                         'blksize' => - 1,
                         'blocks' => - 1);
      
      foreach ($expected as $key => $value) {
         self::assertEquals($value, $stat[$key]);
      }

    }
  
  }