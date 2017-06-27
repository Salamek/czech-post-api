<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

use Salamek\CzechPostApi\Model\Package;
use Salamek\CzechPostApi\Enum\LabelDecomposition;
use Salamek\CzechPostApi\Label;

final class LabelTest extends BaseTest
{
    /**
     * @test
     */
    public function testGeneratePdfFullSinglePackage()
    {
        $raw = Label::generateLabels([$this->package], LabelDecomposition::FULL);
        $this->assertNotEmpty($raw);
        $filePath = __DIR__ . '/../tmp/' . $this->package->getPackageNumber() . '-full.pdf';
        file_put_contents($filePath, $raw);
        $this->assertFileExists($filePath);
    }

    /**
     * @test
     */
    public function testGeneratePdfFullMultiplePackages()
    {
        $raw = Label::generateLabels($this->packages, LabelDecomposition::FULL);
        $this->assertNotEmpty($raw);
        $packageNumbers = [];
        /** @var Package $package */
        foreach ($this->packages AS $package) {
            $packageNumbers[] = $package->getPackageNumber();
        }
        $filePath = __DIR__ . '/../tmp/' . implode('-', $packageNumbers) . '-full.pdf';
        file_put_contents($filePath, $raw);
        $this->assertFileExists($filePath);
    }

    /**
     * @test
     */
    public function testGeneratePdfQuarterSinglePackage()
    {
        $raw = Label::generateLabels([$this->package], LabelDecomposition::QUARTER);
        $this->assertNotEmpty($raw);
        $filePath = __DIR__ . '/../tmp/' . $this->package->getPackageNumber() . '-quarter.pdf';
        file_put_contents($filePath, $raw);
        $this->assertFileExists($filePath);
    }

    /**
     * @test
     */
    public function testGeneratePdfQuarterMultiplePackages()
    {
        $raw = Label::generateLabels($this->packages, LabelDecomposition::QUARTER);
        $this->assertNotEmpty($raw);
        $packageNumbers = [];
        /** @var Package $package */
        foreach ($this->packages AS $package) {
            $packageNumbers[] = $package->getPackageNumber();
        }
        $filePath = __DIR__ . '/../tmp/' . implode('-', $packageNumbers) . '-quarter.pdf';
        file_put_contents($filePath, $raw);
        $this->assertFileExists($filePath);
    }

    /**
     * @test
     * @expectedException \Salamek\CzechPostApi\Exception\WrongDataException
     */
    public function testGeneratePdfQuarterMultiplePackagesWrongDecomposition()
    {
        Label::generateLabels($this->packages, 20);
    }
}