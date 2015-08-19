StringStream
============

Stream wrapper for strings. Basically, like php://temp except that you can have multiple streams
at once, and can pre-initialise the contents.

[![Build Status](https://travis-ci.org/dvdoug/StringStream.png)](https://travis-ci.org/dvdoug/StringStream) 

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

* PHP 5.3 or higher (tested with 5.4/5.5/5.6)

License
-------
StringStream is MIT-licensed. 