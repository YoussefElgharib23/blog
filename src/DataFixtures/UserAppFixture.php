<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserAppFixture extends Fixture implements FixtureGroupInterface
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user
            ->setFirstName('Youssef')
            ->setLastName('El Gharib')
            ->setEmail('Youssef@example.com')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword($this->encoder->encodePassword($user, 'Password'))
        ;

        $manager->persist($user);
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['BlogGroup'];
    }
}
