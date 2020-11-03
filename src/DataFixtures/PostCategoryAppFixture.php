<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Post;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class PostCategoryAppFixture extends Fixture implements FixtureGroupInterface
{

    public function load(ObjectManager $manager)
    {
        $categories = array();
        $faker = Factory::create();
        for ($i = 0; $i < 10; $i++) {
            $category = new Category();
            $category
                ->setName($faker->words(mt_rand(1, 3), true))
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTimeImmutable())
                ;
            $categories[] = $category;

            $manager->persist($category);
        }

        for ($i = 0; $i < 20; $i++) {
            $post = new Post();
            $post
                ->setTitle($faker->words(3, true))
                ->setDescription($faker->sentences(mt_rand(5, 15), true))
                ->setViews(0)
                ->setCategory($faker->randomElement($categories))
                ->setImageLink($faker->imageUrl())
            ;

            $manager->persist($post);
        }
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['BlogGroup'];
    }
}
