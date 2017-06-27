<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\CzechPostApi\Enum;


class Product
{
    const PACKAGE_TO_HAND = 'DR';
    const PACKAGE_TO_THE_POST_OFFICE = 'NP';

    /** @var array */
    public static $list = [
        self::PACKAGE_TO_HAND,
        self::PACKAGE_TO_THE_POST_OFFICE
    ];
}