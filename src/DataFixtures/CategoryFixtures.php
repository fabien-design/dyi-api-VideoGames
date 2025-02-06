<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public const REFERENCE_IDENTIFIER = 'category_';
    
    private const CATEGORIES = [
        'Action',
        'Adventure',
        'Role-Playing',
        'Simulation',
        'Strategy',
        'Sports',
        'Puzzle',
        'Idle',
        'Racing',
        'Fighting',
        'Survival',
        'Horror',
        'MMORPG',
        'Educational',
        'Music',
        'Party',
        'Board',
        'Card',
        'Casual',
        'Arcade',
        'Platformer',
        'Shooter',
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::CATEGORIES as $key => $categoryName) {
            $category = new Category();
            $category->setName($categoryName);
            $manager->persist($category);
            $this->addReference(self::REFERENCE_IDENTIFIER . $key, $category);
        }

        $manager->flush();
    }
}
