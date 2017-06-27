<?php

namespace Salamek\CzechPostApi\Enum;

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */
class PackageService
{
    const DO_RUKOU = 1;
    const DO_RUKOU_DORUCIT_DOPOLEDNE = '1A';
    const DO_RUKOU_DORUCIT_ODPOLEDNE = '1B';
    const DOBIRKA_CSOB = 2; //Složenka PS ??? !FIXME
    const DODEJKA = 3;
    const DOBIRKA_PK_A = 4;
    const DOBIRKA_PK_C = 5;
    const ODPOVEDNI_ZASILKA = 6;
    const UDANA_CENA = 7;
    const DO_VLASTNICH_RUKOU_VYHRADNE_JEN_ADRESATA = 8;
    const PRIORITAIRE_LETECKY = 9;
    const NESKLADNE = 10;
    const KREHKE = 11;
    const ULOZIT_7_DNU = 12;
    const OPIS_PODACI_STVRZENKY = 13;
    const GARANTOVANY_CAS_DODANI = 14;
    const PILNE = 15;
    const NEPRODLUZOVAT_ODBERNI_LHUTU = 16;
    const GARANTOVANE_DODANI_V_SOBOTU = 18;
    const GARANTOVANY_CAS_DODANI_V_NE_SO = 19;
    const NEUKLADAT = 20;
    const ULOZIT_3_DNY = 21;
    const ULOZIT_10_DNU = 22;
    const ZMESKALA = 23;
    const KOMPLETNI_DORUCENI = 24;
    const ODVOZ_STAREHO_SPOTREBICE = 25;


    const AVIZO_SE = 45;
    const AVIZO_EMAIL = 46;
    const POST_BOX = 85;
    const SLEVA_DATOVE_PODANI = 90;
    const VICEKUSOVA_ZASILKA = 70;
    const BEZDOKLADOVA_DOBIRKA = 41;
    const DODANI_FIRME = 40;
    const EDODEJKA_EMAIL = 77;
    const PRODLOUZENI_ULOZNI_DOBY = 30;
    const AVIZO_SMS = 34;
    const EDODEJKA_SE = 78;
    const NEODESILAT = 26;
    const SLEVA_J = 28;
    const DO_RUKOU_NAD_30_KG = 29;
    const EDODEJKA_SMS = 76;
    const NEKLOPIT = 47;
    const SLEVA_KONTAKTNI_INFO = 97;


    /** @var array */
    public static $list = [
        self::DO_RUKOU,
        self::DO_RUKOU_DORUCIT_DOPOLEDNE,
        self::DO_RUKOU_DORUCIT_ODPOLEDNE,
        self::DOBIRKA_CSOB,
        self::DODEJKA,
        self::DOBIRKA_PK_A,
        self::DOBIRKA_PK_C,
        self::ODPOVEDNI_ZASILKA,
        self::UDANA_CENA,
        self::DO_VLASTNICH_RUKOU_VYHRADNE_JEN_ADRESATA,
        self::PRIORITAIRE_LETECKY,
        self::NESKLADNE,
        self::KREHKE,
        self::ULOZIT_7_DNU,
        self::OPIS_PODACI_STVRZENKY,
        self::GARANTOVANY_CAS_DODANI,
        self::PILNE,
        self::NEPRODLUZOVAT_ODBERNI_LHUTU,
        self::GARANTOVANE_DODANI_V_SOBOTU,
        self::GARANTOVANY_CAS_DODANI_V_NE_SO,
        self::NEUKLADAT,
        self::ULOZIT_3_DNY,
        self::ULOZIT_10_DNU,
        self::ZMESKALA,
        self::KOMPLETNI_DORUCENI,
        self::ODVOZ_STAREHO_SPOTREBICE,

        self::AVIZO_SE,
        self::AVIZO_EMAIL,
        self::POST_BOX,
        self::SLEVA_DATOVE_PODANI,
        self::VICEKUSOVA_ZASILKA,
        self::BEZDOKLADOVA_DOBIRKA,
        self::DODANI_FIRME,
        self::EDODEJKA_EMAIL,
        self::PRODLOUZENI_ULOZNI_DOBY,
        self::AVIZO_SMS,
        self::EDODEJKA_SE,
        self::NEODESILAT,
        self::SLEVA_J,
        self::DO_RUKOU_NAD_30_KG,
        self::EDODEJKA_SMS,
        self::NEKLOPIT,
        self::SLEVA_KONTAKTNI_INFO
    ];

    /** @var array */
    public static $cashOnDelivery = [
        self::DOBIRKA_PK_A,
        self::DOBIRKA_PK_C,
        self::DOBIRKA_CSOB,
        self::BEZDOKLADOVA_DOBIRKA
    ];

    /** @var array */
    public static $zoneA = [
        self::DO_RUKOU_DORUCIT_DOPOLEDNE,
        self::GARANTOVANY_CAS_DODANI,
        self::GARANTOVANY_CAS_DODANI_V_NE_SO,
        self::DODANI_FIRME
    ];

    /** @var array */
    public static $zoneB = [
        self::DO_RUKOU_DORUCIT_ODPOLEDNE
    ];
}