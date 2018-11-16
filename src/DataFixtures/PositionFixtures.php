<?php

namespace App\DataFixtures;

use App\Entity\Position;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class PositionFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // create 20 products! Bam!
        for ($i = 0; $i < 20; $i++) {
            $product = new Position();
            $product->setName('position '.$i);
            $product->setPrice(mt_rand(10, 100));
            $product->setReach(mt_rand(1000, 10000)*$i);
            $product->setRemaining(1000);
            $product->setMaxQuantity(1000);
            $manager->persist($product);
        }
        $manager->flush();
    }
}
