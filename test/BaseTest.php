<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

use Salamek\CzechPostApi\Api;
use Salamek\CzechPostApi\Model\Package;
use Salamek\CzechPostApi\Model\Sender;
use Salamek\CzechPostApi\Enum\Product;
use Salamek\CzechPostApi\Enum\Country;
use Salamek\CzechPostApi\Model\Recipient;
use Salamek\CzechPostApi\Model\PaymentInfo;
use Salamek\CzechPostApi\Enum\Currency;
use Salamek\CzechPostApi\Model\WeightedPackageInfo;
use Salamek\CzechPostApi\Enum\PackageService;

abstract class BaseTest extends PHPUnit_Framework_TestCase
{
    /** @var null|Api */
    public $czechPostApi = null;

    /** @var null|Package */
    public $package = null;

    /** @var Sender */
    public $sender;

    /** @var Recipient */
    public $recipient;

    /** @var Package[] */
    public $packages = [];

    public function setUp()
    {
        $this->czechPostApi = new Api('username', 'password', __DIR__.'/tmp/cookiejar.txt');

        
        //!FIXME make Sender and Recipient ~same
        $sender = new Sender(9567, 'Dominik', 'Pichal', 'CALBUCO s.r.o.', 'http://www.grizly.cz', 'Větrná', '378/6', '78336', 'Křelov-Břuchotín', 'Křelov', Country::CZ, '77072');
        $recipient = new Recipient('Adam', 'Schubert', 'Brnenska', '54', 'Olomouc', 'Olomouc', '77900', 'Dead Raven s.r.o.', '123456', null, Country::CZ, 'adam.schubert@sg1-game.net', '777978331', 'https://www.salamek.cz');
        $paymentInfo = new PaymentInfo(500, Currency::CZK, 'VAR1234');
        $weighedInfo = new WeightedPackageInfo(10);

        $this->sender = $sender;
        $this->recipient = $recipient;

        //!FIXME goodsPrice to PaymentInfo inssurancePrice ???
        $this->package = new Package(114, Product::PACKAGE_TO_HAND, $sender, $recipient, $paymentInfo, $weighedInfo, '500', [], 'testovaci balik1');

        $this->packages[] = new Package(114, Product::PACKAGE_TO_HAND, $sender, $recipient, $paymentInfo, null, '500', [], 'testovaci balik2');
        $this->packages[] = new Package(115, Product::PACKAGE_TO_HAND, $sender, $recipient, $paymentInfo, null, '500', [], 'testovaci balik3');
        $this->packages[] = new Package(116, Product::PACKAGE_TO_HAND, $sender, $recipient, $paymentInfo, null, '500', [], 'testovaci balik4');
        $this->packages[] = new Package(117, Product::PACKAGE_TO_HAND, $sender, $recipient, $paymentInfo, $weighedInfo, '500', [], 'testovaci balik5');
        $this->packages[] = new Package(118, Product::PACKAGE_TO_HAND, $sender, $recipient, $paymentInfo, null, '500', [], 'testovaci balik6');
        $this->packages[] = new Package(119, Product::PACKAGE_TO_HAND, $sender, $recipient, $paymentInfo, null, '500', [], 'testovaci balik7');
        $this->packages[] = new Package(120, Product::PACKAGE_TO_HAND, $sender, $recipient, $paymentInfo, null, '500', [PackageService::DO_RUKOU_DORUCIT_ODPOLEDNE], 'testovaci balik8');
        $this->packages[] = new Package(121, Product::PACKAGE_TO_HAND, $sender, $recipient, $paymentInfo, null, '500', [PackageService::DO_RUKOU_DORUCIT_DOPOLEDNE], 'testovaci balik9');

        $paymentInfoFirst = new PaymentInfo(4000, Currency::CZK, '123456');
        $packageFirst = new Package(122, Product::PACKAGE_TO_HAND, $sender, $recipient, $paymentInfoFirst, null, '500', [], 'testovaci balik10');
        $packageFirst->setPackageCount(2);
        $packageFirst->setPackagePosition(1);

        $paymentInfoSecond = new PaymentInfo(0, Currency::CZK, '123456');
        $packageSecond = new Package(123, Product::PACKAGE_TO_HAND, $sender, $recipient, $paymentInfoSecond, null, '500', [], 'testovaci balik11');
        $packageSecond->setPackageCount(2);
        $packageSecond->setPackagePosition(2);
        $packageSecond->setParentPackageNumber($packageFirst->getSeriesNumberId());

        $this->packages[] = $packageFirst;
        $this->packages[] = $packageSecond;
    }
}
