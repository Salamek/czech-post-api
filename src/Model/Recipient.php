<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\CzechPostApi\Model;

use Salamek\CzechPostApi\Enum\Country;
use Salamek\CzechPostApi\Exception\WrongDataException;
use Salamek\CzechPostApi\Validator;

/**
 * Class Recipient
 * @package Salamek\MyApi
 */
class Recipient
{
    /** @var Validator */
    private $validator;

    /** @var  string */
    private $firstName;

    /** @var  string */
    private $lastName;

    /** @var string */
    private $street;

    /** @var string */
    private $streetNumber;

    /** @var string */
    private $city;

    /** @var null|string */
    private $cityPart = null;

    /** @var null|string */
    private $company = null;

    /** @var null|string */
    private $companyId = null;

    /** @var null|string */
    private $companyVatId = null;

    /** @var null|string */
    private $country = null;

    /** @var null|string */
    private $email = null;

    /** @var null|string */
    private $phone = null;

    /** @var null|string */
    private $www = null;

    /** @var string */
    private $zipCode;

    private $skipStreetValidation;

    /**
     * Recipient constructor.
     * @param string $firstName
     * @param string $lastName
     * @param string $street
     * @param string $streetNumber
     * @param string $city
     * @param null|string $cityPart
     * @param string $zipCode
     * @param null|string $company
     * @param null|string $companyId
     * @param null|string $companyVatId
     * @param null|string $country
     * @param null|string $email
     * @param null|string $phone
     * @param null|string $www
     * @param null|bool $skipStreetValidation
     */
    public function __construct($firstName, $lastName, $street, $streetNumber, $city, $cityPart, $zipCode, $company, $companyId, $companyVatId, $country, $email, $phone, $www, $skipStreetValidation = false)
    {
        $this->validator = new Validator();

        $this->skipStreetValidation = $skipStreetValidation;

        $this->setFirstName($firstName);
        $this->setLastName($lastName);
        $this->setStreet($street);
        $this->setStreetNumber($streetNumber);
        $this->setCity($city);
        $this->setCityPart($cityPart);
        $this->setZipCode($zipCode);
        $this->setCompany($company);
        $this->setCompanyId($companyId);
        $this->setCompanyVatId($companyVatId);
        $this->setCountry($country);
        $this->setEmail($email);
        $this->setPhone($phone);
        $this->setWww($www);
    }


    /**
     * @param $firstName
     * @throws WrongDataException
     */
    public function setFirstName($firstName)
    {
        if (!trim($firstName)) {
            throw new WrongDataException('First name must be not empty string');
        }
        $this->firstName = $firstName;
    }

    /**
     * @param $lastName
     * @throws WrongDataException
     */
    public function setLastName($lastName)
    {
        if (!trim($lastName)) {
            throw new WrongDataException('Last name must be not empty string');
        }
        $this->lastName = $lastName;
    }


    /**
     * @param $www
     * @throws WrongDataException
     */
    public function setWww($www)
    {
        if ($www && filter_var($www, FILTER_VALIDATE_URL) === false) {
            throw new WrongDataException('WWW have wrong format');
        }

        $this->www = $www;
    }

    /**
     * @param $street
     * @throws WrongDataException
     */
    public function setStreet($street)
    {
        if (!$this->skipStreetValidation && !trim($street)) {
            throw new WrongDataException('Street have wrong format');
        }

        $this->street = $street;
    }

    /**
     * @param $streetNumber
     * @throws WrongDataException
     */
    public function setStreetNumber($streetNumber)
    {
        if (!$this->skipStreetValidation && !trim($streetNumber)) {
            throw new WrongDataException('Street number have wrong format');
        }
        $this->streetNumber = $streetNumber;
    }

    /**
     * @param $city string
     * @throws WrongDataException
     */
    public function setCity($city)
    {
        if (strlen($city) > 50) {
            throw new WrongDataException('$city is longer then 50 characters');
        }
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
     * @param $country string
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
     * @param $email string
     * @throws WrongDataException
     */
    public function setEmail($email)
    {
        if (strlen($email) > 50) {
            throw new WrongDataException('$email is longer then 100 characters');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new WrongDataException('$email have invalid value');
        }

        $this->email = $email;
    }

    /**
     * @param $phone
     * @throws WrongDataException
     */
    public function setPhone($phone)
    {
        if (!$this->validator->validatePhone($phone))
        {
            throw new WrongDataException('Phone number have wrong format');
        }

        $this->phone = $phone;
    }

    /**
     * @param $zipCode
     * @throws WrongDataException
     */
    public function setZipCode($zipCode)
    {
        if (!$this->skipStreetValidation && !$this->validator->validateZipCode($zipCode)) {
            throw new WrongDataException('Zip code have wrong format');
        }

        $this->zipCode = $zipCode;
    }

    /**
     * @param string $company
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }

    /**
     * @param null|string $companyId
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;
    }

    /**
     * @param null|string $companyVatId
     */
    public function setCompanyVatId($companyVatId)
    {
        $this->companyVatId = $companyVatId;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @return null|string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @return null|string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return null|string
     */
    public function getPhone()
    {
        return $this->phone;
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
    public function getZipCode()
    {
        return $this->zipCode;
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
     * @return string
     */
    public function getStreetNumber()
    {
        return $this->streetNumber;
    }

    public function getSkipStreetValidation() {
        return $this->skipStreetValidation;
    }

    public function getSeparatedStreetNumber1() {
        if ($this->streetNumber == null) return null;

        $split = explode('/', $this->streetNumber);

        if (count($split) < 1) return null;

        return $split[0];
    }
    public function getSeparatedStreetNumber2() {
        if ($this->streetNumber == null) return null;

        $split = explode('/', $this->streetNumber);

        if (count($split) < 2) return null;

        return $split[1];
    }

    /**
     * @return null|string
     */
    public function getCityPart()
    {
        return $this->cityPart;
    }

    /**
     * @return null|string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @return null|string
     */
    public function getCompanyId()
    {
        return $this->companyId;
    }

    /**
     * @return null|string
     */
    public function getCompanyVatId()
    {
        return $this->companyVatId;
    }

    /**
     * @return null|string
     */
    public function getWww()
    {
        return $this->www;
    }

    public function __sleep()
    {
        return array_diff(array_keys(get_object_vars($this)), array('validator'));
    }
}
