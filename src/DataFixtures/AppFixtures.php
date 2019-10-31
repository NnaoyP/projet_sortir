<?php

namespace App\DataFixtures;

use App\Entity\City;
use App\Entity\Participant;
use App\Entity\ParticipantArea;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create();

        for ($i = 1; $i <=3; $i++) {
            $city = new City();
            $city->setName($faker->city);
            $city->setZipCode($faker->postcode);

            $manager->persist($city);
        }

        $participantAreas = array();
        for ($i = 1; $i <=3; $i++) {
            $participantArea = new ParticipantArea();
            $participantArea->setName($faker->domainName);
            array_push($participantAreas, $participantArea);

            $manager->persist($participantArea);
        }

        for ($i=1; $i<=50; $i++) {
            $participant = new Participant();
            $participant->setEmail($faker->unique()->email);
            $participant->setLastName($faker->lastName);
            $participant->setFirstName($faker->firstName);
            $participant->setPhoneNumber($faker->phoneNumber);
            $participant->setIsDeleted(0);
            $participant->setIsActive(0);
            $participant->setPassword($faker->password);
            $participant->setImageUrl("");
            $participant->setParticipantArea($participantAreas[]);

            $manager->persist($participant);
        }

        $manager->flush();
    }
}
