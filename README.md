# Czech post API client in PHP (Parsing podani online)

[![Build Status](https://travis-ci.org/Salamek/czech-post-api.svg?branch=master)](https://travis-ci.org/Salamek/czech-post-api)
[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=D8LQ4XTBLV3C4&lc=CZ&item_number=Salamekczech-post-api&currency_code=EUR)

Czech post API client in PHP (Parsing podani online)


## Requirements

- PHP 5.4 or higher

## Installation

Install salamek/czech-post-api using  [Composer](http://getcomposer.org/)

```sh
$ composer require salamek/czech-post-api
```

or if you want master branch code:

```sh
$ composer require salamek/czech-post-api:dev-master
```

## Usage

### Create Packages

Creates package/s on PPL MyApi (sends Package object to PPL)

```php

$username = 'my_api_username';
$password = 'my_api_password';
$cookieJar = __DIR__.'/cookieJar.txt';

$czechPostApi = new Salamek\CzechPostApi\Api($username, $password, $cookieJar);

$sender = new Salamek\CzechPostApi\Model\Sender('Olomouc', 'My Compamy s.r.o.', 'My Address', '77900', 'info@example.com', '+420123456789', 'http://www.example.cz', Country::CZ);
$recipient = new Salamek\CzechPostApi\Model\Recipient('Olomouc', 'Adam Schubert', 'My Address', '77900', 'adam@example.com', '+420123456789', 'http://www.salamek.cz', Country::CZ, 'My Compamy a.s.');

$myPackageIdFromNumberSeries = 115;
$weight = 3.15;
$insurance = 100;
$package = new Salamek\CzechPostApi\Model\Package($myPackageIdFromNumberSeries, Product::PACKAGE_TO_HAND, $sender, $recipient, null, null, $insurance, [], 'Package desc', 1, 1, null);

try
{
    $czechPostApi->createPackages([$package]);
}
catch (\Exception $e)
{
    echo $e->getMessage() . PHP_EOL;
}

```

### Get Labels

Returns PDF with label/s for print on paper, two decompositions are supported, LabelDecomposition::FULL (one A4 Label per page) or LabelDecomposition::QUARTER (one label per 1/4 of A4 page)

```php

$sender = new Salamek\CzechPostApi\Model\Sender('Olomouc', 'My Compamy s.r.o.', 'My Address', '77900', 'info@example.com', '+420123456789', 'http://www.example.cz', Country::CZ);
$recipient = new Salamek\CzechPostApi\Model\Recipient('Olomouc', 'Adam Schubert', 'My Address', '77900', 'adam@example.com', '+420123456789', 'http://www.salamek.cz', Country::CZ, 'My Compamy a.s.');

$myPackageIdFromNumberSeries = 115;
$weight = 3.15;
$insurance = 100;
$package = new Salamek\CzechPostApi\Model\Package($myPackageIdFromNumberSeries, Product::PACKAGE_TO_HAND, $sender, $recipient, null, null, $insurance, [], 'Package desc', 1, 1, null);


$rawPdf = Label::generateLabels([$package]);
file_put_contents($package->getPackageNumber() . '.pdf', $rawPdf);
```
