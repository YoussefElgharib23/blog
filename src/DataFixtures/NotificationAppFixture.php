<?php

namespace App\DataFixtures;

use App\Entity\Notification;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class NotificationAppFixture extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();
        for ($i = 0; $i < 20; $i++) {
            $notification = new Notification();
            $notification
                ->setDescription($faker->sentences(2, true))
                ->setIsViewed(false)
            ;

            $manager->persist($notification);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['BlogGroup'];
    }
}
