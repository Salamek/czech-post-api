<?php

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\CzechPostApi;

use Salamek\CzechPostApi\Enum\Product;

/**
 * Interface ICzechPostData
 * @package Salamek
 */
interface IData
{
    /**
     * @param string $deliveryType
     * @return mixed
     */
    public function getZipCodes($deliveryType = Product::PACKAGE_TO_HAND);

    /**
     * @param $zipCode
     * @param null $deliveryType
     * @return mixed
     */
    public function findZipCode($zipCode, $deliveryType = null);

    /**
     * @param $city
     * @return mixed
     */
    public function findCity($city);
}