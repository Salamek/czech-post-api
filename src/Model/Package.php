<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\CzechPostApi\Model;


use Salamek\CzechPostApi\Enum\PackageService;
use Salamek\CzechPostApi\Enum\Product;
use Salamek\CzechPostApi\Exception\WrongDataException;
use Salamek\CzechPostApi\Tools;
use Salamek\CzechPostApi\Validator;

class Package
{
    /** @var Validator */
    private $validator;

    /** @var integer */
    private $seriesNumberId;

    /** @var string */
    private $packageProductType;

    /** @var string */
    private $packageNumber;

    /** @var Sender */
    private $sender;

    /** @var Recipient */
    private $recipient;

    /** @var null|PaymentInfo */
    private $paymentInfo = null;

    /** @var null|WeightedPackageInfo */
    private $weightedPackageInfo = null;

    /** @var  integer */
    private $goodsPrice;

    /** @var  array */
    private $services = [];

    /** @var  string */
    private $description;

    /** @var integer */
    private $packageCount;

    /** @var integer */
    private $packagePosition;

    /** @var null|string */
    private $parentPackageNumber = null;

    /** @var null|string */
    private $internalPackageNumber;

    /**
     * Package constructor.
     * @param int $seriesNumberId
     * @param string $packageProductType
     * @param Sender $sender
     * @param Recipient $recipient
     * @param null|PaymentInfo $paymentInfo
     * @param null|WeightedPackageInfo $weightedPackageInfo
     * @param null|integer $goodsPrice
     * @param array $services
     * @param null|string $description
     * @param integer $packageCount
     * @param integer $packagePosition
     * @param null|string $parentPackageNumber
     */
    public function __construct(
        $seriesNumberId,
        $packageProductType = Product::PACKAGE_TO_HAND,
        Sender $sender,
        Recipient $recipient,
        PaymentInfo $paymentInfo = null,
        WeightedPackageInfo $weightedPackageInfo = null,
        $goodsPrice = null,
        array $services = [],
        $description = null,
        $packageCount = 1,
        $packagePosition = 1,
        $parentPackageNumber = null,
        $internalPackageNumber = null
    ) {
        $this->validator = new Validator();

        $this->setServices($services); //Must be first, other setters are setting services too

        $this->setPackageProductType($packageProductType);
        $this->setSender($sender);
        $this->setRecipient($recipient);
        $this->setPaymentInfo($paymentInfo);
        $this->setWeightedPackageInfo($weightedPackageInfo);
        $this->setGoodsPrice($goodsPrice);
        $this->setPackageCount($packageCount);
        $this->setPackagePosition($packagePosition);
        $this->setParentPackageNumber($parentPackageNumber);

        $this->setDescription($description);

        $this->setInternalPackageNumber($internalPackageNumber);

        if (!is_null($seriesNumberId)) {
            $this->setSeriesNumberId($seriesNumberId);
        }
    }

    /**
     * @param int $seriesNumberId
     * @throws WrongDataException
     */
    public function setSeriesNumberId($seriesNumberId)
    {
        if (!is_numeric($seriesNumberId)) {
            throw new WrongDataException('$seriesNumberId has wrong format');
        }
        $this->seriesNumberId = $seriesNumberId;
        $this->setPackageNumber(Tools::generatePackageNumber($this));
    }

    /**
     * @param string $packageProductType
     * @throws WrongDataException
     */
    public function setPackageProductType($packageProductType)
    {
        if (!in_array($packageProductType, Product::$list)) {
            throw new WrongDataException(sprintf('Product %s is not supported, use one of %s', $packageProductType, implode(', ', Product::$list)));
        }

        if ($this->getRecipient() && !$this->validator->validateZipCodeDelivery($this->getRecipient()->getZipCode(), $packageProductType)) {
            throw new WrongDataException('This delivery type is not supported for this zip code');
        }

        $this->packageProductType = $packageProductType;
    }

    /**
     * @param string $packageNumber
     */
    public function setPackageNumber($packageNumber)
    {
        $this->packageNumber = $packageNumber;
    }

