<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class AppFixtures extends Fixture {

    private string $uploadDir;

    public function __construct(
        private readonly string $projectDir,
        private readonly Filesystem $filesystem
    ) {
        $this->uploadDir = $this->projectDir . DIRECTORY_SEPARATOR . 'uploads';

        $finder = new Finder();
        foreach ($finder->files()->in($this->uploadDir) as $file) {
            $this->filesystem->remove($file->getRealPath());
        }
    }

    public function load(ObjectManager $manager): void {
        $faker = Factory::create('fr_FR');
        for ($i = 0; $i < 100; $i++) {
            $user = (new User())
                ->setUsername($faker->userName())
                ->setEmail($faker->email())
                ->setPassword($faker->password())
                ->setVerified(1)
                ->setCreatedAt(DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months')));

            $photo = $faker->randomElement([
                null,
                'https://avatar.iran.liara.run/public' . '?username=' . urlencode($user->getUsername())
            ]);

            if ($photo) {
                $name = bin2hex(random_bytes(16)) . '.jpg';
                $url = $this->uploadDir . DIRECTORY_SEPARATOR . $name;
                $this->filesystem->dumpFile($url, file_get_contents($photo));
                $user->setPhoto($name);
            }

            $manager->persist($user);
        }

        $manager->flush();
    }
}
