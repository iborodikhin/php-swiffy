Usage
=====

1. run ```composer require iborodikhin/php-swiffy```
2. write something like this

```
$swiffy = new Swiffy\Client();

//Returns swiffy HTML file content
$html = $swiffy->convert("my-movie.swf");

//Returns only the swiffy json data
$json = $swiffy->convert("my-movie.swf",true);
```
