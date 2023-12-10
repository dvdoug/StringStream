StringStream
============

Stream wrapper for strings. Basically, like php://temp ~~except that you can have multiple streams
at once, and can pre-initialise the contents~~. This was never tested by my past self, php://temp is in fact not shared
between handles and this package was never needed. Just use php://temp

[![Build Status](https://travis-ci.org/dvdoug/StringStream.svg?branch=master)](https://travis-ci.org/dvdoug/StringStream) 
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/dvdoug/StringStream/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/dvdoug/StringStream/?branch=master)
[![Download count](https://img.shields.io/packagist/dt/dvdoug/stringstream.svg)](https://packagist.org/packages/dvdoug/stringstream)
[![Download count](https://img.shields.io/packagist/v/dvdoug/stringstream.svg)](https://packagist.org/packages/dvdoug/stringstream)

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

License
-------
StringStream is MIT-licensed. 
