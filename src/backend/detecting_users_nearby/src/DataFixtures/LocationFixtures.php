<?php

namespace App\DataFixtures;

use App\Entity\Location;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class LocationFixtures extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        for ($i = 7; $i < 20; $i++){
            $location = new Location();
            if ($i % 2 == 0){
                $location->setCity('Qazvin');
            }else{
                $location->setCity('Tehran');
            }
            $user = $manager->find(User::class,$i);
            $location->setUserId($user);
            $location->setLongitude(rand(-2,200));
            $location->setLatitude(rand(-2,200));
            $manager->persist($location);
        }
        $manager->flush();
    }
}
