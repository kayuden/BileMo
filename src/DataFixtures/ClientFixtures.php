<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Enum\Status;
use App\Entity\Client;
use Faker\Factory as FakerFactory;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ClientFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create('en_US');

        $demo = new Client();
        $demo->setEmail('demo@example.com')
            ->setName('Demo Client')
            ->setRoles(['ROLE_CLIENT'])
            ->setStatus(Status::ACTIVE);

        $demo->setPassword($this->passwordHasher->hashPassword($demo, 'password'));

        $createdAt = \DateTimeImmutable::createFromMutable(
            $faker->dateTimeBetween('-2 years', '-6 months')
        );
        $updatedAt = \DateTimeImmutable::createFromMutable(
            $faker->dateTimeBetween($createdAt->format('Y-m-d H:i:s'), 'now')
        );

        $demo->setCreatedAt($createdAt)->setUpdatedAt($updatedAt);
        $manager->persist($demo);

        // Users for demo
        $usersPerClient = $faker->numberBetween(2, 6);
        for ($u = 0; $u < $usersPerClient; $u++) {
            $user = new User();
            $user->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setPhoneNumber($faker->boolean(70) ? $faker->phoneNumber() : null);

            $uCreated = \DateTimeImmutable::createFromMutable(
                $faker->dateTimeBetween('-18 months', '-1 months')
            );
            $uUpdated = \DateTimeImmutable::createFromMutable(
                $faker->dateTimeBetween($uCreated->format('Y-m-d H:i:s'), 'now')
            );

            $user->setCreatedAt($uCreated)
                 ->setUpdatedAt($uUpdated)
                 ->setClient($demo);

            $manager->persist($user);
        }
        
        $count = 10;
        for ($i = 1; $i <= $count; $i++) {
            $client = new Client();

            $client->setEmail(sprintf('client%d@%s', $i, $faker->freeEmailDomain()))
                   ->setName($faker->company())
                   ->setRoles(['ROLE_CLIENT'])
                   ->setStatus($this->randomStatus());

            $client->setPassword($this->passwordHasher->hashPassword($client, 'password'));

            $cCreated = \DateTimeImmutable::createFromMutable(
                $faker->dateTimeBetween('-2 years', '-1 months')
            );
            $cUpdated = \DateTimeImmutable::createFromMutable(
                $faker->dateTimeBetween($cCreated->format('Y-m-d H:i:s'), 'now')
            );

            $client->setCreatedAt($cCreated)->setUpdatedAt($cUpdated);
            $manager->persist($client);

            // Users for this client
            $usersPerClient = $faker->numberBetween(2, 6);
            for ($u = 0; $u < $usersPerClient; $u++) {
                $user = new User();
                $user->setFirstName($faker->firstName())
                    ->setLastName($faker->lastName())
                    ->setPhoneNumber($faker->boolean(70) ? $faker->phoneNumber() : null);

                $uCreated = \DateTimeImmutable::createFromMutable(
                    $faker->dateTimeBetween('-18 months', '-1 months')
                );
                $uUpdated = \DateTimeImmutable::createFromMutable(
                    $faker->dateTimeBetween($uCreated->format('Y-m-d H:i:s'), 'now')
                );

                $user->setCreatedAt($uCreated)
                     ->setUpdatedAt($uUpdated)
                     ->setClient($client);

                $manager->persist($user);
            }
        }

        $manager->flush();
    }

    private function randomStatus(): Status
    {
        $cases = Status::cases();
        return $cases[array_rand($cases)];
    }
}
