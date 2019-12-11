# upyx/php-time

[![Source Code][badge-source]][source]
[![Latest Version][badge-release]][packagist]
[![Software License][badge-license]][license]
[![PHP Version][badge-php]][php]
[![Build Status][badge-build]][build]
[![Coverage Status][badge-coverage]][coverage]
[![Total Downloads][badge-downloads]][downloads]

Object oriented representation of time with microseconds. It like o'clock time
without date, timezone or something else but cyclical for 24 hours.


## Why not DateTime/DateTimeImmutable from standard library?

It is for different purposes. DateTime uses for calendar (linear) time through
ages. But this library is intended to represent periodical time day by day.

Let's look closer. Noon in Tokyo and noon in New York are different times in
the world. Noon on the 1st of December and noon on the 2nd December are
different times. But noon is still 12 **o'clock**.

It is still possible to use DateTime for o'clock time, but it has some
disadvantages. Firstly, it is curly because it needs some "zero-date".
Secondary, it is difficult to compare and calculate because of changing dates.
Thirdly, it is buggy because of timezone conversions. Fourthly, it confuses
with the DateTime type used as calendar time.


## Installation

The preferred method of installation is via [Composer][]. Run the following
command to install the package and add it as a requirement to your project's
`composer.json`:

```bash
composer require upyx/php-time
```


## Usage

LocalTime can be created in three ways:

```php
use Upyx\PhpTime\LocalTime;
$time1 = new LocalTime(10, 20, 30, 40000);
$time2 = LocalTime::fromDateTime(new DateTimeImmutable('10:20:30'));
$time3 = LocalTime::fromMicroseconds(3600000000);
```

There are "add" and "subtract" methods:

```php
use Upyx\PhpTime\LocalTime;
$time1 = new LocalTime(10, 0);
$time2 = new LocalTime(13, 0);
var_dump($time1->cyclicAdd($time2)); // 23:00:00.000000
var_dump($time2->cyclicSubtract($time1)); // 03:00:00.000000
var_dump($time1->cyclicSubtract($time2)); // 21:00:00.000000
```

As time is periodical, there are two distances between two times in
both directions. There is a method to calculate the smallest distance
between ones:

```php
use Upyx\PhpTime\LocalTime;
$time1 = new LocalTime(2, 0);
$time2 = new LocalTime(12, 0);
$time3 = new LocalTime(22, 0);
var_dump($time1->calcDistance($time2)); // 10:00:00.000000
var_dump($time2->calcDistance($time3)); // 10:00:00.000000
var_dump($time1->calcDistance($time3)); // 04:00:00.000000
```

It is possible to use comparison operators.
The smallest value is "00:00:00.000000", the greatest one is "23:59:59.999999".

```php
use Upyx\PhpTime\LocalTime;
$time1 = new LocalTime(10, 0);
$time2 = new LocalTime(13, 0);
var_dump($time1 < $time2); // true
var_dump($time1 <= $time2); // true
var_dump($time1 > $time2); // false
var_dump($time1 >= $time2); // false
var_dump($time1 <=> $time2); // -1
```


## Contributing

If you have a question, feel free to create an issue. If you would like to send
me a pull request, please create an issue first.


## Copyright and License

The upyx/php-time library is copyright © Sergey Rabochiy
and licensed for use under the MIT License (MIT). Please see [LICENSE][] for
more information.


[composer]: https://getcomposer.org/

[badge-source]: https://img.shields.io/badge/source-upyx/php--time-blue.svg?style=flat
[badge-release]: https://img.shields.io/packagist/v/upyx/php-time.svg?style=flat&label=release
[badge-license]: https://img.shields.io/packagist/l/upyx/php-time.svg?style=flat
[badge-php]: https://img.shields.io/packagist/php-v/upyx/php-time.svg?style=flat
[badge-build]: https://img.shields.io/travis/upyx/php-time/master.svg?style=flat
[badge-coverage]: https://img.shields.io/coveralls/github/upyx/php-time/master.svg?style=flat
[badge-downloads]: https://img.shields.io/packagist/dt/upyx/php-time.svg?style=flat&colorB=mediumvioletred

[source]: https://github.com/upyx/php-time
[packagist]: https://packagist.org/packages/upyx/php-time
[license]: https://github.com/upyx/php-time/blob/master/LICENSE
[php]: https://php.net
[build]: https://travis-ci.org/upyx/php-time
[coverage]: https://coveralls.io/r/upyx/php-time?branch=master
[downloads]: https://packagist.org/packages/upyx/php-time
