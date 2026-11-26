<?php

namespace App\Tests\Application;

use App\Entity\Challenge;
use App\Entity\ChallengeCategory;
use App\Entity\Evaluation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Les pages des défis, vues par un navigateur de test.
 * Chaque test crée son propre utilisateur et le supprime à la fin (avec ses résultats),
 * pour que la base de test reste la même d'un lancement à l'autre.
 */
class ChallengeControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;

    /** @var User[] */
    private array $createdUsers = [];

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
    }

    protected function tearDown(): void
    {
        foreach ($this->createdUsers as $user) {
            $user = $this->em->find(User::class, $user->getId());
            if (!$user) {
                continue;
            }
            foreach ($this->em->getRepository(Evaluation::class)->findBy(['associatedUser' => $user]) as $evaluation) {
                $this->em->remove($evaluation);
            }
            $this->em->remove($user);
        }
        $this->em->flush();

        parent::tearDown();
    }

    public function testAnonymousIsRedirectedToLogin(): void
    {
        $this->client->request('GET', '/user/challenge/');

        $this->assertResponseRedirects('/login');
    }

    public function testListShowsAllChallenges(): void
    {
        $this->client->loginUser($this->createUser());

        $crawler = $this->client->request('GET', '/user/challenge/');

        $this->assertResponseIsSuccessful();
        $this->assertCount(15, $crawler->filter('section li a'));
        $this->assertCount(3, $crawler->filter('section'));
    }

    public function testFilterByCategoryIsRememberedInSession(): void
    {
        $this->client->loginUser($this->createUser());
        $category = $this->em->getRepository(ChallengeCategory::class)->findOneBy(['name' => 'MSEC']);

        // Choisir la catégorie MSEC
        $crawler = $this->client->request('GET', '/user/challenge/');
        $this->client->submit($crawler->selectButton('Filtrer')->form([
            'challenge_filter[category]' => $category->getId(),
        ]));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('section h3', 'MSEC');
        $this->assertSelectorCount(1, 'section');

        // En revenant sur la liste sans paramètre, le filtre est toujours appliqué
        $this->client->request('GET', '/user/challenge/');
        $this->assertSelectorCount(1, 'section');
        $this->assertSelectorTextContains('section h3', 'MSEC');

        // « Toutes » enlève le filtre
        $crawler = $this->client->request('GET', '/user/challenge/');
        $this->client->submit($crawler->selectButton('Filtrer')->form([
            'challenge_filter[category]' => '',
        ]));
        $this->assertSelectorCount(3, 'section');
    }

    #[DataProvider('valueProvider')]
    public function testCreateEvaluation(int $value): void
    {
        $this->client->loginUser($this->createUser());
        $challenge = $this->findChallenge('Pompes');

        $crawler = $this->client->request('GET', '/user/challenge/' . $challenge->getId() . '/evaluations/create');
        $this->assertResponseIsSuccessful();

        $this->client->submit($crawler->selectButton('Enregistrer')->form([
            'evaluation_form[value]' => $value,
        ]));

        $this->assertResponseRedirects('/user/challenge/' . $challenge->getId());
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.test-evaluation-value', $value . ' répétitions');
    }

    public static function valueProvider(): array
    {
        return [[13], [16], [9]];
    }

    public function testNegativeValueIsRefused(): void
    {
        $this->client->loginUser($this->createUser());
        $challenge = $this->findChallenge('Pompes');

        $crawler = $this->client->request('GET', '/user/challenge/' . $challenge->getId() . '/evaluations/create');
        $this->client->submit($crawler->selectButton('Enregistrer')->form([
            'evaluation_form[value]' => -5,
        ]));

        $this->assertResponseStatusCodeSame(422);
        $this->assertSelectorTextContains('body', 'Le résultat ne peut pas être négatif.');
    }

    public function testCannotUpdateSomeoneElsesEvaluation(): void
    {
        $owner = $this->createUser();
        $challenge = $this->findChallenge('Pompes');
        $evaluation = (new Evaluation())
            ->setAssociatedUser($owner)
            ->setChallenge($challenge)
            ->setValue(20);
        $this->em->persist($evaluation);
        $this->em->flush();

        $this->client->loginUser($this->createUser());
        $this->client->request('GET', sprintf(
            '/user/challenge/%d/evaluations/%d/update',
            $challenge->getId(),
            $evaluation->getId(),
        ));

        $this->assertResponseStatusCodeSame(404);
    }

    private function createUser(): User
    {
        $user = (new User())
            ->setEmail(uniqid('test-', true) . '@40fit.test')
            ->setFirstName('Test')
            ->setLastName('Auto')
            ->setPassword('pas-utilisé-avec-loginUser');
        $this->em->persist($user);
        $this->em->flush();
        $this->createdUsers[] = $user;

        return $user;
    }

    private function findChallenge(string $title): Challenge
    {
        $challenge = $this->em->getRepository(Challenge::class)->findOneBy(['title' => $title]);
        $this->assertNotNull($challenge, "Le défi « $title » doit exister dans les fixtures.");

        return $challenge;
    }
}
