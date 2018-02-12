<?php

namespace Application\Fixtures;

use AppBundle\Doctrine\Entity\{
    PersonalApplicationUser, ProApplicationUser
};
use AppBundle\Utils\TokenGenerator;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class UserFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $userPersonalCedric = new PersonalApplicationUser(
            'cedric.spalvieri@gmail.com',
            $this->container->get('wamcar.security.password_encoder')->encodePassword('azerty', 'The_password_is_azerty'),
            'The_password_is_azerty',
            'Cédric',
            'Spalvieri'
        );

        $userProCedric = new ProApplicationUser(
            'cedric@novaway.fr',
            $this->container->get('wamcar.security.password_encoder')->encodePassword('azerty', 'The_password_is_azerty'),
            'The_password_is_azerty',
            'Cédric'
        );
        $userProCedric->generateApiCredentials();

        $manager->persist($userPersonalCedric);
        $manager->persist($userProCedric);
        $manager->flush();

        $this->addReference('user-perso-cedric', $userPersonalCedric);
        $this->addReference('user-pro-cedric', $userProCedric);
    }
}
