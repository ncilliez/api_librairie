<?php
// src\DataFixtures\AppFixtures.php

namespace App\DataFixtures;

use App\Entity\Livre;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Création d'une vingtaine de livres ayant pour titre
        for ($i = 0; $i < 20; $i++) {
            $livre = new Livre;
            $livre->setTitre('Livre ' . $i);
            $livre->setCouverture('Quatrième de couverture numéro : ' . $i);
            $manager->persist($livre);
        }

        $manager->flush();
    }
}
