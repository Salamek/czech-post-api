<?php

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */
final class ApiTest extends BaseTest
{
    /**
     * @test
     */
    public function testGetRegions()
    {
        $result = $this->czechPostApi->getRegions();

        $this->assertInternalType('array', $result);
        $this->assertNotEmpty($result);
    }

    /**
     * @test
     */
    public function testGetDistricts()
    {
        $result = $this->czechPostApi->getDistricts(12);
        $this->assertInternalType('array', $result);
        $this->assertNotEmpty($result);
    }

    /**
     * @test
     */
    public function testGetCities()
    {
        $result = $this->czechPostApi->getCities(71);
        $this->assertInternalType('array', $result);
        $this->assertNotEmpty($result);
    }

    /**
     * @test
     */
    public function testGetCityParts()
    {
        $result = $this->czechPostApi->getCityParts(3573);
        $this->assertInternalType('array', $result);
        $this->assertNotEmpty($result);
    }

    /**
     * @test
     */
    public function testGetStreets()
    {
        $result = $this->czechPostApi->getStreets(8648);
        $this->assertInternalType('array', $result);
        $this->assertNotEmpty($result);
    }

    /**
     * @test
     */
    public function testGetStreetNumbers()
    {
        $result = $this->czechPostApi->getStreetNumbers(75383);
        $this->assertInternalType('array', $result);
        $this->assertNotEmpty($result);
    }

    /**
     * @test
     * @throws \Salamek\CzechPostApi\Exception\WrongDataException
     */
    public function testGetPackages()
    {
        $result = $this->czechPostApi->getPackages(['NP9567013032C']);
        $this->assertInternalType('array', $result);
        $this->assertNotEmpty($result);
    }

    /**
     * @test
     * @expectedException \Salamek\CzechPostApi\Exception\WrongDataException
     */
    public function testGetPackagesFailToMuchPackages()
    {
        $packages = [
            'NP9567013032C',
            'NP9567013032C',
            'NP9567013032C',
            'NP9567013032C',
            'NP9567013032C',
            'NP9567013032C',
            'NP9567013032C',
            'NP9567013032C',
            'NP9567013032C',
            'NP9567013032C',
            'NP9567013032C',
            'NP9567013032C',
            'NP9567013032C',
            'NP9567013032C',
            'NP9567013032C',
        ];
        $this->czechPostApi->getPackages($packages);
    }

    /**
     * @test
     */
    public function testFindPostCodes()
    {
        $result = $this->czechPostApi->findPostCodes(null, null, null, null, null, 74901);
        $this->assertInternalType('array', $result);
        $this->assertNotEmpty($result);
    }

    /**
     * @test
     * @expectedException \Salamek\CzechPostApi\Exception\WrongDataException
     */
    public function testFindPostCodesFailNoParameters()
    {
        $result = $this->czechPostApi->findPostCodes();
        $this->assertInternalType('array', $result);
        $this->assertNotEmpty($result);
    }

    /**
     * @test
     */
    public function testGetPostOfficeInformations()
    {
        $result = $this->czechPostApi->getPostOfficeInformations(71);
        $this->assertInternalType('array', $result);
        $this->assertNotEmpty($result);
    }

    /**
     * @test
     */
    public function testGetLastUpdate()
    {
        $result = $this->czechPostApi->getLastUpdate(new \DateTime());
        $this->assertInternalType('array', $result);
        $this->assertNotEmpty($result);
    }

    /**
     * @test
     */
    public function testIsAfternoonParcelDelivery()
    {
        $result = $this->czechPostApi->isAfternoonParcelDelivery(71);
        $this->assertInternalType('bool', $result);
    }


}