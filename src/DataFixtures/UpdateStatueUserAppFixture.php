<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UpdateStatueUserAppFixture extends Fixture implements FixtureGroupInterface
{

    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;
    /**
     * @var UserPasswordEncoderInterface
     */
    private UserPasswordEncoderInterface $userPasswordEncoder;

    public function __construct(UserRepository $userRepository, UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $this->userRepository = $userRepository;
        $this->userPasswordEncoder = $userPasswordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();
        for ( $i = 0; $i < 5; $i++ ) {
            $user = ( new User() )->setRoles(['ROLE_USER']);
            $user
                ->setFirstName($faker->name)
                ->setLastName($faker->lastName)
                ->setEmail($faker->email)
                ->setStatus('active')
                ->setPassword($this->userPasswordEncoder->encodePassword($user, 'Password'))
            ;

            $manager->persist($user);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['BlogGroup'];
    }
}
