<?php
namespace Salamek\CzechPostApi;

use Httpful\Request;
use Httpful\Response;
use Salamek\CzechPostApi\Enum\LabelDecomposition;
use Salamek\CzechPostApi\Enum\PackageService;
use Salamek\CzechPostApi\Enum\Product;
use Salamek\CzechPostApi\Exception\WrongDataException;
use Salamek\CzechPostApi\Model\Package;


/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */
class Api extends FakeClient
{
    /**
     * @param array $packages
     * @param int|null $customFillingId
     * @throws \Exception
     */
    public function createPackages(
        array $packages,
        $customFillingId = NULL,
        $cardIdentifier = 'null;/1;/0;/null'
    ) {
        $filingDate = new \DateTime;
        $filingId = $customFillingId == NULL ? (date('H') + 1) : $customFillingId;

        //Close all batches containing packages, delete empty batches
        foreach ($this->getOpenBatches() AS $openBatch) {
            if ($openBatch['closeUrl']) {
                $this->closeOpenBatch($openBatch['id']);
            } else {
                if ($openBatch['deleteUrl']) {
                    $this->deleteOpenBatch($openBatch['id']);
                }
            }
        }

        //Close or delete all open fillings not matching filingDate or filingId
        foreach ($this->getOpenFilings() AS $openFilling) {
            if ($openFilling != $filingId || $filingDate != $openFilling['filingDate']) {
                if ($openFilling['closeUrl']) {
                    $this->closeOpenFiling($openFilling['id']);
                } else {
                    if ($openFilling['deleteUrl']) {
                        $this->deleteOpenFiling($openFilling['id']);
                    }
                }
            }
        }

        if (!empty($packages)) {

            $rows = [];
            /** @var Package $package */
            foreach ($packages AS $package) {
                $services = $package->getServices();
                $services[PackageService::SLEVA_DATOVE_PODANI] = PackageService::SLEVA_DATOVE_PODANI; //Sleva za datove podani

                switch ($this->importConfiguration) {
                    case self::IMPORT_FORMAT_EXPORTED:
                        $row = array(
                            $package->getPackageNumber(), //1
                            null, //2
                            null, //3
                            null, //4
                            null, //5
                            null, //6
                            null, //7
                            $package->getRecipient()->getFirstName(), //8
                            $package->getRecipient()->getLastName(), //9
                            null, //10
                            null, //11
                            ($package->getPackageProductType() == Product::PACKAGE_TO_THE_POST_OFFICE ? 'Na postu' : $package->getRecipient()->getStreet()), //12
                            $package->getRecipient()->getStreetNumber(), //13
                            null, //14 $streetNumberSecond
                            $package->getRecipient()->getCity(), //15
                            $package->getRecipient()->getCityPart(), //16
                            $package->getRecipient()->getZipCode(), //17
                            $package->getRecipient()->getCountry(), //18
                            ($package->getPaymentInfo() ? $package->getPaymentInfo()->getCashOnDeliveryPrice() : null), // 19
                            $package->getGoodsPrice(), //20
                            ($package->getWeightedPackageInfo() ? $package->getWeightedPackageInfo()->getWeight() / 1000 : null), //21
                            implode('+', $services), //22
                            ($package->getPaymentInfo() ? $package->getPaymentInfo()->getCashOnDeliveryVariableSymbol() : null), //23
                            $package->getInternalPackageNumber(), //24
                            null, // 25
                            null, //26
                            null, //27
                            null, //28
                            ($package->getWeightedPackageInfo() ? $package->getWeightedPackageInfo()->getHeight() : null), //29
                            ($package->getWeightedPackageInfo() ? $package->getWeightedPackageInfo()->getWidth() : null), //30
                            ($package->getWeightedPackageInfo() ? $package->getWeightedPackageInfo()->getLength() : null), //31
                            null, //32
                            null, //33
                            null, //34
                            null, //35
                            null, //36
                            $package->getDescription(), //37
                            $package->getDescription(), //38
                            null, //39
                            null, //40
                            null, //41
                            null, //42
                            null, //43
                            null, //44
                            null, //45
                            null, //46
                            $package->getPackageProductType(), //47
                            $package->getRecipient()->getCompany(), //48
                            $package->getRecipient()->getCompanyVatId(), //49
                            $package->getRecipient()->getCompanyId(), //50
                            $package->getRecipient()->getEmail(), //51
                            $package->getRecipient()->getPhone(), //52
                            null, //53
                            $package->getRecipient()->getPhone(), //54
                            null, //55
                            null, //56
                        );
                        break;

                    default:
                        throw new \Exception(sprintf('Import configuration %s not supported', $this->importConfiguration));
                        break;
                }


                $rows[] = $row;
            }


            //Create new empty filling
            $this->createFiling($package->getSender()->getType() . $package->getSender()->getId(), $filingDate, $filingId, '', '', $cardIdentifier);

            $this->importBatchData($rows);
        }
    }

