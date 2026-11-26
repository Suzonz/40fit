<?php

namespace App\DataFixtures;

use App\Entity\Challenge;
use App\Entity\Evaluation;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Un compte de démonstration avec quelques résultats étalés sur plusieurs semaines,
 * pour voir le tableau de bord rempli dès le premier lancement.
 * Connexion (en développement seulement) : demo@40fit.fr / demo1234
 */
class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {}

    public function getDependencies(): array
    {
        return [AppFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $user = (new User())
            ->setEmail('demo@40fit.fr')
            ->setFirstName('Camille')
            ->setLastName('Demo');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'demo1234'));
        $manager->persist($user);

        // Titre du défi => [il y a N jours => résultat]
        $results = [
            'Pompes' => [42 => 14, 28 => 19, 14 => 24, 3 => 31],
            'Planche' => [35 => 60, 21 => 85, 7 => 105],
            'Course 5km' => [30 => 2280, 16 => 2040, 2 => 1860],
            'Squats au poids du corps' => [20 => 35, 5 => 42],
        ];

        foreach ($results as $title => $history) {
            $challenge = $manager->getRepository(Challenge::class)->findOneBy(['title' => $title]);
            foreach ($history as $daysAgo => $value) {
                $manager->persist((new Evaluation())
                    ->setAssociatedUser($user)
                    ->setChallenge($challenge)
                    ->setValue($value)
                    ->setCreatedAt(new \DateTimeImmutable("-{$daysAgo} days")));
            }
        }

        $manager->flush();
    }
}
