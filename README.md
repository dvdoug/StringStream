StringStream
============

Stream wrapper for strings. Basically, like php://temp except that you can have multiple streams
at once, and can pre-initialise the contents. 

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

* PHP 5.3 or higher

License
-------
StringStream is MIT-licensed. 