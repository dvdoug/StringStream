StringStream
============

Stream wrapper for strings. Basically, like php://temp except that you can have multiple streams
at once, and can pre-initialise the contents.

[![Build Status](https://travis-ci.org/dvdoug/StringStream.svg?branch=master)](https://travis-ci.org/dvdoug/StringStream) 
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/dvdoug/StringStream/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/dvdoug/StringStream/?branch=master)
[![Download count](https://img.shields.io/packagist/dt/dvdoug/StringStream.svg)](https://packagist.org/packages/dvdoug/StringStream)
[![Download count](https://img.shields.io/packagist/v/dvdoug/StringStream.svg)](https://packagist.org/packages/dvdoug/StringStream)

Usage
-----
```php
stream_wrapper_register('string', '\DVDoug\StringStream\StringStream');

$handle = fopen('string://foobar', 'r+');
$contents = '';
while (!feof($handle)) {
  $contents .= fread($handle, 8192);
}
fclose($handle);

```



Requirements
------------

* PHP 5.3 or higher (including PHP7 and HHVM)

License
-------
StringStream is MIT-licensed. 
