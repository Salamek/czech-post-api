<?php

use Salamek\CzechPostApi\Model\Package;
use Salamek\CzechPostApi\Enum\Product;

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */
final class PackageTest extends BaseTest
{
    /**
     * @test
     * @expectedException \Salamek\CzechPostApi\Exception\WrongDataException
     */
    public function testPackageWrongSeriesNumberId()
    {
        new Package('AHAHA', Product::PACKAGE_TO_HAND, $this->sender, $this->recipient);
    }

    /**
     * @test
     * @expectedException \Salamek\CzechPostApi\Exception\WrongDataException
     */
    public function testPackageWrongProductType()
    {
        new Package(12, 165, $this->sender, $this->recipient);
    }
}