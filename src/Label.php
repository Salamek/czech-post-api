<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\CzechPostApi;


use Salamek\CzechPostApi\Enum\LabelDecomposition;
use Salamek\CzechPostApi\Enum\LabelPosition;
use Salamek\CzechPostApi\Enum\PackageService;
use Salamek\CzechPostApi\Enum\Product;
use Salamek\CzechPostApi\Exception\WrongDataException;
use Salamek\CzechPostApi\Model\Package;

class Label
{
    /**
     * @param array $packages
     * @param int $decomposition
     * @return string
     * @throws \Exception
     * @throws WrongDataException
     */
    public static function generateLabels(array $packages, $decomposition = LabelDecomposition::FULL)
    {
        if (!in_array($decomposition, LabelDecomposition::$list)) {
            throw new WrongDataException(sprintf('unknown $decomposition only %s are allowed', implode(', ', LabelDecomposition::$list)));
        }

        $packageNumbers = [];
        /** @var Package $package */
        foreach ($packages AS $package) {
            $packageNumbers[] = $package->getPackageNumber();
        }

        $pdf = new \TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Adam Schubert');
        $pdf->SetTitle(sprintf('Czech Post Label %s', implode(', ', $packageNumbers)));
        $pdf->SetSubject(sprintf('Czech Post Label %s', implode(', ', $packageNumbers)));
        $pdf->SetKeywords('Czech Post');
        $pdf->SetFont('freeserif');
        $pdf->setFontSubsetting(true);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $quarterPosition = LabelPosition::TOP_LEFT;
        /** @var Package $package */
        foreach ($packages AS $package) {
            switch ($decomposition) {
                case LabelDecomposition::FULL:
                    $pdf->AddPage();
                    $pdf = self::generateLabelFull($pdf, $package);
                    break;
                case LabelDecomposition::QUARTER:
                    if ($quarterPosition > LabelPosition::BOTTOM_RIGHT) {
                        $quarterPosition = LabelPosition::TOP_LEFT;
                    }
                    if ($quarterPosition == LabelPosition::TOP_LEFT) {
                        $pdf->AddPage();
                    }
                    $pdf = self::generateLabelQuarter($pdf, $package, $quarterPosition);
                    $quarterPosition++;
                    break;
            }
        }
        return $pdf->Output(null, 'S');
    }

