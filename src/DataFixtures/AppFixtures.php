<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use Faker\Generator;
use App\Entity\Photo;
use App\Entity\Video;
use App\Entity\Figure;
use DateTimeImmutable;
use App\Entity\Message;
use App\Entity\Category;
use Symfony\Component\Finder\Finder;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture {

    public function __construct(
        private readonly string $projectDir,
        private readonly string $uploadDir,
        private readonly Filesystem $filesystem,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
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

        $this->createMessages($faker, $manager);
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
                $this->filesystem->dumpFile($this->uploadDir . DIRECTORY_SEPARATOR . $name, file_get_contents($photo));
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
        $users = $manager->getRepository(User::class)->findAll();
        foreach ([
            [
                'name' => 'Mute',
                'description' => 'Saisie de la carre frontside de la planche entre les deux pieds avec la main avant',
                'category' => 'Grabs',
                'video' => 'https://www.youtube.com/embed/NnnsXEBwTHc'
            ],
            [
                'name' => 'Sad',
                'description' => 'Saisie de la carre backside de la planche, entre les deux pieds, avec la main avant',
                'category' => 'Grabs',
                'video' => 'https://www.youtube.com/embed/KEdFwJ4SWq4'
            ],
            [
                'name' => 'Indy',
                'description' => 'Saisie de la carre frontside de la planche, entre les deux pieds, avec la main arrière',
                'category' => 'Grabs',
                'video' => 'https://www.youtube.com/embed/6yA3XqjTh_w'
            ],
            [
                'name' => '180',
                'description' => 'Demie-rotation horizontale',
                'category' => 'Rotations',
                'video' => 'https://www.youtube.com/embed/2IqJcdFQiXk'
            ],
            [
                'name' => '360',
                'description' => 'Rotation horizontale complète',
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
                'name' => 'One foot',
                'description' => 'Figure réalisée avec un seul pied fixé sur la planche',
                'category' => 'One foot tricks'
            ],
            [
                'name' => 'Japan air',
                'description' => 'Saut avec une saisie Japan',
                'category' => 'Old school',
                'image' => false
            ]
        ] as $figure) {
            $userCreator = $faker->randomElement($users);
            $figureObject = (new Figure())
                ->setName($figure['name'])
                ->setDescription($figure['description'])
                ->setCategory($manager->getRepository(Category::class)->findOneBy(['name' => $figure['category']]))
                ->setCreatedBy($userCreator)
                ->setUpdatedBy($userCreator);

            if (!isset($figure['image'])) {
                $name = 'tricks-' . bin2hex(random_bytes(16)) . '.jpg';
                $this->filesystem->dumpFile($this->uploadDir . DIRECTORY_SEPARATOR . $name, file_get_contents($this->projectDir . DIRECTORY_SEPARATOR . 'images_tricks' . DIRECTORY_SEPARATOR . str_replace(' ', '', strtolower($figure['name'])) . '.jpg'));
                $figureObject->addPhoto((new Photo())
                        ->setPath($name)
                        ->setFeatured(true)
                );
            }

            if (isset($figure['video'])) {
                $figureObject->addVideo((new Video())
                    ->setPath($figure['video']));
            }

            $manager->persist($figureObject);
        }

        $manager->flush();
    }

    private function createMessages(Generator $faker, ObjectManager $manager): void {
        $users = $manager->getRepository(User::class)->findAll();
        for ($i = 0; $i < 100; $i++) {
            $user = $faker->randomElement($users);
            $message = (new Message())
                ->setMessage($faker->sentence())
                ->setCreatedAt(DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months')))
                ->setUser($user);
            $manager->persist($message);
        }
        $manager->flush();
    }
}
