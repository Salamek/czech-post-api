<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\CzechPostApi\Model;



use Salamek\CzechPostApi\Exception\WrongDataException;

class WeightedPackageInfo
{
    /** @var float */
    private $weight;

    /** @var null|int */
    private $height = null;

    /** @var null|int */
    private $width = null;

    /** @var null|int */
    private $length = null;

    /**
     * WeightedPackageInfo constructor.
     * @param float $weight (In KG)
     * @param int|null $height
     * @param int|null $width
     * @param int|null $length
     */
    public function __construct($weight, $height = null, $width = null, $length = null)
    {
        $this->setWeight($weight);
        $this->setHeight($height);
        $this->setWidth($width);
        $this->setLength($length);
    }


    /**
     * @param float $weight
     * @throws WrongDataException
     */
    public function setWeight($weight)
    {
        if (!is_numeric($weight)) {
            throw new WrongDataException('$weight has wrong value');
        }
        $this->weight = $weight;
    }

    /**
     * @param int|null $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * @param int|null $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * @param int|null $length
     */
    public function setLength($length)
    {
        $this->length = $length;
    }

    /**
     * @return float
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @return int|null
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @return int|null
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return int|null
     */
    public function getLength()
    {
        return $this->length;
    }
}