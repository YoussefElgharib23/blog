<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CategoryAppFixture extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();
        for ($i = 0; $i < 10; $i++) {
            $category = new Category();
            $category
                ->setName($faker->title)
            ;
            $manager->persist($category);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return [
            'Category',
        ];
    }
}
