<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\CzechPostApi;

use Salamek\CzechPostApi\Enum\Product;

/**
 * Class Data
 * @package Salamek
 */
class Data implements IData
{
    /** @var null|\PDO  */
    private $database = null;

    private $deliveryTypeTable = [
        Product::PACKAGE_TO_HAND => 'zv_pcobc',
        Product::PACKAGE_TO_THE_POST_OFFICE => 'zv_np_dodani'
    ];

    /**
     * CzechPostData constructor.
     */
    public function __construct()
    {
        $this->database = new \PDO('sqlite:' . __DIR__ . '/../data/czechPostData.db');
        $this->database->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * @param string $deliveryType
     * @return array
     */
    public function getZipCodes($deliveryType = Product::PACKAGE_TO_HAND)
    {
        $query = $this->database->prepare('SELECT zipCode FROM '.$this->deliveryTypeTable[$deliveryType]);
        return $query->fetchAll();
    }

    /**
     * @param $zipCode
     * @param null|string $deliveryType
     * @return mixed
     */
    public function findZipCode($zipCode, $deliveryType = null)
    {
        if ($deliveryType == Product::PACKAGE_TO_HAND || $deliveryType == Product::PACKAGE_TO_THE_POST_OFFICE)
        {
            $query = $this->database->prepare('SELECT zipCode FROM '.$this->deliveryTypeTable[$deliveryType].' WHERE zipCode = ?');
            $query->execute([$zipCode]);
            return $query->fetch(\PDO::FETCH_ASSOC);
        }
        else
        {
            $query = $this->database->prepare('SELECT zipCode FROM '.$this->deliveryTypeTable[Product::PACKAGE_TO_HAND].' WHERE zipCode = ?');
            $query->execute([$zipCode]);
            $found = $query->fetch(\PDO::FETCH_ASSOC);

            if ($found)
            {
                return $found;
            }

            $query = $this->database->prepare('SELECT zipCode FROM '.$this->deliveryTypeTable[Product::PACKAGE_TO_THE_POST_OFFICE].' WHERE zipCode = ?');
            $query->execute([$zipCode]);
            $found = $query->fetch(\PDO::FETCH_ASSOC);

            if ($found)
            {
                return $found;
            }
        }

        return null;
    }

    /**
     * @param $city
     * @return mixed
     */
    public function findCity($city)
    {
        $query = $this->database->prepare('SELECT nazevCObce AS city FROM '.$this->deliveryTypeTable[Product::PACKAGE_TO_HAND].' WHERE lower(nazevCObce) LIKE lower(?) OR lower(nazevObce) LIKE lower(?)');
        $query->execute(['%'.$city.'%', '%'.$city.'%']);

        return $query->fetch(\PDO::FETCH_ASSOC);
    }
}