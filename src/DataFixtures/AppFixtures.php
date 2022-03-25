<?php

namespace App\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\{
    FixtureGroupInterface,
    Fixture,
};
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;

class AppFixtures extends Fixture implements FixtureGroupInterface
{
    private UserPasswordEncoderInterface $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $usersData = [
            [
                'email'     => 'admin@test.com',
                'name'      => 'Admin',
                'password'  => '123456',
                'roles'     => [
                    User::ROLE_SUPER_ADMIN,
                ],
                'gender'    => 1
            ],
            [
                'email'     => 'user@test.com',
                'name'      => 'User',
                'password'  => '654321',
                'roles'     => [
                    User::ROLE_USER,
                ],
                'gender'    => 1
            ],
        ];

        foreach ($usersData as $userData) {
            $user = $manager
                ->getRepository(User::class)
                ->findOneBy([
                    'email' => $userData['email'],
                ]) ?? (new User())
                ->setEmail($userData['email']);

            $password = $this->encoder->encodePassword($user, $userData['password']);

            $user->setIsVerified(true);
            $user->setName($userData['name']);
            $user->setGender($userData['gender']);
            $user->setRoles($userData['roles']);
            $user->setPassword($password);
            $user->setIsOnline(false);

            $manager->persist($user);
            $manager->flush();
        }
    }

    public static function getGroups(): array
    {
        return ['default'];
    }

}