    /**
     * @param \TCPDF $pdf
     * @param Package $package
     * @return \TCPDF
     */
    public static function generateLabelFull(\TCPDF $pdf, Package $package)
    {
        $pdf->Image(__DIR__.'/../assets/logo.png', 20, 13, 70, '', 'PNG');
        $pdf->Image(sprintf(__DIR__.'/../assets/delivery_type_%s.png', $package->getPackageProductType()), 115, 13, 50, '', 'PNG');

        $pdf->SetFont($pdf->getFontFamily(), 'B', 55);
        $pdf->Text(166, 11, $package->getPackageProductType());

        $pdf->write1DBarcode($package->getPackageNumber(), 'C128', 200, 4, 90, 50, 0.3, ['stretch' => true]);

        $pdf->SetFont($pdf->getFontFamily(), '', 22);
        $pdf->Text(220, 55, $package->getPackageNumber());

        //Sender
        $senderX = 20;
        $senderY = 40;
        $pdf->SetFont($pdf->getFontFamily(), '', 22);
        $pdf->Text($senderX, $senderY, 'Odesílatel/Sender:');

        if ($package->getSender()->getCompany())
        {
            $pdf->Text($senderX, $senderY + 10, $package->getSender()->getCompany());
        }

        if ($package->getSender()->getFirstName() && $package->getSender()->getLastName())
        {
            $pdf->Text($senderX, $senderY + 20, sprintf('%s %s', $package->getSender()->getFirstName(), $package->getSender()->getLastName()));
        }

        $pdf->Text($senderX, $senderY + 30, $package->getSender()->getWww());

        $pdf->Text($senderX, $senderY + 40, sprintf('%s %s', $package->getSender()->getStreet(), $package->getSender()->getStreetNumber()));

        $pdf->Text($senderX, $senderY + 50, sprintf('%s %s - %s', $package->getSender()->getZipCode(), $package->getSender()->getCityPart(), $package->getSender()->getCity()));

        //Postal Office
        $pdf->Text($senderX, $senderY + 70, sprintf('PSČ dodací pošty: %s', $package->getSender()->getPostOfficeZipCode()));

        if ($package->getWeightedPackageInfo())
        {
            $pdf->Text($senderX, $senderY + 80, sprintf('Hmotnost: %s Kg', $package->getWeightedPackageInfo()->getWeight()/1000));
        }

        if (in_array($package->getPackageProductType(), [Product::PACKAGE_TO_HAND]))
        {
            if (!empty(array_intersect(PackageService::$zoneA, $package->getServices())))
            {
                $pdf->Image(__DIR__.'/../assets/a.png', 129, 35, 35, '', 'PNG');
            }
            else if (!empty(array_intersect(PackageService::$zoneB, $package->getServices())))
            {
                $pdf->Image(__DIR__.'/../assets/b.png', 129, 35, 35, '', 'PNG');
            }
        }

        if (!empty(array_intersect(PackageService::$cashOnDelivery, $package->getServices())) && $package->getPaymentInfo())
        {
            //Cash on Delivery info
            $pdf->Image(__DIR__.'/../assets/cashOnDelivery.png', 106, 102, 60, '', 'PNG');

            $pdf->Text($senderX, $senderY + 100, sprintf('Dobírka: =%s=', $package->getPaymentInfo()->getCashOnDeliveryPrice()));
            $pdf->Text($senderX, $senderY + 110, sprintf('Slovy: =%s=', self::numbers2words($package->getPaymentInfo()->getCashOnDeliveryPrice())));
        }

        // Multiple packages
        if ($package->getPackageCount() > 1)
        {
            $y = 70;
            //$pdf->SetFont($pdf->getFontFamily(), 'B', 9);

            //Stupid way how to generate foreign packageNumber
            //!FIXME Handle this in some better way...
            if ($package->getPackagePosition() != 1)
            {
                $fakePackageParent = clone $package;
                $fakePackageParent->setSeriesNumberId($package->getParentPackageNumber());
                $parentPackageNumber = Tools::generatePackageNumber($fakePackageParent);
            }
            else
            {
                $parentPackageNumber = null;
            }

            $text = sprintf('VK %s/%s %s', $package->getPackagePosition(), $package->getPackageCount(), ($package->getPackagePosition() == 1 ? 'Hlavní zásilka' : 'k hl. z. '.$parentPackageNumber));

            $pdf->MultiCell(106, 0, $text, ['LTRB' => ['width' => 0.2]], 'C', 0, 0, 170, $y, true, 0, false, true, 0);
        }

        // Receiver
        $receiverX = 170;
        $receiverY = 120;
        $pdf->Text($receiverX, $receiverY, 'Adresát/Address:');
        if ($package->getRecipient()->getCompany())
        {
            $pdf->Text($receiverX, $receiverY + 10, $package->getRecipient()->getCompany());
        }

        if ($package->getRecipient()->getFirstName() && $package->getRecipient()->getLastName()) {
            $pdf->Text($receiverX, $receiverY + 20, sprintf('%s %s', $package->getRecipient()->getFirstName(), $package->getRecipient()->getLastName()));
        }

        $pdf->Text($receiverX, $receiverY + 30, $package->getRecipient()->getPhone());
        $pdf->Text($receiverX, $receiverY + 40, sprintf('%s %s', $package->getRecipient()->getStreet(), $package->getRecipient()->getStreetNumber()));
        $pdf->Text($receiverX, $receiverY + 50, sprintf('%s %s %s', $package->getRecipient()->getZipCode(), $package->getRecipient()->getCityPart(), $package->getRecipient()->getCity()));
        $pdf->Text($receiverX, $receiverY + 60, $package->getRecipient()->getCountry());

        // Infotable
        $pdf->SetFont($pdf->getFontFamily(), 'B', 20);
        $pdf->Ln(5);
        $y = 104;
        $x = 170;
        $pdf->MultiCell(28, 12, '', ['LTRB' => ['width' => 0.7]], 'C', 0, 0, $x, $y, true, 0, false, true, 0);
        $pdf->MultiCell(28, 12, 'D+1', 1, 'C', 0, 0, $x + 28, $y, true, 0, false, true, 0);
        $pdf->MultiCell(50, 12, '', 1, 'C', 0, 0, $x + 56, $y, true, 0, false, true, 0);

        return $pdf;
    }

