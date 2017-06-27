<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\CzechPostApi\Model;


use Salamek\CzechPostApi\Enum\Country;
use Salamek\CzechPostApi\Enum\SenderType;
use Salamek\CzechPostApi\Exception\WrongDataException;

class Sender
{
    /** @var integer */
    private $id;

    /** @var string */
    private $firstName;

    /** @var string */
    private $lastName;

    /** @var  string */
    private $company;

    /** @var  string */
    private $www;

    /** @var  string */
    private $street;

    /** @var  string */
    private $streetNumber;

    /** @var  string */
    private $zipCode;

    /** @var  string */
    private $city;

    /** @var  string */
    private $cityPart;

    /** @var null|string */
    private $country;

    /** @var  string */
    private $postOfficeZipCode;

    /** @var  integer */
    private $type;

    /**
     * Sender constructor.
     * @param $id
     * @param $firstName
     * @param $lastName
     * @param $company
     * @param $www
     * @param $street
     * @param $streetNumber
     * @param $zipCode
     * @param $cityPart
     * @param $city
     * @param $country
     * @param $postOfficeZipCode
     * @param string $type
     */
    public function __construct(
        $id,
        $firstName,
        $lastName,
        $company,
        $www,
        $street,
        $streetNumber,
        $zipCode,
        $cityPart,
        $city,
        $country,
        $postOfficeZipCode,
        $type = SenderType::C
    ) {
        $this->setId($id);
        $this->setFirstName($firstName);
        $this->setLastName($lastName);
        $this->setCompany($company);
        $this->setWWW($www);
        $this->setStreet($street);
        $this->setStreetNumber($streetNumber);
        $this->setZipCode($zipCode);
        $this->setCity($city);
        $this->setCityPart($cityPart);
        $this->setType($type);
        $this->setCountry($country);
        $this->setPostOfficeZipCode($postOfficeZipCode);
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @param string $company
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }

    /**
     * @param string $www
     */
    public function setWww($www)
    {
        $this->www = $www;
    }

    /**
     * @param string $street
     */
    public function setStreet($street)
    {
        $this->street = $street;
    }

    /**
     * @param string $streetNumber
     */
    public function setStreetNumber($streetNumber)
    {
        $this->streetNumber = $streetNumber;
    }

    /**
     * @param string $zipCode
     */
    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @param string $cityPart
     */
    public function setCityPart($cityPart)
    {
        $this->cityPart = $cityPart;
    }

    /**
     * @param null|string $country
     * @throws WrongDataException
     */
    public function setCountry($country)
    {
        if (!in_array($country, Country::$list)) {
            throw new WrongDataException(sprintf('Country Code %s is not supported, use one of %s', $country, implode(', ', Country::$list)));
        }
        $this->country = $country;
    }

    /**
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param integer $type
     * @throws WrongDataException
     */
    public function setType($type)
    {
        if (!in_array($type, SenderType::$list)) {
            throw new WrongDataException(sprintf('$type %s is not supported, use one of %s', $type, implode(', ', SenderType::$list)));
        }
        $this->type = $type;
    }

    /**
     * @param $postOfficeZipCode
     */
    public function setPostOfficeZipCode($postOfficeZipCode)
    {
        $this->postOfficeZipCode = $postOfficeZipCode;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getCityPart()
    {
        return $this->cityPart;
    }

    /**
     * @return string
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }

    /**
     * @return string
     */
    public function getStreetNumber()
    {
        return $this->streetNumber;
    }

    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @return string
     */
    public function getWww()
    {
        return $this->www;
    }

    /**
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @return string
     */
    public function getPostOfficeZipCode()
    {
        return $this->postOfficeZipCode;
    }

    /**
     * @return null|string
     */
    public function getCountry()
    {
        return $this->country;
    }

}