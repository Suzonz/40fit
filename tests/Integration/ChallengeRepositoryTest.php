<?php

namespace App\Tests\Integration;

use App\Entity\Challenge;
use App\Entity\ChallengeCategory;
use App\Repository\ChallengeRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * ChallengeRepository::findByCategory sur la base de test,
 * remplie avec les fixtures (les 15 défis de data/challenges.json).
 */
class ChallengeRepositoryTest extends KernelTestCase
{
    private ChallengeRepository $challengeRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->challengeRepository = static::getContainer()->get(ChallengeRepository::class);
    }

    #[DataProvider('categoryProvider')]
    public function testFindByCategory(?string $categoryName, int $expectedCount): void
    {
        $category = null;
        if ($categoryName !== null) {
            $category = static::getContainer()->get('doctrine')
                ->getRepository(ChallengeCategory::class)
                ->findOneBy(['name' => $categoryName]);
            $this->assertNotNull($category, "La catégorie « $categoryName » doit exister dans les fixtures.");
        }

        $challenges = $this->challengeRepository->findByCategory($category);

        $this->assertCount($expectedCount, $challenges);
        foreach ($challenges as $challenge) {
            $this->assertInstanceOf(Challenge::class, $challenge);
            if ($category !== null) {
                $this->assertSame($category, $challenge->getCategory());
            }
        }
    }

    public static function categoryProvider(): array
    {
        return [
            'Force Corporelle' => ['Force Corporelle', 5],
            'Endurance Cardiovasculaire' => ['Endurance Cardiovasculaire', 5],
            'MSEC' => ['MSEC', 5],
            'toutes les catégories' => [null, 15],
        ];
    }

    public function testChallengesAreSortedByCategory(): void
    {
        $categoryIds = array_map(
            fn (Challenge $challenge) => $challenge->getCategory()->getId(),
            $this->challengeRepository->findByCategory(),
        );

        $sorted = $categoryIds;
        sort($sorted);
        $this->assertSame($sorted, $categoryIds, 'Les défis doivent être rangés par catégorie.');
    }
}