    /**
     * @param \TCPDF $pdf
     * @param Package $package
     * @param int $position
     * @return \TCPDF
     * @throws \Exception
     */
    public static function generateLabelQuarter(\TCPDF $pdf, Package $package, $position = LabelPosition::TOP_LEFT)
    {
        switch ($position) {
            default:
            case LabelPosition::TOP_LEFT:
                $xPositionOffset = 0;
                $yPositionOffset = 0;
                break;
            case LabelPosition::TOP_RIGHT:
                $xPositionOffset = 150;
                $yPositionOffset = 0;
                break;
            case LabelPosition::BOTTOM_LEFT:
                $xPositionOffset = 0;
                $yPositionOffset = 98;
                break;
            case LabelPosition::BOTTOM_RIGHT:
                $xPositionOffset = 150;
                $yPositionOffset = 98;
                break;
        }

        $pdf->Image(__DIR__.'/../assets/logo.png', 12 + $xPositionOffset, 7 + $yPositionOffset, 34, '', 'PNG');
        $pdf->Image(sprintf(__DIR__.'/../assets/delivery_type_%s.png', $package->getPackageProductType()), 52 + $xPositionOffset, 7 + $yPositionOffset, 26, '', 'PNG');

        $pdf->SetFont($pdf->getFontFamily(), 'B', 30);
        $pdf->Text(78 + $xPositionOffset, 6 + $yPositionOffset, $package->getPackageProductType());

        $pdf->write1DBarcode($package->getPackageNumber(), 'C128', 96 + $xPositionOffset, 4 + $yPositionOffset, 42, 25, '', []);

        $pdf->SetFont($pdf->getFontFamily(), '', 10);
        $pdf->Text(103 + $xPositionOffset, 29 + $yPositionOffset, $package->getPackageNumber());

        //Sender
        $senderX = 10 + $xPositionOffset;
        $senderY = 20 + $yPositionOffset;
        $pdf->SetFont($pdf->getFontFamily(), '', 8);
        $pdf->Text($senderX, $senderY, 'Odesílatel/Sender:');

        $pdf->SetFont($pdf->getFontFamily(), '', 10);

        if ($package->getSender()->getCompany())
        {
            $pdf->Text($senderX, $senderY + 5, $package->getSender()->getCompany());
        }

        if ($package->getSender()->getFirstName() && $package->getSender()->getLastName())
        {
            $pdf->Text($senderX, $senderY + 10, sprintf('%s %s', $package->getSender()->getFirstName(), $package->getSender()->getLastName()));
        }

        $pdf->Text($senderX, $senderY + 15, $package->getSender()->getWww());

        $pdf->Text($senderX, $senderY + 20, sprintf('%s %s', $package->getSender()->getStreet(), $package->getSender()->getStreetNumber()));

        $pdf->Text($senderX, $senderY + 25, sprintf('%s %s - %s', $package->getSender()->getZipCode(), $package->getSender()->getCityPart(), $package->getSender()->getCity()));

        //Postal Office
        $pdf->Text($senderX, 55 + $yPositionOffset, sprintf('PSČ dodací pošty: %s', $package->getSender()->getPostOfficeZipCode()));

        if ($package->getWeightedPackageInfo())
        {
            $pdf->Text(10 + $xPositionOffset, 60 + $yPositionOffset, sprintf('Hmotnost: %s Kg', $package->getWeightedPackageInfo()->getWeight()/1000));
        }

        if (in_array($package->getPackageProductType(), [Product::PACKAGE_TO_HAND]))
        {
            if (!empty(array_intersect(PackageService::$zoneA, $package->getServices())))
            {
                $pdf->Image(__DIR__.'/../assets/a.png', 59 + $xPositionOffset, 18 + $yPositionOffset, 18, '', 'PNG');
            }
            else if (!empty(array_intersect(PackageService::$zoneB, $package->getServices())))
            {
                $pdf->Image(__DIR__.'/../assets/b.png', 59 + $xPositionOffset, 18 + $yPositionOffset, 18, '', 'PNG');
            }
        }

        if (!empty(array_intersect(PackageService::$cashOnDelivery, $package->getServices())) && $package->getPaymentInfo())
        {
            //Cash on Delivery info
            $pdf->Image(__DIR__.'/../assets/cashOnDelivery.png', 50 + $xPositionOffset, 52 + $yPositionOffset, 30, '', 'PNG');

            $pdf->Text(10 + $xPositionOffset, 70 + $yPositionOffset, sprintf('Dobírka: =%s=', $package->getPaymentInfo()->getCashOnDeliveryPrice()));
            $pdf->Text(10 + $xPositionOffset, 75 + $yPositionOffset, sprintf('Slovy: =%s=', self::numbers2words($package->getPaymentInfo()->getCashOnDeliveryPrice())));
        }

        // Multiple packages
        if ($package->getPackageCount() > 1)
        {
            $y = 35 + $yPositionOffset;
            $pdf->SetFont($pdf->getFontFamily(), 'B', 9);

            $text = sprintf('VK %s/%s %s', $package->getPackagePosition(), $package->getPackageCount(), ($package->getPackagePosition() == 1 ? 'Hlavní zásilka' : 'k hl. z. '.$package->getParentPackageNumber()));

            $pdf->MultiCell(53, 0, $text, ['LTRB' => ['width' => 0.2]], 'C', 0, 0, 81 + $xPositionOffset, $y, true, 0, false, true, 0);
        }

        // Receiver
        $receiverX = 80 + $xPositionOffset;
        $receiverY = 50 + $yPositionOffset;
        $pdf->Text($receiverX, $receiverY, 'Adresát/Address:');
        if ($package->getRecipient()->getCompany())
        {
            $pdf->Text($receiverX, $receiverY + 5, $package->getRecipient()->getCompany());
        }

        if ($package->getRecipient()->getFirstName() && $package->getRecipient()->getLastName())
        {
            $pdf->Text($receiverX, $receiverY + 10, sprintf('%s %s', $package->getRecipient()->getFirstName(), $package->getRecipient()->getLastName()));
        }

        $pdf->Text($receiverX, $receiverY + 15, $package->getRecipient()->getPhone());
        $pdf->Text($receiverX, $receiverY + 20, sprintf('%s %s', $package->getRecipient()->getStreet(), $package->getRecipient()->getStreetNumber()));
        $pdf->Text($receiverX, $receiverY + 25, sprintf('%s %s %s',$package->getRecipient()->getZipCode(), $package->getRecipient()->getCityPart(), $package->getRecipient()->getCity()));
        $pdf->Text($receiverX, $receiverY + 30, $package->getRecipient()->getCountry());

        // Infotable
        $pdf->SetFont($pdf->getFontFamily(), 'B', 13);
        $pdf->Ln(5);
        $y = 43 + $yPositionOffset;
        $pdf->MultiCell(14, 0, '', ['LTRB' => ['width' => 0.7]], 'C', 0, 0, 81 + $xPositionOffset, $y, true, 0, false, true, 0);
        $pdf->MultiCell(14, 0, 'D+1', 1, 'C', 0, 0, 95 + $xPositionOffset, $y, true, 0, false, true, 0);
        $pdf->MultiCell(25, 0, '', 1, 'C', 0, 0, 109 + $xPositionOffset, $y, true, 0, false, true, 0);

        return $pdf;
    }

    /**
     * @param $number
     * @return mixed
     */
    private static function numbers2words($number)
    {
        return ucfirst(str_replace(' ', '', @\Numbers_Words::toWords($number, 'cs')));
    }
}