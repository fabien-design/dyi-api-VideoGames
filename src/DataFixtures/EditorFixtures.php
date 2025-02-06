<?php

namespace App\DataFixtures;

use App\Entity\Editor;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EditorFixtures extends Fixture
{
    public const REFERENCE_IDENTIFIER = 'editor_';
    
    private const EDITORS = [
        [
            'name' => 'Nintendo',
            'country' => 'Japan',
        ],
        [
            'name' => 'Sony',
            'country' => 'Japan',
        ],
        [
            'name' => 'Microsoft',
            'country' => 'USA',
        ],
        [
            'name' => 'Ubisoft',
            'country' => 'France',
        ],
        [
            'name' => 'Electronic Arts',
            'country' => 'USA',
        ],
        [
            'name' => 'Activision',
            'country' => 'USA',
        ],
        [
            'name' => 'Square Enix',
            'country' => 'Japan',
        ],
        [
            'name' => 'Sega',
            'country' => 'Japan',
        ],
        [
            'name' => 'Capcom',
            'country' => 'Japan',
        ],
        [
            'name' => 'Bandai Namco',
            'country' => 'Japan',
        ],
        [
            'name' => 'Konami',
            'country' => 'Japan',
        ],
        [
            'name' => 'Take-Two Interactive',
            'country' => 'USA',
        ],
        [
            'name' => 'Bethesda Softworks',
            'country' => 'USA',
        ],
        [
            'name' => 'Valve',
            'country' => 'USA',
        ],
        [
            'name' => 'CD Projekt',
            'country' => 'Poland',
        ],
        [
            'name' => 'Epic Games',
            'country' => 'USA',
        ],
        [
            'name' => 'Blizzard Entertainment',
            'country' => 'USA',
        ],
        [
            'name' => 'Rockstar Games',
            'country' => 'USA',
        ],
        [
            'name' => 'Tencent',
            'country' => 'China',
        ],
        [
            'name' => 'NetEase',
            'country' => 'China',
        ],
    ];
    public function load(ObjectManager $manager): void
    {
        foreach(self::EDITORS as $key => $editorData) {
            $editor = new Editor();
            $editor->setName($editorData['name'])
                ->setCountry($editorData['country']);
            $manager->persist($editor);
            $this->addReference(self::REFERENCE_IDENTIFIER . $key, $editor);
        }

        $manager->flush();
    }
}
