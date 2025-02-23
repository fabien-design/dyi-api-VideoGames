<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Editor;
use App\Entity\VideoGame;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class VideoGameFixtures extends Fixture implements DependentFixtureInterface
{
    private const VIDEO_GAMES = [
        [
            'title' => 'The Legend of Zelda: Breath of the Wild',
            'description' => 'An action-adventure game developed and published by Nintendo.',
            'releaseDate' => '2017-03-03',
            'categories' => ['3', '0'],
            'editor' => '1',
            'coverImage' => 'thelegendofzeldabreathofthewild.jpg'
        ],
        [
            'title' => 'The Witcher 3: Wild Hunt',
            'description' => 'An action role-playing game developed and published by CD Projekt.',
            'releaseDate' => '2015-05-19',
            'categories' => ['1', '2'],
            'editor' => '1'
        ],
        [
            'title' => 'FIFA 21',
            'description' => 'A football simulation game developed and published by Electronic Arts.',
            'releaseDate' => '2020-10-09',
            'categories' => ['3'],
            'editor' => '4'
        ],
        [
            'title' => 'Super Mario Odyssey',
            'description' => 'A platform game developed and published by Nintendo.',
            'releaseDate' => '2017-10-27',
            'categories' => ['0'],
            'editor' => '1'
        ],
        [
            'title' => 'Minecraft',
            'description' => 'A sandbox game developed and published by Mojang Studios.',
            'releaseDate' => '2011-11-18',
            'categories' => ['3'],
            'editor' => '5'
        ],
        [
            'title' => 'Grand Theft Auto V',
            'description' => 'An action-adventure game developed by Rockstar North and published by Rockstar Games.',
            'releaseDate' => '2013-09-17',
            'categories' => ['1'],
            'editor' => '6'
        ],
        [
            'title' => 'Red Dead Redemption 2',
            'description' => 'An action-adventure game developed and published by Rockstar Games.',
            'releaseDate' => '2018-10-26',
            'categories' => ['1'],
            'editor' => '6'
        ],
        [
            'title' => 'Cyberpunk 2077',
            'description' => 'An action role-playing game developed and published by CD Projekt.',
            'releaseDate' => '2020-12-10',
            'categories' => ['1', '2'],
            'editor' => '1',
            'coverImage' => 'cyberpunk2077.jpg'
        ],
        [
            'title' => 'Assassin\'s Creed Valhalla',
            'description' => 'An action role-playing game developed and published by Ubisoft.',
            'releaseDate' => '2020-11-10',
            'categories' => ['1', '2'],
            'editor' => '4',
            'coverImage' => 'assassinscreedvalhalla.jpg'
        ],
        [
            'title' => 'Call of Duty: Warzone',
            'description' => 'A battle royale game developed by Infinity Ward and Raven Software and published by Activision.',
            'releaseDate' => '2020-03-10',
            'categories' => ['1'],
            'editor' => '6'
        ],
        [
            'title' => 'League of Legends',
            'description' => 'A multiplayer online battle arena game developed and published by Riot Games.',
            'releaseDate' => '2009-10-27',
            'categories' => ['3'],
            'editor' => '7'
        ],
        [
            'title' => 'Valorant',
            'description' => 'A first-person shooter game developed and published by Riot Games.',
            'releaseDate' => '2020-06-02',
            'categories' => ['1'],
            'editor' => '7'
        ],
        [
            'title' => 'World of Warcraft',
            'description' => 'A massively multiplayer online role-playing game developed and published by Blizzard Entertainment.',
            'releaseDate' => '2004-11-23',
            'categories' => ['3'],
            'editor' => '8'
        ],
        [
            'title' => 'Overwatch',
            'description' => 'A first-person shooter game developed and published by Blizzard Entertainment.',
            'releaseDate' => '2016-05-24',
            'categories' => ['1'],
            'editor' => '8'
        ],
        [
            'title' => 'Diablo III',
            'description' => 'An action role-playing game developed and published by Blizzard Entertainment.',
            'releaseDate' => '2012-05-15',
            'categories' => ['1', '2'],
            'editor' => '8'
        ],
        [
            'title' => 'StarCraft II',
            'description' => 'A real-time strategy game developed and published by Blizzard Entertainment.',
            'releaseDate' => '2010-07-27',
            'categories' => ['1'],
            'editor' => '8'
        ],
        [
            'title' => 'Counter-Strike: Global Offensive',
            'description' => 'A first-person shooter game developed and published by Valve.',
            'releaseDate' => '2012-08-21',
            'categories' => ['1'],
            'editor' => '9'
        ],
        [
            'title' => 'Half-Life: Alyx',
            'description' => 'A virtual reality first-person shooter game developed and published by Valve.',
            'releaseDate' => '2020-03-23',
            'categories' => ['1'],
            'editor' => '9'
        ],
        [
            'title' => 'Portal 2',
            'description' => 'A puzzle-platform game developed and published by Valve.',
            'releaseDate' => '2011-04-18',
            'categories' => ['1'],
            'editor' => '9',
        ],
        [
            'title' => 'Dota 2',
            'description' => 'A multiplayer online battle arena game developed and published by Valve.',
            'releaseDate' => '2013-07-09',
            'categories' => ['3'],
            'editor' => '9'
        ],
        [
            'title' => 'Fortnite',
            'description' => 'A battle royale game developed and published by Epic Games.',
            'releaseDate' => '2017-07-25',
            'categories' => ['1'],
            'editor' => '10'
        ],
        [
            'title' => 'Rocket League',
            'description' => 'A vehicular soccer game developed and published by Psyonix.',
            'releaseDate' => '2015-07-07',
            'categories' => ['1'],
            'editor' => '11',
            'coverImage' => 'rocketleague.jpg'
        ],
        [
            'title' => 'Among Us',
            'description' => 'An online multiplayer social deduction game developed and published by InnerSloth.',
            'releaseDate' => '2018-06-15',
            'categories' => ['1'],
            'editor' => '12'
        ],
        [
            'title' => 'Genshin Impact',
            'description' => 'An action role-playing game developed and published by miHoYo.',
            'releaseDate' => '2020-09-28',
            'categories' => ['1', '2'],
            'editor' => '13'
        ],
        [
            'title' => 'PUBG: Battlegrounds',
            'description' => 'A battle royale game developed and published by PUBG Corporation.',
            'releaseDate' => '2017-12-20',
            'categories' => ['1'],
            'editor' => '14'
        ],
        [
            'title' => 'Apex Legends',
            'description' => 'A battle royale game developed by Respawn Entertainment and published by Electronic Arts.',
            'releaseDate' => '2019-02-04',
            'categories' => ['1'],
            'editor' => '4'
        ],
        [
            'title' => 'The Elder Scrolls V: Skyrim',
            'description' => 'An action role-playing game developed and published by Bethesda Softworks.',
            'releaseDate' => '2011-11-11',
            'categories' => ['1', '2'],
            'editor' => '7'
        ],
        [
            'title' => 'DOOM Eternal',
            'description' => 'A first-person shooter game developed by id Software and published by Bethesda Softworks.',
            'releaseDate' => '2020-03-20',
            'categories' => ['1'],
            'editor' => '7'
        ],
        [
            'title' => 'Halo: The Master Chief Collection',
            'description' => 'A compilation of first-person shooter games developed by 343 Industries and published by Xbox Game Studios.',
            'releaseDate' => '2014-11-11',
            'categories' => ['1'],
            'editor' => '2'
        ],
    ];

    private string $fixturesPath;
    private string $uploadsPath;


    public function __construct()
    {
        $this->fixturesPath = __DIR__ . '/../../public/images/fixtures/';
        $this->uploadsPath = __DIR__.'/../../public/images/covers/';
    }

    public function load(ObjectManager $manager): void
    {
        // Créer le dossier des uploads s'il n'existe pas
        if (!file_exists($this->uploadsPath)) {
            mkdir($this->uploadsPath, 0777, true);
        }

        foreach (self::VIDEO_GAMES as $videoGameData) {
            $videoGame = new VideoGame();
            $videoGame->setTitle($videoGameData['title']);
            $videoGame->setDescription($videoGameData['description']);
            $videoGame->setReleaseDate(new \DateTime($videoGameData['releaseDate']));
            
            // Gérer l'image de couverture si elle existe
            if (isset($videoGameData['coverImage']) && file_exists($this->fixturesPath . $videoGameData['coverImage'])) {
                // Copier l'image des fixtures vers le dossier des uploads
                $newFilename = uniqid() . '_' . $videoGameData['coverImage'];
                copy(
                    $this->fixturesPath . $videoGameData['coverImage'],
                    $this->uploadsPath . $newFilename
                );
                
                // Définir le nom du fichier dans l'entité
                $videoGame->setCoverImage($newFilename);
                $videoGame->setUpdatedAt(new \DateTimeImmutable());
            }
            
            foreach ($videoGameData['categories'] as $category) {
                $category = $this->getReference('category_' . $category, Category::class);
                $videoGame->addCategory($category);
            }

            if ($videoGameData['editor']) {
                $editor = $this->getReference('editor_' . $videoGameData['editor'], Editor::class);
                $videoGame->setEditor($editor);
            }

            $manager->persist($videoGame);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
            EditorFixtures::class,
        ];
    }
}