    /**
     * @return array|object|string
     */
    public function getRegions()
    {
        /** @var Response $response */
        $response = Request::get('https://b2c.cpost.cz/services/Address/getRegionListAsJson')
            ->expectsJson()
            ->send();
        return $response->body;
    }

    /**
     * @param $regionId
     * @return array|object|string
     */
    public function getDistricts($regionId)
    {
        $params = http_build_query([
            'id' => $regionId,
        ]);
        /** @var Response $response */
        $response = Request::get('https://b2c.cpost.cz/services/Address/getDistrictListAsJson?' . $params)
            ->expectsJson()
            ->send();
        return $response->body;
    }

    /**
     * @param $districtId
     * @return array|object|string
     */
    public function getCities($districtId)
    {
        $params = http_build_query([
            'id' => $districtId,
        ]);
        /** @var Response $response */
        $response = Request::get('https://b2c.cpost.cz/services/Address/getCityListAsJson?' . $params)
            ->expectsJson()
            ->send();
        return $response->body;
    }

    /**
     * @param $cityId
     * @return array|object|string
     */
    public function getCityParts($cityId)
    {
        $params = http_build_query([
            'id' => $cityId,
        ]);
        /** @var Response $response */
        $response = Request::get('https://b2c.cpost.cz/services/Address/getCityPartListAsJson?' . $params)
            ->expectsJson()
            ->send();
        return $response->body;
    }

    /**
     * @param $cityPartId
     * @return array|object|string
     */
    public function getStreets($cityPartId)
    {
        $params = http_build_query([
            'id' => $cityPartId,
        ]);
        /** @var Response $response */
        $response = Request::get('https://b2c.cpost.cz/services/Address/getStreetListAsJson?' . $params)
            ->expectsJson()
            ->send();
        return $response->body;
    }

    /**
     * @param $streetId
     * @param $cityPartId
     * @return array|object|string
     */
    public function getStreetNumbers($streetId, $cityPartId = null)
    {
        $params = http_build_query([
            'idStreet' => $streetId,
            'idCityPart' => $cityPartId,
        ]);
        /** @var Response $response */
        $response = Request::get('https://b2c.cpost.cz/services/Address/getNumberListAsJson?' . $params)
            ->expectsJson()
            ->send();
        return $response->body;
    }

    /**
     * @param array $packageNumbers
     * @param string $language
     * @return array|object|string
     * @throws WrongDataException
     */
    public function getPackages(array $packageNumbers, $language = 'cs')
    {
        if (count($packageNumbers) > 10) {
            throw new WrongDataException('You can requests only 10 packages max');
        }

        $params = http_build_query([
            'idParcel' => implode(';', $packageNumbers),
            'language' => $language,
        ]);
        /** @var Response $response */
        $response = Request::get('https://b2c.cpost.cz/services/ParcelHistory/getDataAsJson?' . $params)
            ->expectsJson()
            ->send();
        return $response->body;
    }

    /**
     * @param $idDistrict
     * @param null $cityOrPart
     * @param null $cityPart
     * @param null $nameStreet
     * @param null $number
     * @param null $postCode
     * @return array|object|string
     * @throws WrongDataException
     */
    public function findPostCodes($idDistrict = null, $cityOrPart = null, $cityPart = null, $nameStreet = null, $number = null, $postCode = null)
    {
        if (!$cityOrPart && !$postCode)
        {
            throw new WrongDataException('$cityOrPart or $postCode is required');
        }

        $params = http_build_query([
            'idDistrict' => $idDistrict,
            'cityOrPart' => $cityOrPart,
            'cityPart' => $cityPart,
            'nameStreet' => $nameStreet,
            'number' => $number,
            'postCode' => $postCode,
        ]);
        /** @var Response $response */
        $response = Request::get('https://b2c.cpost.cz/services/PostCode/getDataAsJson?' . $params)
            ->expectsJson()
            ->send();
        return $response->body;
    }

