<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class UserFixtures extends Fixture
{

    private array $cities = [
        'Paris',
        'Bordeaux',
        'Lyon',
        'Lens',
        'Rennes',
        'Marseille'
    ];

    public function __construct(private readonly UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $plainPassword = 'password';

        for ($i = 0; $i < 20; $i++) {

            $cityId = array_rand($this->cities);
            $user = new User();
            $user->setEmail('email' . $i);
            $user->setPassword($this->userPasswordHasher->hashPassword($user, $plainPassword));
            $user->setRoles(['ROLE_USER']);
            $user->setCity($this->cities[$cityId]);
            $manager->persist($user);
        }

        $manager->flush();
    }
}
