<?php

namespace App\DataFixtures;

use App\Entity\Position;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class PositionFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $product1 = $this->setProduct('Reklama kube', 300, 100, 120, true);
        $manager->persist($product1);
        $product2 = $this->setProduct('LED juosta išorėje', 100, 20, 1440, true);
        $manager->persist($product2);
        $product3 = $this->setProduct('LED juosta viduje', 200, 100, 180, true);
        $manager->persist($product3);
        $product4 = $this->setProduct('Didelis logo ant marškinėlių', 25000, 100000, 2, false);
        $manager->persist($product4);
        $product5 = $this->setProduct('Mažas logo ant marškinėlių', 7500, 15000, 4, false);
        $manager->persist($product5);
        $product6 = $this->setProduct('Reklama "TimeOut Žalgiris" laidoje', 100, 3000, 120, true);
        $manager->persist($product6);
        $product7 = $this->setProduct('Logo vidury parketo', 75000, 300000, 1, false);
        $manager->persist($product7);
        $product8 = $this->setProduct('Rungtynių pranešėjęs paskelbia jūsų reklamą', 100, 1500, 180, true);
        $manager->persist($product8);
        $product9 = $this->setProduct('Mažas logo ant parketo', 2500, 15000, 4, false);
        $manager->persist($product9);
        $product10 = $this->setProduct('Logo ant šokėjų aprangų', 5000, 35000, 6, false);
        $manager->persist($product10);

        $manager->flush();
    }

    function setProduct($name, $price, $reach, $maxQuantity, $isConsume)
    {
        $product = new Position();
        $product->setName($name);
        $product->setPrice($price);
        $product->setReach($reach);
        $product->setRemaining($maxQuantity);
        $product->setMaxQuantity($maxQuantity);
        $product->setHasTime($isConsume);
        return $product;
    }
}
