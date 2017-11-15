<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\CzechPostApi;


use Salamek\CzechPostApi\Enum\SenderType;
use Salamek\CzechPostApi\Exception\WrongDataException;
use Salamek\CzechPostApi\Model\Package;

class Tools
{
    /** @var array  */
    public static $senderTypePackageIdFormat = [
        SenderType::C => '%s%04d%05d%s%s',
        SenderType::B => '%s%04d%05d%s%s',
        SenderType::M => '%s%05d%04d%s%s',
        SenderType::L => '%s%05d%04d%s%s'
    ];

    /**
     * @param Package $package
     * @return mixed
     * @throws WrongDataException
     * @throws \Exception
     */
    public static function generatePackageNumber(Package $package)
    {
        if (!$package->getSeriesNumberId()) {
            throw new WrongDataException('Package has no Series number ID!');
        }

        $id = sprintf(self::$senderTypePackageIdFormat[$package->getSender()->getType()], $package->getPackageProductType(), $package->getSender()->getId(), $package->getSeriesNumberId(), self::calculateControlNumber($package), $package->getSender()->getType());

        if (strlen($id) != 13) {
            throw new \Exception(sprintf('Failed to generate correct package id:%s', $id));
        }

        return $id;
    }

    /**
     * Calculates control number modulo 11
     * @param Package $package
     * @return int
     * @throws \Exception
     */
    public static function calculateControlNumber(Package $package)
    {
        switch ($package->getSender()->getType()) {
            default:
            case SenderType::C:
            case SenderType::B:
            case SenderType::M:
            case SenderType::L:
            case SenderType::F:
            case SenderType::E:
            case SenderType::P:
            case SenderType::U:
            case SenderType::T:
                $modulo = [1, 8, 6, 4, 2, 3, 5, 9, 7];
                break;

            case SenderType::CZ:
                $modulo = [8, 6, 4, 2, 3, 5, 9, 7];
                break;
        }

        $numberArray = str_split(str_pad($package->getSender()->getId(), 4, '0', STR_PAD_LEFT) . str_pad($package->getSeriesNumberId(), 5, '0', STR_PAD_LEFT));
        if (count($modulo) != count($numberArray)) {
            throw new \Exception(sprintf('Wrong number array or modulo: %s != %s', count($modulo), count($numberArray)));
        }

        $sum = null;
        foreach ($modulo AS $k => $v) {
            $sum += $numberArray[$k] * $v;
        }

        $left = $sum % 11;

        if ($left > 1) {
            $controlNumber = 11 - $left;
        } else {
            if ($left == 0) {
                $controlNumber = 5;
            } else {
                if ($left == 1) {
                    $controlNumber = 0;
                } else {
                    throw new \Exception('Failed to calculate control number');
                }
            }
        }

        return $controlNumber;
    }
}
