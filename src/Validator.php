<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\CzechPostApi;

use Salamek\CzechPostApi\Enum\Product;

/**
 * Class Validator
 * @package Salamek
 */
class Validator
{
    /** @var Data */
    private $czechPostData;

    /**
     * Validator constructor.
     */
    public function __construct()
    {
        $this->czechPostData = new Data();
    }

    /**
     * @param $zipCode
     * @return bool
     */
    public function validateZipCode($zipCode)
    {
        $found = $this->czechPostData->findZipCode($zipCode);
        if (!$found)
        {
            return false;
        }

        return true;
    }

    /**
     * @param $zipCode
     * @param string $deliveryType
     * @return bool
     */
    public function validateZipCodeDelivery($zipCode, $deliveryType = Product::PACKAGE_TO_HAND)
    {
        $found = $this->czechPostData->findZipCode($zipCode, $deliveryType);
        if (!$found)
        {
            return false;
        }
        return true;
    }

    /**
     * @param $city
     * @return bool
     */
    public function validateCity($city)
    {
        $found = $this->czechPostData->findCity($city);
        if (!$found)
        {
            return false;
        }

        return true;
    }

    /**
     * @param $phone
     * @return bool
     */
    public function validatePhone($phone)
    {
        if (preg_match('/^(\+420)? ?[0-9]{3} ?[0-9]{3} ?[0-9]{3}$/i', $phone))
        {
            return true;
        }

        return false;
    }

}