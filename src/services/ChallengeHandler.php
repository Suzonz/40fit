<?php

namespace App\services;

use App\Entity\Challenge;
use App\Entity\ChallengeCategory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Les défis décrits dans data/challenges.json, et leur import en base
 * (utilisé par les fixtures et par la commande app:import-challenges).
 */
class ChallengeHandler
{
    public function __construct(private readonly ParameterBagInterface $params,) {}

    public function getChallenges(): array
    {
        $projectDir = $this->params->get('kernel.project_dir');
        $jsonFilePath = $projectDir . '/data/challenges.json';
        $challenges = json_decode(file_get_contents($jsonFilePath), true);
        return $challenges;
    }

    public function getChallengeById(int $id): ?array
    {

        $challenges = $this->getChallenges();

        foreach ($challenges as $challenge) {
            if ($challenge['id'] === $id) {
                return $challenge;
            }
        }
        return null;
    }

    /**
     * Crée ou met à jour chaque défi du fichier (reconnu par son titre) et sa catégorie.
     * Peut être relancé sans créer de doublons.
     *
     * @return array{created: int, updated: int}
     */
    public function import(ObjectManager $manager): array
    {
        $categories = [];
        $count = ['created' => 0, 'updated' => 0];

        foreach ($this->getChallenges() as $data) {
            $name = $data['categoryName'];
            $categories[$name] ??= $manager->getRepository(ChallengeCategory::class)->findOneBy(['name' => $name]);
            if (!$categories[$name]) {
                $categories[$name] = (new ChallengeCategory())->setName($name);
                $manager->persist($categories[$name]);
            }

            $challenge = $manager->getRepository(Challenge::class)->findOneBy(['title' => $data['title']]);
            $count[$challenge ? 'updated' : 'created']++;
            $challenge ??= new Challenge();

            $challenge
                ->setTitle($data['title'])
                ->setDescription($data['description'])
                ->setCategory($categories[$name])
                ->setTargetValue($data['targetValue'])
                ->setUnit($data['unit'])
                ->setLowerIsBetter($data['lowerIsBetter'] ?? false);
            $manager->persist($challenge);
        }

        $manager->flush();

        return $count;
    }
}
