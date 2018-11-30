<?php
/**
 * Created by PhpStorm.
 * User: osvaldas
 * Date: 18.11.28
 * Time: 17.46
 */

namespace App\DataFixtures;


use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
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

        $user = new User();
        $user->setEmail('admin@nfq.lt')
            ->setRoles([User::ROLE_ADMIN])
            ->setPassword($this->encoder->encodePassword($user, '123456'));

        $manager->persist($user);

        $user = new User();
        $user->setEmail('manager@nfq.lt')
            ->setRoles([User::ROLE_MANAGER])
            ->setPassword($this->encoder->encodePassword($user, '123456'));

        $manager->persist($user);

        $user = new User();
        $user->setEmail('user@nfq.lt')
            ->setRoles([User::ROLE_USER])
            ->setPassword($this->encoder->encodePassword($user, '123456'));

        $manager->persist($user);


        $manager->flush();
    }
}