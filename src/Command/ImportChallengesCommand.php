<?php

namespace App\Command;

use App\services\ChallengeHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * php bin/console app:import-challenges
 * Charge (ou met à jour) les défis de data/challenges.json, sans toucher aux comptes
 * ni aux résultats. Les fixtures ne servant qu'en développement, c'est cette commande
 * qu'on lance sur un serveur.
 */
#[AsCommand(
    name: 'app:import-challenges',
    description: 'Importe ou met à jour les défis de data/challenges.json',
)]
class ImportChallengesCommand extends Command
{
    public function __construct(
        private readonly ChallengeHandler $challengeHandler,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $count = $this->challengeHandler->import($this->entityManager);

        $io->success(sprintf('%d défi(s) ajouté(s), %d mis à jour.', $count['created'], $count['updated']));

        return Command::SUCCESS;
    }
}
