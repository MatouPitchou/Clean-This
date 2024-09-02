<?php

namespace App\DataFixtures;

use App\Entity\Services;
use App\Entity\Users;
use App\Entity\Operations;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Faker\Provider\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
*
* @author Dylan Rohart
* Provides fake data for databases
* fake Users, fake Operations plus Services & Roles
*
*/

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $services = new Services();
        $services->setType('Petite');
        $services->setPrice(1000);
        $manager->persist($services);
        $services1 = new Services();
        $services1->setType('Moyenne');
        $services1->setPrice(2500);
        $manager->persist($services1);
        $services2 = new Services();
        $services2->setType('Grande');
        $services2->setPrice(5000);
        $manager->persist($services2);
        $services3 = new Services();
        $services3->setType('Custom');
        $services3->setPrice(0);
        $manager->persist($services3);

        $manager->flush();

        $faker = Faker\Factory::create('fr_FR');

        
        $createdAt = $faker->dateTimeBetween( '-6 months', 'now');
        $createdAtImmutable = DateTimeImmutable::createFromMutable($createdAt);
        $activeAt = $faker->dateTimeBetween( '-6 days', 'now');
        $activeAtImmutable = DateTimeImmutable::createFromMutable($activeAt);

        $adminUser = new Users();
        $adminUser->setLastname($faker->lastName())
            ->setFirstname($faker->firstName())
            ->setRoles(['ROLE_ADMIN'])
            ->setEmail('admin@cleanthis.com')
            ->setPhone($faker->phoneNumber())
            ->setZipcode(Address::postcode())
            ->setCity($faker->city())
            ->setStreet($faker->streetAddress())
            ->setCreatedAt($createdAtImmutable)
            ->setActiveAt($activeAtImmutable);
            $password = $this->passwordHasher->hashPassword($adminUser, 'password');
            $adminUser->setPassword($password);
        $manager->persist($adminUser);
        $manager->flush();

        for ($i = 0; $i < 5; $i++) {
            $createdAt = $faker->dateTimeBetween( '-6 months', 'now');
            $createdAtImmutable = DateTimeImmutable::createFromMutable($createdAt);
            $activeAt = $faker->dateTimeBetween( '-6 days', 'now');
            $activeAtImmutable = DateTimeImmutable::createFromMutable($activeAt);

            $users = new Users();
            $users->setLastname($faker->lastName())
                    ->setFirstname($faker->firstName())
                    ->setRoles(['ROLE_APPRENTI'])
                    ->setEmail($faker->email())
                    ->setPhone($faker->phoneNumber())
                    ->setZipcode(Address::postcode())
                    ->setCity($faker->city())
                    ->setStreet($faker->streetAddress())
                    ->setCreatedAt($createdAtImmutable)
                    ->setActiveAt($activeAtImmutable);
            $manager->persist($users);

            $password = $this->passwordHasher->hashPassword($users, $faker->word());
            $users->setPassword($password);
        }
        for ($i = 0; $i < 25; $i++) {
            $createdAt = $faker->dateTimeBetween( '-6 months', 'now');
            $createdAtImmutable = DateTimeImmutable::createFromMutable($createdAt);
            $activeAt = $faker->dateTimeBetween( '-6 days', 'now');
            $activeAtImmutable = DateTimeImmutable::createFromMutable($activeAt);

            $users = new Users();
            $users->setLastname($faker->lastName())
                    ->setFirstname($faker->firstName())
                    ->setRoles(['ROLE_SENIOR'])
                    ->setEmail($faker->email())
                    ->setPhone($faker->phoneNumber())
                    ->setZipcode(Address::postcode())
                    ->setCity($faker->city())
                    ->setStreet($faker->streetAddress())
                    ->setCreatedAt($createdAtImmutable)
                    ->setActiveAt($activeAtImmutable);
            $manager->persist($users);

            $password = $this->passwordHasher->hashPassword($users, $faker->word());
            $users->setPassword($password);
        }

        // create 30 users! Bam!
        for ($i = 0; $i < 30; $i++) {
            $createdAt = $faker->dateTimeBetween( '-6 months', 'now');
            $createdAtImmutable = DateTimeImmutable::createFromMutable($createdAt);
            $activeAt = $faker->dateTimeBetween( '-6 days', 'now');
            $activeAtImmutable = DateTimeImmutable::createFromMutable($activeAt);

            $users = new Users();
            $users->setLastname($faker->lastName())
                    ->setFirstname($faker->firstName())
                    ->setRoles(['ROLE_USER'])
                    ->setEmail($faker->email())
                    ->setPhone($faker->phoneNumber())
                    ->setZipcode(Address::postcode())
                    ->setCity($faker->city())
                    ->setStreet($faker->streetAddress())
                    ->setCreatedAt($createdAtImmutable)
                    ->setActiveAt($activeAtImmutable);
            $manager->persist($users);

            $password = $this->passwordHasher->hashPassword($users, $faker->word());
            $users->setPassword($password);




            //* Operations 
                for ($j=0; $j < 1 ; $j++) {
                    $createdAt = $faker->dateTimeBetween( '-6 months', 'now');
                    $createdAtImmutable = DateTimeImmutable::createFromMutable($createdAt);
                    $LastModifiedAt = $faker->dateTimeBetween( '-3 months', 'now');
                    $LastModifiedAtImmutable = DateTimeImmutable::createFromMutable($LastModifiedAt);

                    $operations = new Operations();

                    $operations->addUserId($users)
                                // ->setIdService($this->servicesRepository->getRandom())
                                ->setDescription($faker->text(500))
                                ->setServices($services)
                                ->setStatus('En cours')
                                ->setQuote('Acceptée')
                                ->setZipcode(Address::postcode())
                                ->setCity($faker->city())
                                ->setStreet($faker->streetAddress())
                                ->setCreatedAt($createdAtImmutable)
                                ->setLastModifiedAt($LastModifiedAtImmutable)
                                ->setSurface(5);

                    $manager->persist($operations);
                }
        }


        // Enregistrer les modifications dans la base de données
        $manager->flush();
    }
}
