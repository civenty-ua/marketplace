<?php

namespace App\Command\FakeData;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SeedFakeUsersCommand extends AbstractSeedFakeCommand
{
    protected static $defaultName = 'app:seedFakeUsers';
    protected static $defaultDescription = 'This command for inserting 20k fake users in database. DO NOT EXECUTE ON PROD';
    protected const FAKE_DATA_LIMIT = 20000;

    protected function configure(): void
    {
        $this->setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->checkEnv($output);

        $output->writeln("Creating fake users...");
        $now = new \DateTime();
        //todo rand roles

        for ($i = 0; $i < self::FAKE_DATA_LIMIT; $i++) {
            $roles = [User::ROLE_USER];
            $user = new User();
            $user->setEmail($this->faker->email . $i);
            $user->setPhone($this->faker->phoneNumber);
            $user->setName($this->faker->name);
            $user->setIsVerified($this->faker->boolean(2));
            $user->setVerifyPhone(true);
            $user->setIsBanned($this->faker->boolean(2));
            $user->setCreatedAt($now);
            $user->setUpdatedAt($now);
            $user->setIsOnline(false);
            $user->setPassword('$argon2id$v=19$m=65536,t=4,p=1$53Tq0YsFs714FNpeFRZUmw$Oz0yY+tYpZIe3pZkTTvId2sea2VN1DMaWC6Prk7cKeo');

            if ($this->faker->boolean(70)) {
                $roles[] = User::ROLE_SALESMAN;
                $user->setGender(1);
                $user->setDateOfBirth($this->faker->dateTimeBetween('-30 years', '-1 year'));
            } else {
                $roles[] = User::ROLE_SERVICE_PROVIDER;
                $user->setGender(0);
            }
            $user->setRoles($roles);


            $this->entityManager->persist($user);

            if ($i % 50 === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }
        }
        $output->writeln("$i new Fake Users successfully created.");

        return Command::SUCCESS;
    }
}
