<?php
/**
 * Created by PhpStorm.
 * User: osvaldas
 * Date: 18.12.19
 * Time: 12.03
 */

namespace App\DataFixtures;


use App\Entity\Offer;
use App\Entity\OfferPositionTemplate;
use App\Entity\OfferTemplate;
use App\Entity\Position;
use App\Entity\PositionTemplate;
use App\Entity\Template;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class TemplateFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;    /**
 * InitialUserFixtures constructor.
 *
 * @param UserPasswordEncoderInterface $encoder
 */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    public function load(ObjectManager $manager)
    {
        $products = $this->makePositions($manager);
        $templates = $this->makeTemplates($manager, $products);
        $users = $this->makeUsers($manager);
        $offers = $this->makeOffers($manager,$users, $templates, $products);

        $manager->flush();
    }
    public function createTemplate($title, $price, $reach, $status)
    {
        return (new Template())
            ->setStatus($status)
            ->setPrice($price)
            ->setReach($reach)
            ->setTitle($title);
    }
    public function createPositionTemplate($count, $position, $template)
    {
        return (new PositionTemplate())
            ->setCount($count)
            ->setPosition($position)
            ->setTemplate($template);
    }

    public function createOfferTemplate($price, $reach, $status, $template, $offer)
    {
        return (new OfferTemplate())
            ->setPrice($price)
            ->setReach($reach)
            ->setStatus($status)
            ->setTemplate($template)
            ->setOffer($offer);
    }
    public function createOfferPositionTemplate($count, $price, $position, $offer, $offerTemplate)
    {
        return (new OfferPositionTemplate())
            ->setCount($count)
            ->setPrice($price)
            ->setPosition($position)
            ->setOffer($offer)
            ->setOfferTemplate($offerTemplate);
    }

    public function createOffer($clientEmail, $clientName, $message, $status, $viewed, $user)
    {
        return (new Offer())
            ->setMd5(md5($clientName . (new \DateTime())->format('Y-m-d H:i:s')))
            ->setClientEmail($clientEmail)
            ->setClientName($clientName)
            ->setMessage($message)
            ->setStatus($status)
            ->setViewed($viewed)
            ->setUser($user);
    }

    function setPosition($name, $price, $reach, $maxQuantity, $isConsume)
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

    /**
     * @param ObjectManager $manager
     * @return array
     */
    private function makePositions(ObjectManager $manager): array
    {
        $products = [];
        $product1 = $this->setPosition('Reklama kube', 30, 10, 1000, true);
        $manager->persist($product1);
        $product2 = $this->setPosition('LED juosta išorėje', 10, 20, 4000, true);
        $manager->persist($product2);
        $product3 = $this->setPosition('LED juosta viduje', 20, 10, 2000, true);
        $manager->persist($product3);
        $product4 = $this->setPosition('Didelis logo ant marškinėlių', 2500, 10000, 2, false);
        $manager->persist($product4);
        $product5 = $this->setPosition('Mažas logo ant marškinėlių', 750, 1500, 4, false);
        $manager->persist($product5);
        $product6 = $this->setPosition('Reklama "TimeOut Žalgiris" laidoje', 10, 300, 1000, true);
        $manager->persist($product6);
        $product7 = $this->setPosition('Logo vidury parketo', 7500, 30000, 1, false);
        $manager->persist($product7);
        $product8 = $this->setPosition('Rungtynių pranešėjęs paskelbia jūsų reklamą', 10, 150, 1000, true);
        $manager->persist($product8);
        $product9 = $this->setPosition('Mažas logo ant parketo', 250, 1500, 4, false);
        $manager->persist($product9);
        $product10 = $this->setPosition('Logo ant šokėjų aprangų', 500, 3500, 6, false);
        $manager->persist($product10);
        $product11 = $this->setPosition('Reklama Zalgiris.lt svetainėje', 200, 1000, 10, false);
        $manager->persist($product11);

        array_push(
            $products,
            $product1, $product2, $product3, $product4, $product5, $product6, $product7, $product8, $product9,
            $product10, $product11);

        return $products;
    }

    /**
     * @param ObjectManager $manager
     * @param $products
     * @return array
     */
    private function makeTemplates(ObjectManager $manager, $products): array
    {
        $templates = [];
        $template1 = $this->createTemplate("Generalinis remėjas", 14000, 56000, Template::BASE);
        $manager->persist($template1);

        $pt1 = $this->createPositionTemplate(100,$products[0], $template1);
        $manager->persist($pt1);
        $pt2 = $this->createPositionTemplate(100,$products[7], $template1);
        $manager->persist($pt2);
        $pt3 = $this->createPositionTemplate(1,$products[3], $template1);
        $manager->persist($pt3);
        $pt4 = $this->createPositionTemplate(1,$products[6], $template1);
        $manager->persist($pt4);

        $template1
            ->addPositionTemplate($pt1)
            ->addPositionTemplate($pt2)
            ->addPositionTemplate($pt3)
            ->addPositionTemplate($pt4);

        $template2 = $this->createTemplate("LED paketas", 12000, 15000, Template::BASE);
        $manager->persist($template2);

        $pt5 = $this->createPositionTemplate(600, $products[1], $template2);
        $manager->persist($pt5);
        $pt6 = $this->createPositionTemplate(300, $products[2], $template2);
        $manager->persist($pt6);

        $template2
            ->addPositionTemplate($pt5)
            ->addPositionTemplate($pt6);

        $template3 = $this->createTemplate("Interneto paslaugos", 1200, 31000, Template::BASE);
        $manager->persist($template3);

        $pt7 = $this->createPositionTemplate(1, $products[10], $template3);
        $manager->persist($pt7);
        $pt8 = $this->createPositionTemplate(100, $products[5], $template3);
        $manager->persist($pt8);

        $template3
            ->addPositionTemplate($pt7)
            ->addPositionTemplate($pt8);

        array_push($templates, $template1, $template2, $template3);

        return $templates;
    }

    /**
     * @param ObjectManager $manager
     * @param $users
     * @param $templates
     * @param $positions
     * @return array
     */
    private function makeOffers(ObjectManager $manager, $users, $templates, $positions)
    {
        $offers = [];
        $names = [];

        array_push($names,
            "Osvaldas Gudauskas","Tautvydas Sabanskis", "Haroldas Mackevičius", "Tadas Rimgaila", "Gabrielius Landsbergis",
            "Petras Dimša", "Gary Albreit", "Yu Lee", "LaVar Ball", "Kevinas Makalisteris", "Rūta Nelutytė");

        for($i = 0; $i < 10; $i++)
        {
            $offer = $this->sentOffers($users,$templates,$positions, $manager, $names[$i]);
            array_push($offers, $offer);
        }

//        for($i = 0; $i < 10; $i++)
//        {
//            $offer = $this->answeredOffers($users,$templates,$positions, $manager, $names[$i]);
//            array_push($offers, $offer);
//        }
//
//        for($i = 0; $i < 10; $i++)
//        {
//            $offer = $this->viewedOffers($users,$templates,$positions, $manager, $names[$i]);
//            array_push($offers, $offer);
//        }

        return $offers;
    }

    private function sentOffers($users,$templates, $positions, $manager, $name){
        $offer1 = $this->createOffer(
            "gudauskas.osvaldas@gmail.com",
            $name,
            "Ar norite tapti Žalgirio remėju?",
            Offer::SENT,
            "2018-10-24 15:00:24",
            $users[1]);

        $manager->persist($offer1);

        $ot1 = $this->createOfferTemplate($templates[0]->getPrice(), $templates[0]->getReach(),"CHECKED",$templates[0], $offer1);
        $manager->persist($ot1);
        $opt1 = $this->createOfferPositionTemplate(100, 50, $positions[0], $offer1, $ot1);
        $manager->persist($opt1);
        $opt2 = $this->createOfferPositionTemplate(100, 10, $positions[7], $offer1, $ot1);
        $manager->persist($opt2);
        $opt3 = $this->createOfferPositionTemplate(1, 2500, $positions[3], $offer1, $ot1);
        $manager->persist($opt3);
        $opt4 = $this->createOfferPositionTemplate(1, 8000, $positions[6], $offer1, $ot1);
        $manager->persist($opt4);
        $ot1
            ->addOfferPositionTemplate($opt1)
            ->addOfferPositionTemplate($opt2)
            ->addOfferPositionTemplate($opt3)
            ->addOfferPositionTemplate($opt4);
        $offer1->addOfferTemplate($ot1);

        return $offer1;
    }

    private function viewedOffers($users,$templates, $positions, $manager, $name){
        $offer1 = $this->createOffer(
            "gudauskas.osvaldas@gmail.com",
            $name,
            "Ar norite tapti Žalgirio remėju?",
            Offer::VIEWED,
            "2018-10-24 15:00:24",
            $users[1]);

        $manager->persist($offer1);

        $ot1 = $this->createOfferTemplate($templates[0]->getPrice(), $templates[0]->getReach(),"CHECKED",$templates[0], $offer1);
        $manager->persist($ot1);
        $opt1 = $this->createOfferPositionTemplate(100, 50, $positions[0], $offer1, $ot1);
        $manager->persist($opt1);
        $opt2 = $this->createOfferPositionTemplate(100, 10, $positions[7], $offer1, $ot1);
        $manager->persist($opt2);
        $opt3 = $this->createOfferPositionTemplate(1, 2500, $positions[3], $offer1, $ot1);
        $manager->persist($opt3);
        $opt4 = $this->createOfferPositionTemplate(1, 8000, $positions[6], $offer1, $ot1);
        $manager->persist($opt4);
        $ot1
            ->addOfferPositionTemplate($opt1)
            ->addOfferPositionTemplate($opt2)
            ->addOfferPositionTemplate($opt3)
            ->addOfferPositionTemplate($opt4);
        $offer1->addOfferTemplate($ot1);

        return $offer1;
    }

    private function answeredOffers($users,$templates, $positions, $manager, $name){
        $offer1 = $this->createOffer(
            "gudauskas.osvaldas@gmail.com",
            $name,
            "Ar norite tapti Žalgirio remėju?",
            Offer::ANSWERED,
            "2018-10-24 15:00:24",
            $users[1]);

        $manager->persist($offer1);

        $ot1 = $this->createOfferTemplate($templates[0]->getPrice(), $templates[0]->getReach(),"CHECKED",$templates[0], $offer1);
        $manager->persist($ot1);
        $opt1 = $this->createOfferPositionTemplate(100, 50, $positions[0], $offer1, $ot1);
        $manager->persist($opt1);
        $opt2 = $this->createOfferPositionTemplate(100, 10, $positions[7], $offer1, $ot1);
        $manager->persist($opt2);
        $opt3 = $this->createOfferPositionTemplate(1, 2500, $positions[3], $offer1, $ot1);
        $manager->persist($opt3);
        $opt4 = $this->createOfferPositionTemplate(1, 8000, $positions[6], $offer1, $ot1);
        $manager->persist($opt4);
        $ot1
            ->addOfferPositionTemplate($opt1)
            ->addOfferPositionTemplate($opt2)
            ->addOfferPositionTemplate($opt3)
            ->addOfferPositionTemplate($opt4);
        $offer1->addOfferTemplate($ot1);

        return $offer1;
    }

    private function makeUsers(ObjectManager $manager)
    {
        $users = [];

        $user1 = new User();
        $user1
            ->setEmail('admin@nfq.lt')
            ->setRoles([User::ROLE_ADMIN])
            ->setPassword($this->encoder->encodePassword($user1, '123456'));

        $manager->persist($user1);

        $user2 = new User();
        $user2
            ->setEmail('manager1@nfq.lt')
            ->setRoles([User::ROLE_MANAGER])
            ->setPassword($this->encoder->encodePassword($user2, '123456'));

        $manager->persist($user2);

        $user3 = new User();
        $user3
            ->setEmail('manager2@nfq.lt')
            ->setRoles([User::ROLE_MANAGER])
            ->setPassword($this->encoder->encodePassword($user3, '123456'));

        $manager->persist($user3);

        $user4 = new User();
        $user4
            ->setEmail('manager3@nfq.lt')
            ->setRoles([User::ROLE_MANAGER])
            ->setPassword($this->encoder->encodePassword($user4, '123456'));

        $manager->persist($user4);

        $user5 = new User();
        $user5
            ->setEmail('user@nfq.lt')
            ->setRoles([User::ROLE_USER])
            ->setPassword($this->encoder->encodePassword($user5, '123456'));

        $manager->persist($user5);

        array_push($users, $user1, $user2, $user3, $user4, $user5);
        return $users;
    }
}