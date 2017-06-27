<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\CzechPostApi\Enum;


class SenderType
{
    const C = 'C';
    const B = 'B';
    const M = 'M';
    const L = 'L';
    const F = 'F';
    const E = 'E';
    const P = 'P';
    const U = 'U';
    const T = 'T';
    const CZ = 'CZ';

    /** @var array */
    public static $list = [
        self::C,
        self::B,
        self::M,
        self::L,
        self::F,
        self::E,
        self::P,
        self::U,
        self::T,
        self::CZ,
    ];
}