    /**
     * @param null $idDistrict
     * @param null $place
     * @param null $postCode
     * @param null $gps
     * @param bool $deliveryToPostOffice
     * @param bool $deliveryToHand
     * @param bool $sale
     * @param bool $copyService
     * @param bool $czechpoint
     * @param bool $atmCSOB
     * @param bool $stickerHighway
     * @param bool $westernUnion
     * @param bool $saturday
     * @param bool $sundayAndHoliday
     * @param bool $shopingCenter
     * @param bool $parking
     * @param bool $extendedOpeningHours
     * @param bool $postovna
     * @param string $language
     * @return array|object|string
     */
    public function getPostOfficeInformations($idDistrict = null, $place = null, $postCode = null, $gps = null, $deliveryToPostOffice = true, $deliveryToHand = true, $sale = true, $copyService = true, $czechpoint = true, $atmCSOB = true, $stickerHighway = true, $westernUnion = true, $saturday = true, $sundayAndHoliday = true, $shopingCenter = true, $parking = true, $extendedOpeningHours = true, $postovna = true, $language = 'cs')
    {
        $params = http_build_query([
            'idDistrict' => $idDistrict,
            'place' => $place,
            'postCode' => $postCode,
            'gps' => $gps,
            'deliveryToPostOffice' => ($deliveryToPostOffice ? 1 : 0),
            'deliveryToHand' => ($deliveryToHand ? 1 : 0),
            'sale' => ($sale ? 1 : 0),
            'copyService' => ($copyService ? 1 : 0),
            'czechpoint' => ($czechpoint ? 1 : 0),
            'atmCSOB' => ($atmCSOB ? 1 : 0),
            'stickerHighway' => ($stickerHighway ? 1 : 0),
            'westernUnion' => ($westernUnion ? 1 : 0),
            'saturday' => ($saturday ? 1 : 0),
            'sundayAndHoliday' => ($sundayAndHoliday ? 1 : 0),
            'shopingCenter' => ($shopingCenter ? 1 : 0),
            'parking' => ($parking ? 1 : 0),
            'extendedOpeningHours' => ($extendedOpeningHours ? 1 : 0),
            'postovna' => ($postovna ? 1 : 0),
            'language' => $language
        ]);
        /** @var Response $response */
        $response = Request::get('https://b2c.cpost.cz/services/PostOfficeInformation/getDataAsJson?' . $params)
            ->expectsJson()
            ->send();
        return $response->body;
    }

    /**
     * @param \DateTimeInterface $date
     * @return array|object|string
     */
    public function getLastUpdate(\DateTimeInterface $date)
    {
        $params = http_build_query([
            'date' => $date->format('Y-m-d')
        ]);
        /** @var Response $response */
        $response = Request::get('https://b2c.cpost.cz/services/PostOfficeInformation/getUpdatedPostAsJson?' . $params)
            ->expectsJson()
            ->send();
        return $response->body;
    }

    /**
     * @param $idAddress
     * @return bool
     */
    public function isAfternoonParcelDelivery($idAddress)
    {
        $params = http_build_query([
            'idAddress' => $idAddress
        ]);
        /** @var Response $response */
        $response = Request::get('https://b2c.cpost.cz/services/AfternoonParcelDelivery/getDataAsJson?' . $params)
            ->expectsJson()
            ->send();
        return ($response->body->available ? true : false);
    }

    /**
     * @param array $packages
     * @param int $decomposition
     * @return string
     * @throws \Exception
     */
    public function getLabels(array $packages, $decomposition = LabelDecomposition::QUARTER)
    {
        user_error("getLabels is deprecated, use Label::generateLabels instead.", E_DEPRECATED);
        $label = new Label();
        return $label->generateLabels($packages, $decomposition);
    }
}
