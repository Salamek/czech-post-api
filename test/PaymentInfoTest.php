<?php

use Salamek\CzechPostApi\Model\PaymentInfo;
use Salamek\CzechPostApi\Enum\Currency;

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */
final class PaymentInfoTest extends BaseTest
{
    /**
     * @test
     * @expectedException \Salamek\CzechPostApi\Exception\WrongDataException
     */
    public function testWrongInsuranceCurrency()
    {
        new PaymentInfo(10, Currency::CZK, '4458458', 20, 'ABC');
    }

    /**
     * @test
     * @expectedException \Salamek\CzechPostApi\Exception\WrongDataException
     */
    public function testWrongCashOnDeliveryCurrency()
    {
        new PaymentInfo(10, 'ABC', '4458458');
    }
}