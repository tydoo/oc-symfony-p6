<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Figure;
use DateTimeImmutable;
use App\Entity\Category;
use App\Entity\File;
use Symfony\Component\Finder\Finder;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Faker\Generator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture {

    private string $uploadDir;

    public function __construct(
        private readonly string $projectDir,
        private readonly Filesystem $filesystem,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
        $this->uploadDir = $this->projectDir . DIRECTORY_SEPARATOR . 'uploads';

        $finder = new Finder();
        foreach ($finder->files()->in($this->uploadDir) as $file) {
            $this->filesystem->remove($file->getRealPath());
        }
    }

    public function load(ObjectManager $manager): void {
        $faker = Factory::create('fr_FR');

        $this->createTydooAccount($manager);

        $this->createUsers($faker, $manager);

        $this->createCategories($manager);

        $this->createFigures($faker, $manager);
    }

    private function createTydooAccount(ObjectManager $manager): void {
        $user = (new User())
            ->setUsername('tydoo')
            ->setEmail('thomas@tydoo.fr')
            ->setVerified(1);
        $name = 'tydoo-' . bin2hex(random_bytes(16)) . '.jpg';
        $url = $this->uploadDir . DIRECTORY_SEPARATOR . $name;
        $this->filesystem->dumpFile($url, file_get_contents('https://avatar.iran.liara.run/public/boy?username=' . urlencode($user->getUsername())));
        $user->setPhoto($name);
        $manager->persist($user->setPassword($this->passwordHasher->hashPassword($user, '112233')));
    }

    private function createUsers(Generator $faker, ObjectManager $manager): void {
        for ($i = 0; $i < 50; $i++) {
            $user = (new User())
                ->setUsername($faker->unique()->userName())
                ->setEmail($faker->unique()->email())
                ->setPassword($faker->password())
                ->setVerified(1)
                ->setCreatedAt(DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months')));

            $photo = $faker->randomElement([
                null,
                'https://avatar.iran.liara.run/public' . '?username=' . urlencode($user->getUsername())
            ]);

            if ($photo) {
                $name = 'avatar-' . bin2hex(random_bytes(16)) . '.jpg';
                $url = $this->uploadDir . DIRECTORY_SEPARATOR . $name;
                $this->filesystem->dumpFile($url, file_get_contents($photo));
                $user->setPhoto($name);
            }

            $manager->persist($user);
        }
        $manager->flush();
    }

    private function createCategories(ObjectManager $manager): void {
        foreach ([
            'Grabs',
            'Rotations',
            'Flips',
            'Slides',
            'One foot tricks',
            'Old school'
        ] as $categoryName) {
            $category = (new Category())
                ->setName($categoryName);
            $manager->persist($category);
        }
        $manager->flush();
    }

    private function createFigures(Generator $faker, ObjectManager $manager): void {
        foreach ([
            [
                'name' => 'Mute',
                'description' => 'Saisie de la carre frontside de la planche entre les deux pieds avec la main avant',
                'category' => 'Grabs',
            ],
            [
                'name' => 'Sad',
                'description' => 'Saisie de la carre backside de la planche, entre les deux pieds, avec la main avant',
                'category' => 'Grabs'
            ],
            [
                'name' => 'Indy',
                'description' => 'Saisie de la carre frontside de la planche, entre les deux pieds, avec la main arrière',
                'category' => 'Grabs'
            ],
            [
                'name' => 'Stalefish',
                'description' => 'Saisie de la carre backside de la planche entre les deux pieds avec la main arrière',
                'category' => 'Grabs'
            ],
            [
                'name' => 'Tail grab',
                'description' => 'Saisie de la partie arrière de la planche, avec la main arrière',
                'category' => 'Grabs'
            ],
            [
                'name' => 'Nose grab',
                'description' => 'Saisie de la partie avant de la planche, avec la main avant',
                'category' => 'Grabs'
            ],
            [
                'name' => 'Japan',
                'description' => 'Saisie de l\'avant de la planche, avec la main avant, du côté de la carre frontside',
                'category' => 'Grabs'
            ],
            [
                'name' => 'Seat belt',
                'description' => 'Saisie du carre frontside à l\'arrière avec la main avant',
                'category' => 'Grabs'
            ],
            [
                'name' => 'Method',
                'description' => 'Saisie de la carre frontside de la planche entre les deux pieds avec la main arrière',
                'category' => 'Grabs'
            ],
            [
                'name' => '180',
                'description' => 'Demie-rotation horizontale',
                'category' => 'Rotations'
            ],
            [
                'name' => '360',
                'description' => 'Rotation horizontale complète',
                'category' => 'Rotations'
            ],
            [
                'name' => '540',
                'description' => 'Rotation d\'un tour et demi',
                'category' => 'Rotations'
            ],
            [
                'name' => '720',
                'description' => 'Rotation de deux tours',
                'category' => 'Rotations'
            ],
            [
                'name' => '900',
                'description' => 'Rotation de deux tours et demi',
                'category' => 'Rotations'
            ],
            [
                'name' => '1080',
                'description' => 'Rotation de trois tours',
                'category' => 'Rotations'
            ],
            [
                'name' => 'Back flip',
                'description' => 'Rotation verticale en arrière',
                'category' => 'Flips'
            ],
            [
                'name' => 'Front flip',
                'description' => 'Rotation verticale en avant',
                'category' => 'Flips'
            ],
            [
                'name' => 'Switch back flip',
                'description' => 'Rotation verticale en arrière, en position switch',
                'category' => 'Flips'
            ],
            [
                'name' => 'Switch front flip',
                'description' => 'Rotation verticale en avant, en position switch',
                'category' => 'Flips'
            ],
            [
                'name' => 'Cork',
                'description' => 'Rotation horizontale avec un dévers',
                'category' => 'Flips'
            ],
            [
                'name' => 'Underflip',
                'description' => 'Rotation horizontale en arrière',
                'category' => 'Flips'
            ],
            [
                'name' => 'Mc Twist',
                'description' => 'Rotation verticale en avant avec une vrille de 540°',
                'category' => 'Flips'
            ],
            [
                'name' => 'Rodeo',
                'description' => 'Rotation horizontale en avant',
                'category' => 'Flips'
            ],
            [
                'name' => 'Slide',
                'description' => 'Glissade sur une barre de slide',
                'category' => 'Slides'
            ],
            [
                'name' => 'Lipslide',
                'description' => 'Glissade sur le haut de la barre de slide',
                'category' => 'Slides'
            ],
            [
                'name' => 'Boardslide',
                'description' => 'Glissade sur la barre de slide avec la planche perpendiculaire à la barre',
                'category' => 'Slides'
            ],
            [
                'name' => 'Noseslide',
                'description' => 'Glissade sur la barre de slide avec le nose de la planche',
                'category' => 'Slides'
            ],
            [
                'name' => 'Tailslide',
                'description' => 'Glissade sur la barre de slide avec le tail de la planche',
                'category' => 'Slides'
            ],
            [
                'name' => 'Bluntslide',
                'description' => 'Glissade sur la barre de slide avec le tail de la planche',
                'category' => 'Slides'
            ],
            [
                'name' => 'One foot',
                'description' => 'Figure réalisée avec un seul pied fixé sur la planche',
                'category' => 'One foot tricks'
            ],
            [
                'name' => 'Japan air',
                'description' => 'Saut avec une saisie Japan',
                'category' => 'Old school'
            ],
            [
                'name' => 'Rocket air',
                'description' => 'Saut avec une saisie de la carre frontside de la planche entre les deux pieds avec la main avant',
                'category' => 'Old school'
            ],
            [
                'name' => 'Backside air',
                'description' => 'Saut avec une rotation backside',
                'category' => 'Old school'
            ],
            [
                'name' => 'Frontside air',
                'description' => 'Saut avec une rotation frontside',
                'category' => 'Old school'
            ],
            [
                'name' => 'Method air',
                'description' => 'Saut avec une saisie Method',
                'category' => 'Old school'
            ],
            [
                'name' => 'Indy Nosebone',
                'description' => 'Saut avec une saisie Indy et une extension du pied avant',
                'category' => 'Old school'
            ]
        ] as $figure) {
            $userCreator = $faker->randomElement($manager->getRepository(User::class)->findAll());
            $figureObject = (new Figure())
                ->setName($figure['name'])
                ->setDescription($figure['description'])
                ->setCategory($manager->getRepository(Category::class)->findOneBy(['name' => $figure['category']]))
                ->setCreatedBy($userCreator)
                ->setUpdatedBy($userCreator);


            $name = 'tricks-' . bin2hex(random_bytes(16)) . '.jpg';
            $url = $this->uploadDir . DIRECTORY_SEPARATOR . $name;
            $this->filesystem->dumpFile($url, file_get_contents($this->projectDir . DIRECTORY_SEPARATOR . 'images_tricks' . DIRECTORY_SEPARATOR . str_replace(' ', '', strtolower($figure['name'])) . '.jpg'));
            $file = (new File)
                ->setName($figure['name'])
                ->setType('image')
                ->setUrl($url)
                ->setFigure($figureObject);
            $manager->persist($file);
            $manager->persist($figureObject);
        }

        $manager->flush();
    }
}