    /**
     * @param Sender $sender
     */
    public function setSender(Sender $sender)
    {
        $this->sender = $sender;
    }

    /**
     * @param string $internalPackageNumber
     */
    public function setInternalPackageNumber($internalPackageNumber)
    {
        $this->internalPackageNumber = $internalPackageNumber;
    }

    /**
     * @param Recipient $recipient
     * @throws WrongDataException
     */
    public function setRecipient(Recipient $recipient)
    {
        if (!$recipient->getSkipStreetValidation() && $this->getPackageProductType() && !$this->validator->validateZipCodeDelivery($recipient->getZipCode(), $this->getPackageProductType())) {
            throw new WrongDataException('This delivery type is not supported for this zip code');
        }
        $this->recipient = $recipient;
    }

    /**
     * @param null|PaymentInfo $paymentInfo
     */
    public function setPaymentInfo(PaymentInfo $paymentInfo = null)
    {
        if (!is_null($paymentInfo))
        {
            $this->services[PackageService::BEZDOKLADOVA_DOBIRKA] = PackageService::BEZDOKLADOVA_DOBIRKA;
        }
        $this->paymentInfo = $paymentInfo;
    }

    /**
     * @param null|WeightedPackageInfo $weightedPackageInfo
     */
    public function setWeightedPackageInfo(WeightedPackageInfo $weightedPackageInfo = null)
    {
        $this->weightedPackageInfo = $weightedPackageInfo;
    }

    /**
     * @param int $goodsPrice
     */
    public function setGoodsPrice($goodsPrice)
    {
        if (!is_null($goodsPrice))
        {
            $this->services[PackageService::UDANA_CENA] = PackageService::UDANA_CENA;
        }
        $this->goodsPrice = $goodsPrice;
    }

    /**
     * @param array $services
     */
    public function setServices(array $services)
    {
        $this->services = $services;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @param int $packageCount
     * @throws WrongDataException
     */
    public function setPackageCount($packageCount)
    {
        if (!is_numeric($packageCount))
        {
            throw new WrongDataException('$packageCount must be numeric');
        }

        if ($packageCount > 1)
        {
            $this->services[PackageService::VICEKUSOVA_ZASILKA] = PackageService::VICEKUSOVA_ZASILKA;
        }

        $this->packageCount = $packageCount;
    }

    /**
     * @param int $packagePosition
     * @throws WrongDataException
     */
    public function setPackagePosition($packagePosition)
    {
        if (!is_numeric($packagePosition))
        {
            throw new WrongDataException('$packagePosition must be numeric');
        }
        $this->packagePosition = $packagePosition;
    }

    /**
     * @param null|string $parentPackageNumber
     */
    public function setParentPackageNumber($parentPackageNumber)
    {
        $this->parentPackageNumber = $parentPackageNumber;
    }

    /**
     * @return int
     */
    public function getSeriesNumberId()
    {
        return $this->seriesNumberId;
    }

    /**
     * @return string
     */
    public function getPackageProductType()
    {
        return $this->packageProductType;
    }

    /**
     * @return string
     */
    public function getPackageNumber()
    {
        return $this->packageNumber;
    }

    /**
     * @return string
     */
    public function getInternalPackageNumber()
    {
        return $this->internalPackageNumber;
    }

    /**
     * @return Sender
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @return Recipient
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @return null|PaymentInfo
     */
    public function getPaymentInfo()
    {
        return $this->paymentInfo;
    }

    /**
     * @return null|WeightedPackageInfo
     */
    public function getWeightedPackageInfo()
    {
        return $this->weightedPackageInfo;
    }

    /**
     * @return int
     */
    public function getGoodsPrice()
    {
        return $this->goodsPrice;
    }

    /**
     * @return array
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return int
     */
    public function getPackageCount()
    {
        return $this->packageCount;
    }

    /**
     * @return int
     */
    public function getPackagePosition()
    {
        return $this->packagePosition;
    }

    /**
     * @return null|string
     */
    public function getParentPackageNumber()
    {
        return $this->parentPackageNumber;
    }

    public function __sleep()
    {
        return array_diff(array_keys(get_object_vars($this)), array('validator'));
    }
}
