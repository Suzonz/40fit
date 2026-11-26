<?php

namespace App\DataFixtures;

use App\services\ChallengeHandler;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Les défis et leurs catégories, depuis data/challenges.json
 * (le même import que la commande app:import-challenges).
 */
class AppFixtures extends Fixture
{
    public function __construct(
        private readonly ChallengeHandler $challengeHandler
    ) {}

    public function load(ObjectManager $manager): void
    {
        $this->challengeHandler->import($manager);
    }
}
