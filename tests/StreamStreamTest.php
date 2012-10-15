<?php

  namespace PHPCoord;

  require_once(__DIR__.'/../StringStream.php');

  class test extends \PHPUnit_Framework_TestCase {
    
    public function setUp() {
      stream_wrapper_register('string', '\DVDoug\StringStream\StringStream');
    }
    
    public function testRead() {
      
      $handle = fopen('string://foobar', 'r+');
      $contents = '';
      while (!feof($handle)) {
        $contents .= fread($handle, 8192);
      }
      fclose($handle);
      
      $expected = 'foobar';
      
      self::assertEquals($expected, $contents);
    }
    
  }